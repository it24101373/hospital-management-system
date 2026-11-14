<?php
/**
 * Hospital Management System (HMS)
 * Laboratory Management Controller (SRS Section 3.6)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class LabController {
    public static function getAll(?string $status = null, string $search = ''): array {
        $sql = "SELECT lt.*, 
                       p.full_name as patient_name, p.patient_code, p.gender, p.dob, p.phone as patient_phone,
                       d.full_name as doctor_name 
                FROM laboratory_tests lt 
                JOIN patients p ON lt.patient_id = p.id 
                LEFT JOIN doctors d ON lt.doctor_id = d.id 
                WHERE 1=1";
        $params = [];
        if (!empty($status)) {
            $sql .= " AND lt.sample_status = :status";
            $params['status'] = $status;
        }
        if (!empty($search)) {
            $sql .= " AND (lt.test_code LIKE :s OR lt.test_name LIKE :s OR p.full_name LIKE :s OR p.patient_code LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY lt.order_date DESC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        $sql = "SELECT lt.*, 
                       p.full_name as patient_name, p.patient_code, p.gender, p.dob, p.phone as patient_phone, p.blood_group,
                       d.full_name as doctor_name, d.specialization 
                FROM laboratory_tests lt 
                JOIN patients p ON lt.patient_id = p.id 
                LEFT JOIN doctors d ON lt.doctor_id = d.id 
                WHERE lt.id = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function createRequest(array $data): int {
        // Generate test code
        $last = Database::fetchOne("SELECT test_code FROM laboratory_tests ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/LAB-(\d+)/', $last['test_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("LAB-%03d", $nextSeq);

        $testId = Database::insert('laboratory_tests', [
            'test_code'       => $code,
            'patient_id'      => (int)$data['patient_id'],
            'doctor_id'       => !empty($data['doctor_id']) ? (int)$data['doctor_id'] : 1,
            'test_name'       => cleanInput($data['test_name']),
            'category'        => cleanInput($data['category'] ?? 'General Diagnostic'),
            'cost'            => (float)($data['cost'] ?? 45.0),
            'order_date'      => date('Y-m-d H:i:s'),
            'sample_status'   => 'Pending',
            'reference_range' => cleanInput($data['reference_range'] ?? '')
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Staff', $user['role_name'] ?? 'Staff', 'Request Lab Test', 'Laboratory', "Ordered lab test {$data['test_name']} ({$code}) for Patient #{$data['patient_id']}", $testId);

        return $testId;
    }

    public static function collectSample(int $id): bool {
        $user = Auth::user();
        $updated = Database::update('laboratory_tests', [
            'sample_status'       => 'Sample Collected',
            'sample_collected_at' => date('Y-m-d H:i:s'),
            'conducted_by'        => $user['full_name'] ?? 'Lab Technician'
        ], 'id = :id', ['id' => $id]);

        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Staff', $user['role_name'] ?? 'Lab', 'Sample Collection', 'Laboratory', "Sample collected for lab test ID #{$id}", $id);

        return $updated >= 0;
    }

    public static function enterResult(int $id, array $data): bool {
        $user = Auth::user();
        $updated = Database::update('laboratory_tests', [
            'sample_status'     => 'Completed',
            'result_entered_at' => date('Y-m-d H:i:s'),
            'results'           => cleanInput($data['results']),
            'reference_range'   => cleanInput($data['reference_range'] ?? ''),
            'is_abnormal'       => !empty($data['is_abnormal']) ? 1 : 0,
            'technician_notes'  => cleanInput($data['technician_notes'] ?? ''),
            'conducted_by'      => $user['full_name'] ?? ($data['conducted_by'] ?? 'Lab Specialist')
        ], 'id = :id', ['id' => $id]);

        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Staff', $user['role_name'] ?? 'Lab', 'Enter Result', 'Laboratory', "Results submitted for lab test ID #{$id}", $id);

        return $updated >= 0;
    }
}
