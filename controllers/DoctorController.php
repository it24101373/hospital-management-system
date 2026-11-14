<?php
/**
 * Hospital Management System (HMS)
 * Doctor Controller (SRS Section 3.3)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class DoctorController {
    public static function getAll(string $search = '', ?int $departmentId = null): array {
        $sql = "SELECT d.*, dep.name as department_name, dep.code as department_code 
                FROM doctors d 
                JOIN departments dep ON d.department_id = dep.id 
                WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (d.doctor_code LIKE :s OR d.full_name LIKE :s OR d.specialization LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        if ($departmentId) {
            $sql .= " AND d.department_id = :dept";
            $params['dept'] = $departmentId;
        }
        $sql .= " ORDER BY d.id ASC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        $sql = "SELECT d.*, dep.name as department_name 
                FROM doctors d 
                JOIN departments dep ON d.department_id = dep.id 
                WHERE d.id = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function getDepartments(): array {
        return Database::fetchAll("SELECT * FROM departments ORDER BY name ASC");
    }

    public static function create(array $data): int {
        // Generate doctor code
        $last = Database::fetchOne("SELECT doctor_code FROM doctors ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/DOC-(\d+)/', $last['doctor_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("DOC-%03d", $nextSeq);

        $docId = Database::insert('doctors', [
            'doctor_code'      => $code,
            'full_name'        => cleanInput($data['full_name']),
            'department_id'    => (int)$data['department_id'],
            'specialization'   => cleanInput($data['specialization']),
            'qualification'    => cleanInput($data['qualification'] ?? ''),
            'phone'            => cleanInput($data['phone'] ?? ''),
            'email'            => cleanInput($data['email'] ?? ''),
            'consultation_fee' => (float)($data['consultation_fee'] ?? 50.0),
            'room_number'      => cleanInput($data['room_number'] ?? ''),
            'schedule_days'    => cleanInput($data['schedule_days'] ?? 'Mon,Tue,Wed,Thu,Fri'),
            'schedule_hours'   => cleanInput($data['schedule_hours'] ?? '09:00 - 17:00'),
            'status'           => cleanInput($data['status'] ?? 'active')
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Add Doctor', 'Doctors', "Added new doctor: {$data['full_name']} ({$code})", $docId);

        return $docId;
    }

    public static function update(int $id, array $data): bool {
        $updated = Database::update('doctors', [
            'full_name'        => cleanInput($data['full_name']),
            'department_id'    => (int)$data['department_id'],
            'specialization'   => cleanInput($data['specialization']),
            'qualification'    => cleanInput($data['qualification'] ?? ''),
            'phone'            => cleanInput($data['phone'] ?? ''),
            'email'            => cleanInput($data['email'] ?? ''),
            'consultation_fee' => (float)($data['consultation_fee'] ?? 50.0),
            'room_number'      => cleanInput($data['room_number'] ?? ''),
            'schedule_days'    => cleanInput($data['schedule_days'] ?? 'Mon,Tue,Wed,Thu,Fri'),
            'schedule_hours'   => cleanInput($data['schedule_hours'] ?? '09:00 - 17:00'),
            'status'           => cleanInput($data['status'] ?? 'active')
        ], 'id = :id', ['id' => $id]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Update Doctor', 'Doctors', "Updated doctor details for ID #{$id}", $id);

        return $updated >= 0;
    }
}
