<?php
/**
 * Hospital Management System (HMS)
 * Appointment Controller (SRS Section 3.4)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AppointmentController {
    public static function getAll(?string $date = null, ?int $doctorId = null, ?string $status = null): array {
        $sql = "SELECT a.*, 
                       p.full_name as patient_name, p.patient_code, p.phone as patient_phone,
                       d.full_name as doctor_name, d.room_number,
                       dep.name as department_name 
                FROM appointments a 
                JOIN patients p ON a.patient_id = p.id 
                JOIN doctors d ON a.doctor_id = d.id 
                JOIN departments dep ON a.department_id = dep.id 
                WHERE 1=1";
        $params = [];
        if (!empty($date)) {
            $sql .= " AND a.appointment_date = :date";
            $params['date'] = $date;
        }
        if ($doctorId) {
            $sql .= " AND a.doctor_id = :doc";
            $params['doc'] = $doctorId;
        }
        if (!empty($status)) {
            $sql .= " AND a.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC";
        return Database::fetchAll($sql, $params);
    }

    public static function getById(int $id): ?array {
        $sql = "SELECT a.*, 
                       p.full_name as patient_name, p.patient_code, p.phone as patient_phone, p.allergies,
                       d.full_name as doctor_name, d.consultation_fee, d.room_number,
                       dep.name as department_name 
                FROM appointments a 
                JOIN patients p ON a.patient_id = p.id 
                JOIN doctors d ON a.doctor_id = d.id 
                JOIN departments dep ON a.department_id = dep.id 
                WHERE a.id = :id";
        return Database::fetchOne($sql, ['id' => $id]);
    }

    public static function book(array $data): int {
        // Generate appointment code
        $last = Database::fetchOne("SELECT appointment_code FROM appointments ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/APT-(\d+)/', $last['appointment_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("APT-%03d", $nextSeq);

        // Fetch department from doctor if not explicitly provided
        $departmentId = $data['department_id'] ?? null;
        if (!$departmentId && !empty($data['doctor_id'])) {
            $doc = Database::fetchOne("SELECT department_id FROM doctors WHERE id = :id", ['id' => $data['doctor_id']]);
            $departmentId = $doc['department_id'] ?? 1;
        }

        $aptId = Database::insert('appointments', [
            'appointment_code' => $code,
            'patient_id'       => (int)$data['patient_id'],
            'doctor_id'        => (int)$data['doctor_id'],
            'department_id'    => (int)$departmentId,
            'appointment_date' => cleanInput($data['appointment_date']),
            'appointment_time' => cleanInput($data['appointment_time']),
            'reason'           => cleanInput($data['reason'] ?? 'General Consultation'),
            'status'           => cleanInput($data['status'] ?? 'Scheduled'),
            'notes'            => cleanInput($data['notes'] ?? '')
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Book Appointment', 'Appointments', "Booked appointment {$code} for Patient ID #{$data['patient_id']}", $aptId);

        return $aptId;
    }

    public static function updateStatus(int $id, string $status, string $notes = ''): bool {
        $validStatuses = ['Scheduled', 'Confirmed', 'Completed', 'Cancelled', 'In-Progress'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $updateData = ['status' => $status];
        if (!empty($notes)) {
            $updateData['notes'] = cleanInput($notes);
        }

        $res = Database::update('appointments', $updateData, 'id = :id', ['id' => $id]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Appointment Status Change', 'Appointments', "Changed status of appointment ID #{$id} to {$status}", $id);

        return $res >= 0;
    }

    public static function reschedule(int $id, string $newDate, string $newTime, string $reason = ''): bool {
        $apt = self::getById($id);
        if (!$apt) return false;

        $newNotes = ($apt['notes'] ? $apt['notes'] . " | " : "") . "Rescheduled to {$newDate} {$newTime}. Reason: " . cleanInput($reason);

        $res = Database::update('appointments', [
            'appointment_date' => cleanInput($newDate),
            'appointment_time' => cleanInput($newTime),
            'status'           => 'Scheduled',
            'notes'            => $newNotes
        ], 'id = :id', ['id' => $id]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Staff', 'Reschedule Appointment', 'Appointments', "Rescheduled appointment ID #{$id} to {$newDate} {$newTime}", $id);

        return $res >= 0;
    }

    public static function cancel(int $id, string $reason = ''): bool {
        return self::updateStatus($id, 'Cancelled', 'Cancellation reason: ' . $reason);
    }
}
