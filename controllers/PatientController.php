<?php
/**
 * Hospital Management System (HMS)
 * Patient Controller (SRS Section 3.2)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class PatientController {
    public static function getAll(string $search = '', string $type = ''): array {
        $sql = "SELECT * FROM patients WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (patient_code LIKE :s OR full_name LIKE :s OR phone LIKE :s OR email LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        if (!empty($type)) {
            $sql .= " AND patient_type = :type";
            $params['type'] = $type;
        }
        $sql .= " ORDER BY id DESC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        return Database::fetchOne("SELECT * FROM patients WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int {
        // Generate patient code
        $year = date('Y');
        $last = Database::fetchOne("SELECT patient_code FROM patients ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/PAT-\d{4}-(\d+)/', $last['patient_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("PAT-%s-%03d", $year, $nextSeq);

        $patientId = Database::insert('patients', [
            'patient_code'            => $code,
            'full_name'               => cleanInput($data['full_name']),
            'dob'                     => cleanInput($data['dob']),
            'gender'                  => cleanInput($data['gender']),
            'blood_group'             => cleanInput($data['blood_group'] ?? 'Unknown'),
            'phone'                   => cleanInput($data['phone']),
            'email'                   => cleanInput($data['email'] ?? ''),
            'address'                 => cleanInput($data['address'] ?? ''),
            'emergency_contact_name'  => cleanInput($data['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => cleanInput($data['emergency_contact_phone'] ?? ''),
            'allergies'               => cleanInput($data['allergies'] ?? ''),
            'patient_type'            => cleanInput($data['patient_type'] ?? 'Outpatient'),
            'status'                  => 'Active'
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Register Patient', 'Patients', "Registered patient: {$data['full_name']} ({$code})", $patientId);

        return $patientId;
    }

    public static function update(int $id, array $data): bool {
        $updated = Database::update('patients', [
            'full_name'               => cleanInput($data['full_name']),
            'dob'                     => cleanInput($data['dob']),
            'gender'                  => cleanInput($data['gender']),
            'blood_group'             => cleanInput($data['blood_group'] ?? 'Unknown'),
            'phone'                   => cleanInput($data['phone']),
            'email'                   => cleanInput($data['email'] ?? ''),
            'address'                 => cleanInput($data['address'] ?? ''),
            'emergency_contact_name'  => cleanInput($data['emergency_contact_name'] ?? ''),
            'emergency_contact_phone' => cleanInput($data['emergency_contact_phone'] ?? ''),
            'allergies'               => cleanInput($data['allergies'] ?? ''),
            'patient_type'            => cleanInput($data['patient_type'] ?? 'Outpatient'),
            'status'                  => cleanInput($data['status'] ?? 'Active')
        ], 'id = :id', ['id' => $id]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Update Patient', 'Patients', "Updated details for patient ID #{$id}", $id);

        return $updated >= 0;
    }

    public static function getMedicalHistory(int $patientId): array {
        // Aggregate full comprehensive history
        $patient = self::getById($patientId);
        if (!$patient) return [];

        $appointments = Database::fetchAll("
            SELECT a.*, d.full_name as doctor_name, dep.name as department_name 
            FROM appointments a 
            JOIN doctors d ON a.doctor_id = d.id 
            JOIN departments dep ON a.department_id = dep.id 
            WHERE a.patient_id = :pid 
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ", ['pid' => $patientId]);

        $records = Database::fetchAll("
            SELECT mr.*, d.full_name as doctor_name 
            FROM medical_records mr 
            JOIN doctors d ON mr.doctor_id = d.id 
            WHERE mr.patient_id = :pid 
            ORDER BY mr.record_date DESC
        ", ['pid' => $patientId]);

        foreach ($records as &$rec) {
            $rec['prescriptions'] = Database::fetchAll("SELECT * FROM prescriptions WHERE medical_record_id = :mid", ['mid' => $rec['id']]);
        }

        $labTests = Database::fetchAll("
            SELECT lt.*, d.full_name as doctor_name 
            FROM laboratory_tests lt 
            LEFT JOIN doctors d ON lt.doctor_id = d.id 
            WHERE lt.patient_id = :pid 
            ORDER BY lt.order_date DESC
        ", ['pid' => $patientId]);

        $admissions = Database::fetchAll("
            SELECT adm.*, d.full_name as doctor_name 
            FROM admissions adm 
            JOIN doctors d ON adm.doctor_id = d.id 
            WHERE adm.patient_id = :pid 
            ORDER BY adm.admission_date DESC
        ", ['pid' => $patientId]);

        $bills = Database::fetchAll("
            SELECT * FROM bills WHERE patient_id = :pid ORDER BY bill_date DESC
        ", ['pid' => $patientId]);

        $documents = Database::fetchAll("
            SELECT * FROM patient_documents WHERE patient_id = :pid ORDER BY upload_date DESC
        ", ['pid' => $patientId]);

        return [
            'patient'      => $patient,
            'appointments' => $appointments,
            'records'      => $records,
            'lab_tests'    => $labTests,
            'admissions'   => $admissions,
            'bills'        => $bills,
            'documents'    => $documents
        ];
    }

    public static function uploadDocument(int $patientId, array $file, string $description = ''): bool {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'txt'];
        if (!in_array($ext, $allowedExts)) {
            return false;
        }

        $fileName = 'doc_' . $patientId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = UPLOADS_DIR . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            Database::insert('patient_documents', [
                'patient_id'    => $patientId,
                'file_name'     => $fileName,
                'original_name' => cleanInput($file['name']),
                'file_type'     => $ext,
                'file_size'     => (int)$file['size'],
                'description'   => cleanInput($description),
                'uploaded_by'   => Auth::user()['full_name'] ?? 'Staff'
            ]);

            $user = Auth::user();
            AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Upload Document', 'Patients', "Uploaded document {$file['name']} for patient ID #{$patientId}", $patientId);

            return true;
        }
        return false;
    }
}
