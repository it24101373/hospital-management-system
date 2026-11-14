<?php
/**
 * Hospital Management System (HMS)
 * EMR (Electronic Medical Records) Controller (SRS Section 3.5)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class EMRController {
    public static function getAll(?int $patientId = null): array {
        $sql = "SELECT mr.*, 
                       p.full_name as patient_name, p.patient_code, p.gender, p.dob, p.blood_group,
                       d.full_name as doctor_name, d.specialization 
                FROM medical_records mr 
                JOIN patients p ON mr.patient_id = p.id 
                JOIN doctors d ON mr.doctor_id = d.id 
                WHERE 1=1";
        $params = [];
        if ($patientId) {
            $sql .= " AND mr.patient_id = :pid";
            $params['pid'] = $patientId;
        }
        $sql .= " ORDER BY mr.record_date DESC";
        $records = Database::fetchAll($sql, $params);

        foreach ($records as &$rec) {
            $rec['prescriptions'] = Database::fetchAll("SELECT * FROM prescriptions WHERE medical_record_id = :mid", ['mid' => $rec['id']]);
        }
        return $records;
    }

    public static function getById(int $id): ?array {
        $sql = "SELECT mr.*, 
                       p.full_name as patient_name, p.patient_code, p.gender, p.dob, p.blood_group, p.allergies,
                       d.full_name as doctor_name, d.specialization, d.qualification 
                FROM medical_records mr 
                JOIN patients p ON mr.patient_id = p.id 
                JOIN doctors d ON mr.doctor_id = d.id 
                WHERE mr.id = :id";
        $rec = Database::fetchOne($sql, ['id' => $id]);
        if ($rec) {
            $rec['prescriptions'] = Database::fetchAll("SELECT * FROM prescriptions WHERE medical_record_id = :mid", ['mid' => $rec['id']]);
        }
        return $rec;
    }

    public static function createRecord(array $data, array $prescriptions = []): int {
        $recordId = Database::insert('medical_records', [
            'patient_id'        => (int)$data['patient_id'],
            'doctor_id'         => (int)$data['doctor_id'],
            'appointment_id'    => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'admission_id'      => !empty($data['admission_id']) ? (int)$data['admission_id'] : null,
            'record_date'       => !empty($data['record_date']) ? cleanInput($data['record_date']) : date('Y-m-d H:i:s'),
            'chief_complaint'   => cleanInput($data['chief_complaint'] ?? ''),
            'diagnosis'         => cleanInput($data['diagnosis']),
            'vitals_bp'         => cleanInput($data['vitals_bp'] ?? ''),
            'vitals_heart_rate' => !empty($data['vitals_heart_rate']) ? (int)$data['vitals_heart_rate'] : null,
            'vitals_temp'       => !empty($data['vitals_temp']) ? (float)$data['vitals_temp'] : null,
            'vitals_spo2'       => !empty($data['vitals_spo2']) ? (int)$data['vitals_spo2'] : null,
            'vitals_weight'     => !empty($data['vitals_weight']) ? (float)$data['vitals_weight'] : null,
            'clinical_notes'    => cleanInput($data['clinical_notes'] ?? ''),
            'treatment_plan'    => cleanInput($data['treatment_plan'] ?? '')
        ]);

        // Insert Prescriptions
        if (!empty($prescriptions)) {
            foreach ($prescriptions as $p) {
                if (empty($p['medicine_name'])) continue;
                Database::insert('prescriptions', [
                    'medical_record_id' => $recordId,
                    'patient_id'        => (int)$data['patient_id'],
                    'doctor_id'         => (int)$data['doctor_id'],
                    'medicine_name'     => cleanInput($p['medicine_name']),
                    'dosage'            => cleanInput($p['dosage'] ?? '1 tablet'),
                    'frequency'         => cleanInput($p['frequency'] ?? 'Once daily'),
                    'duration'          => cleanInput($p['duration'] ?? '7 days'),
                    'instructions'      => cleanInput($p['instructions'] ?? ''),
                    'is_dispensed'      => 0
                ]);
            }
        }

        // If linked to an appointment, mark appointment as Completed
        if (!empty($data['appointment_id'])) {
            Database::update('appointments', ['status' => 'Completed'], 'id = :id', ['id' => (int)$data['appointment_id']]);
        }

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Doctor', $user['role_name'] ?? 'Doctor', 'Add EMR Record', 'EMR', "Added clinical encounter record #{$recordId} for Patient #{$data['patient_id']}", $recordId);

        return $recordId;
    }
}
