<?php
/**
 * Hospital Management System (HMS)
 * Billing & Payment Controller (SRS Section 3.8 & DB Modules 11 & 12)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class BillingController {
    public static function getAll(string $search = '', string $status = ''): array {
        $sql = "SELECT b.*, 
                       p.full_name as patient_name, p.patient_code, p.phone as patient_phone 
                FROM bills b 
                JOIN patients p ON b.patient_id = p.id 
                WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (b.invoice_number LIKE :s OR p.full_name LIKE :s OR p.patient_code LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        if (!empty($status)) {
            $sql .= " AND b.payment_status = :st";
            $params['st'] = $status;
        }
        $sql .= " ORDER BY b.id DESC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        $sql = "SELECT b.*, 
                       p.full_name as patient_name, p.patient_code, p.dob, p.gender, p.phone as patient_phone, p.email, p.address 
                FROM bills b 
                JOIN patients p ON b.patient_id = p.id 
                WHERE b.id = :id";
        $bill = Database::fetchOne($sql, ['id' => $id]);
        if ($bill) {
            $bill['items'] = Database::fetchAll("SELECT * FROM bill_items WHERE bill_id = :bid", ['bid' => $id]);
            $bill['payments'] = Database::fetchAll("SELECT * FROM payments WHERE bill_id = :bid ORDER BY payment_date DESC", ['bid' => $id]);
        }
        return $bill;
    }

    public static function createBill(array $data, array $items = []): int {
        // Generate invoice number
        $year = date('Y');
        $last = Database::fetchOne("SELECT invoice_number FROM bills ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/INV-\d{4}-(\d+)/', $last['invoice_number'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $invoiceNumber = sprintf("INV-%s-%03d", $year, $nextSeq);

        $totalConsultation = (float)($data['total_consultation'] ?? 0.0);
        $totalLab = (float)($data['total_lab'] ?? 0.0);
        $totalPharmacy = (float)($data['total_pharmacy'] ?? 0.0);
        $totalAdmission = (float)($data['total_admission'] ?? 0.0);

        $subtotal = $totalConsultation + $totalLab + $totalPharmacy + $totalAdmission;
        if (empty($subtotal) && !empty($items)) {
            foreach ($items as $item) {
                $subtotal += (float)$item['total_price'];
            }
        }

        $discount = (float)($data['discount'] ?? 0.0);
        $taxRate = (float)($data['tax_rate'] ?? 0.05); // 5% default
        $tax = round(($subtotal - $discount) * $taxRate, 2);
        if ($tax < 0) $tax = 0;
        $grandTotal = ($subtotal - $discount) + $tax;
        $balance = $grandTotal;

        $billId = Database::insert('bills', [
            'invoice_number'     => $invoiceNumber,
            'patient_id'         => (int)$data['patient_id'],
            'appointment_id'     => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'admission_id'       => !empty($data['admission_id']) ? (int)$data['admission_id'] : null,
            'bill_date'          => !empty($data['bill_date']) ? cleanInput($data['bill_date']) : date('Y-m-d'),
            'due_date'           => !empty($data['due_date']) ? cleanInput($data['due_date']) : date('Y-m-d', strtotime('+7 days')),
            'total_consultation' => $totalConsultation,
            'total_lab'          => $totalLab,
            'total_pharmacy'     => $totalPharmacy,
            'total_admission'    => $totalAdmission,
            'subtotal'           => $subtotal,
            'discount'           => $discount,
            'tax'                => $tax,
            'grand_total'        => $grandTotal,
            'paid_amount'        => 0.0,
            'balance_amount'     => $balance,
            'payment_status'     => 'Unpaid',
            'notes'              => cleanInput($data['notes'] ?? '')
        ]);

        // Insert line items
        if (!empty($items)) {
            foreach ($items as $item) {
                Database::insert('bill_items', [
                    'bill_id'     => $billId,
                    'item_type'   => cleanInput($item['item_type'] ?? 'Other'),
                    'description' => cleanInput($item['description']),
                    'quantity'    => (int)($item['quantity'] ?? 1),
                    'unit_price'  => (float)($item['unit_price'] ?? 0.0),
                    'total_price' => (float)($item['total_price'] ?? 0.0)
                ]);
            }
        }

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Accountant', $user['role_name'] ?? 'Accountant', 'Generate Invoice', 'Billing', "Generated invoice {$invoiceNumber} for Patient #{$data['patient_id']}: Total \${$grandTotal}", $billId);

        return $billId;
    }

    public static function recordPayment(int $billId, array $data): int {
        $bill = self::getById($billId);
        if (!$bill) return 0;

        $amount = (float)$data['amount'];
        if ($amount <= 0) return 0;

        // Generate payment code
        $last = Database::fetchOne("SELECT payment_code FROM payments ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/PAY-(\d+)/', $last['payment_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("PAY-%03d", $nextSeq);

        $user = Auth::user();
        $payId = Database::insert('payments', [
            'payment_code'          => $code,
            'bill_id'               => $billId,
            'patient_id'            => (int)$bill['patient_id'],
            'amount'                => $amount,
            'payment_method'        => cleanInput($data['payment_method'] ?? 'Cash'),
            'transaction_reference' => cleanInput($data['transaction_reference'] ?? ('REF-' . strtoupper(bin2hex(random_bytes(3))))),
            'payment_date'          => date('Y-m-d H:i:s'),
            'received_by'           => $user['full_name'] ?? 'Accountant',
            'notes'                 => cleanInput($data['notes'] ?? '')
        ]);

        // Recompute paid & balance
        $newPaid = $bill['paid_amount'] + $amount;
        $newBalance = $bill['grand_total'] - $newPaid;
        if ($newBalance < 0.01) {
            $newBalance = 0;
            $newStatus = 'Paid';
        } else {
            $newStatus = 'Partial';
        }

        Database::update('bills', [
            'paid_amount'    => $newPaid,
            'balance_amount' => $newBalance,
            'payment_status' => $newStatus
        ], 'id = :id', ['id' => $billId]);

        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Accountant', $user['role_name'] ?? 'Accountant', 'Record Payment', 'Billing', "Recorded payment of \${$amount} via {$data['payment_method']} for Invoice {$bill['invoice_number']}", $payId);

        return $payId;
    }

    public static function getPatientUnbilledItems(int $patientId): array {
        // Find consultation fee from latest appointment
        $apt = Database::fetchOne("
            SELECT a.id, a.appointment_code, d.consultation_fee, d.full_name as doctor_name 
            FROM appointments a 
            JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.patient_id = :pid AND a.status = 'Completed' 
            ORDER BY a.id DESC LIMIT 1
        ", ['pid' => $patientId]);

        // Find completed lab tests
        $labs = Database::fetchAll("
            SELECT id, test_code, test_name, cost 
            FROM laboratory_tests 
            WHERE patient_id = :pid AND sample_status = 'Completed' 
            ORDER BY id DESC LIMIT 5
        ", ['pid' => $patientId]);

        // Find active admissions
        $adm = Database::fetchOne("
            SELECT id, admission_code, ward_name, bed_number, daily_bed_charge, admission_date 
            FROM admissions 
            WHERE patient_id = :pid AND status = 'Admitted' 
            ORDER BY id DESC LIMIT 1
        ", ['pid' => $patientId]);

        return [
            'appointment' => $apt,
            'labs'        => $labs,
            'admission'   => $adm
        ];
    }
}
