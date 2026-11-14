<?php
/**
 * Hospital Management System (HMS)
 * Inpatient & Admission Controller (SRS Scope & DB Module 6)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AdmissionController {
    public static function getAll(?string $status = null): array {
        $sql = "SELECT adm.*, 
                       p.full_name as patient_name, p.patient_code, p.phone as patient_phone, p.blood_group,
                       d.full_name as doctor_name 
                FROM admissions adm 
                JOIN patients p ON adm.patient_id = p.id 
                JOIN doctors d ON adm.doctor_id = d.id 
                WHERE 1=1";
        $params = [];
        if (!empty($status)) {
            $sql .= " AND adm.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY adm.admission_date DESC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        $sql = "SELECT adm.*, 
                       p.full_name as patient_name, p.patient_code, p.gender, p.dob, p.phone as patient_phone,
                       d.full_name as doctor_name 
                FROM admissions adm 
                JOIN patients p ON adm.patient_id = p.id 
                JOIN doctors d ON adm.doctor_id = d.id 
                WHERE adm.id = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function admit(array $data): int {
        // Generate admission code
        $year = date('Y');
        $last = Database::fetchOne("SELECT admission_code FROM admissions ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/ADM-\d{4}-(\d+)/', $last['admission_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("ADM-%s-%03d", $year, $nextSeq);

        $admId = Database::insert('admissions', [
            'admission_code'    => $code,
            'patient_id'        => (int)$data['patient_id'],
            'doctor_id'         => (int)$data['doctor_id'],
            'ward_name'         => cleanInput($data['ward_name']),
            'room_number'       => cleanInput($data['room_number']),
            'bed_number'        => cleanInput($data['bed_number']),
            'admission_date'    => !empty($data['admission_date']) ? cleanInput($data['admission_date']) : date('Y-m-d H:i:s'),
            'daily_bed_charge'  => (float)($data['daily_bed_charge'] ?? 120.0),
            'status'            => 'Admitted',
            'diagnosis_summary' => cleanInput($data['diagnosis_summary'] ?? '')
        ]);

        // Update patient status to Inpatient
        Database::update('patients', ['patient_type' => 'Inpatient'], 'id = :id', ['id' => (int)$data['patient_id']]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Staff', $user['role_name'] ?? 'Staff', 'Admit Patient', 'Admissions', "Admitted patient #{$data['patient_id']} to {$data['ward_name']} Bed {$data['bed_number']} ({$code})", $admId);

        return $admId;
    }

    public static function discharge(int $id, string $dischargeNotes = ''): bool {
        $adm = self::getById($id);
        if (!$adm) return false;

        $dischargeDate = date('Y-m-d H:i:s');
        Database::update('admissions', [
            'status'          => 'Discharged',
            'discharge_date'  => $dischargeDate,
            'discharge_notes' => cleanInput($dischargeNotes)
        ], 'id = :id', ['id' => $id]);

        // Revert patient type to Outpatient
        Database::update('patients', ['patient_type' => 'Outpatient'], 'id = :id', ['id' => $adm['patient_id']]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Staff', $user['role_name'] ?? 'Staff', 'Discharge Patient', 'Admissions', "Discharged admission record #{$id} for Patient #{$adm['patient_id']}", $id);

        return true;
    }
}
