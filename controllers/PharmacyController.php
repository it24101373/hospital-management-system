<?php
/**
 * Hospital Management System (HMS)
 * Pharmacy Management Controller (SRS Section 3.7)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class PharmacyController {
    public static function getAll(string $search = '', string $category = '', string $status = ''): array {
        $sql = "SELECT * FROM pharmacy_medicines WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (code LIKE :s OR name LIKE :s OR generic_name LIKE :s OR manufacturer LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        if (!empty($category)) {
            $sql .= " AND category = :cat";
            $params['cat'] = $category;
        }
        if (!empty($status)) {
            if ($status === 'Low Stock') {
                $sql .= " AND stock_quantity <= reorder_level AND stock_quantity > 0";
            } elseif ($status === 'Out of Stock') {
                $sql .= " AND stock_quantity <= 0";
            } elseif ($status === 'Expiring Soon') {
                $sql .= " AND expiry_date <= date('now', '+30 days') AND expiry_date >= date('now')";
            } elseif ($status === 'Expired') {
                $sql .= " AND expiry_date < date('now')";
            }
        }
        $sql .= " ORDER BY name ASC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM pharmacy_medicines WHERE id = :id", ['id' => $id]);
    }

    public static function getAlerts(): array {
        $today = date('Y-m-d');
        $in30Days = date('Y-m-d', strtotime('+30 days'));

        $lowStock = Database::fetchAll("SELECT * FROM pharmacy_medicines WHERE stock_quantity <= reorder_level ORDER BY stock_quantity ASC");
        $expiringSoon = Database::fetchAll("SELECT * FROM pharmacy_medicines WHERE expiry_date <= :in30 AND expiry_date >= :today ORDER BY expiry_date ASC", [
            'in30'  => $in30Days,
            'today' => $today
        ]);
        $expired = Database::fetchAll("SELECT * FROM pharmacy_medicines WHERE expiry_date < :today ORDER BY expiry_date ASC", [
            'today' => $today
        ]);

        return [
            'low_stock'     => $lowStock,
            'expiring_soon' => $expiringSoon,
            'expired'       => $expired
        ];
    }

    public static function createMedicine(array $data): int {
        // Generate medicine code
        $last = Database::fetchOne("SELECT code FROM pharmacy_medicines ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/MED-(\d+)/', $last['code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("MED-%03d", $nextSeq);

        $stock = (int)($data['stock_quantity'] ?? 0);
        $reorder = (int)($data['reorder_level'] ?? 20);
        $expiry = cleanInput($data['expiry_date']);

        $status = 'Available';
        if ($stock <= 0) {
            $status = 'Out of Stock';
        } elseif ($stock <= $reorder) {
            $status = 'Low Stock';
        }

        $medId = Database::insert('pharmacy_medicines', [
            'code'           => $code,
            'name'           => cleanInput($data['name']),
            'generic_name'   => cleanInput($data['generic_name'] ?? ''),
            'category'       => cleanInput($data['category'] ?? 'General'),
            'dosage_form'    => cleanInput($data['dosage_form'] ?? 'Tablet'),
            'batch_number'   => cleanInput($data['batch_number'] ?? ('BAT-' . strtoupper(bin2hex(random_bytes(3))))),
            'unit_price'     => (float)($data['unit_price'] ?? 0.0),
            'stock_quantity' => $stock,
            'reorder_level'  => $reorder,
            'expiry_date'    => $expiry,
            'manufacturer'   => cleanInput($data['manufacturer'] ?? ''),
            'status'         => $status
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Pharmacist', $user['role_name'] ?? 'Pharmacist', 'Add Medicine', 'Pharmacy', "Added {$data['name']} ({$code}) to pharmacy inventory", $medId);

        return $medId;
    }

    public static function updateStock(int $id, int $quantityChange, string $type = 'Restock', string $notes = ''): bool {
        $med = self::getById($id);
        if (!$med) return false;

        $newStock = $med['stock_quantity'] + $quantityChange;
        if ($newStock < 0) $newStock = 0;

        $status = 'Available';
        if ($newStock <= 0) {
            $status = 'Out of Stock';
        } elseif ($newStock <= $med['reorder_level']) {
            $status = 'Low Stock';
        }

        Database::update('pharmacy_medicines', [
            'stock_quantity' => $newStock,
            'status'         => $status
        ], 'id = :id', ['id' => $id]);

        $user = Auth::user();
        Database::insert('pharmacy_transactions', [
            'medicine_id'      => $id,
            'transaction_type' => $type,
            'quantity'         => abs($quantityChange),
            'unit_price'       => $med['unit_price'],
            'total_amount'     => abs($quantityChange) * $med['unit_price'],
            'transaction_date' => date('Y-m-d H:i:s'),
            'notes'            => cleanInput($notes),
            'performed_by'     => $user['full_name'] ?? 'Pharmacist'
        ]);

        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Pharmacist', $user['role_name'] ?? 'Pharmacist', 'Adjust Stock', 'Pharmacy', "Adjusted stock for {$med['name']}: new quantity {$newStock}", $id);

        return true;
    }

    public static function getPendingPrescriptions(): array {
        $sql = "SELECT pr.*, 
                       p.full_name as patient_name, p.patient_code,
                       d.full_name as doctor_name,
                       mr.record_date 
                FROM prescriptions pr 
                JOIN patients p ON pr.patient_id = p.id 
                JOIN doctors d ON pr.doctor_id = d.id 
                JOIN medical_records mr ON pr.medical_record_id = mr.id 
                WHERE pr.is_dispensed = 0 
                ORDER BY pr.id DESC";
        return Database::fetchAll($sql);
    }

    public static function dispensePrescription(int $prescriptionId, ?int $medicineId = null, int $qty = 1): bool {
        $presc = Database::fetchOne("SELECT * FROM prescriptions WHERE id = :id", ['id' => $prescriptionId]);
        if (!$presc) return false;

        // Try to match medicine by ID or by name
        if (!$medicineId) {
            $med = Database::fetchOne("SELECT * FROM pharmacy_medicines WHERE name LIKE :name LIMIT 1", ['name' => '%' . strtok($presc['medicine_name'], ' ') . '%']);
            if ($med) {
                $medicineId = (int)$med['id'];
            }
        }

        if ($medicineId) {
            self::updateStock($medicineId, -$qty, 'Dispense', "Dispensed for prescription ID #{$prescriptionId}");
        }

        Database::update('prescriptions', ['is_dispensed' => 1], 'id = :id', ['id' => $prescriptionId]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Pharmacist', $user['role_name'] ?? 'Pharmacist', 'Dispense Prescription', 'Pharmacy', "Dispensed prescription #{$prescriptionId} for patient ID #{$presc['patient_id']}", $prescriptionId);

        return true;
    }
}
