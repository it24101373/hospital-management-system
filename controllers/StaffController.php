<?php
/**
 * Hospital Management System (HMS)
 * Staff Management Controller (SRS Section 3.9 & DB Module 13)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class StaffController {
    public static function getEmployees(string $search = '', ?int $deptId = null): array {
        $sql = "SELECT e.*, dep.name as department_name 
                FROM employees e 
                LEFT JOIN departments dep ON e.department_id = dep.id 
                WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (e.employee_code LIKE :s OR e.full_name LIKE :s OR e.designation LIKE :s OR e.phone LIKE :s)";
            $params['s'] = '%' . $search . '%';
        }
        if ($deptId) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = $deptId;
        }
        $sql .= " ORDER BY e.id ASC";
        return Database::fetchAll($sql, $params);
    }

    public static function createEmployee(array $data): int {
        // Generate employee code
        $last = Database::fetchOne("SELECT employee_code FROM employees ORDER BY id DESC LIMIT 1");
        $nextSeq = 1;
        if ($last && preg_match('/EMP-(\d+)/', $last['employee_code'], $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $code = sprintf("EMP-%03d", $nextSeq);

        $empId = Database::insert('employees', [
            'employee_code' => $code,
            'full_name'     => cleanInput($data['full_name']),
            'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'designation'   => cleanInput($data['designation']),
            'role'          => cleanInput($data['role'] ?? 'staff'),
            'phone'         => cleanInput($data['phone']),
            'email'         => cleanInput($data['email'] ?? ''),
            'join_date'     => cleanInput($data['join_date'] ?? date('Y-m-d')),
            'salary'        => (float)($data['salary'] ?? 0.0),
            'status'        => 'Active'
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Admin', $user['role_name'] ?? 'Admin', 'Register Employee', 'Staff', "Registered employee {$data['full_name']} ({$code})", $empId);

        return $empId;
    }

    public static function getAttendance(?string $date = null): array {
        if (!$date) $date = date('Y-m-d');
        $sql = "SELECT a.*, e.employee_code, e.full_name, e.designation, dep.name as department_name 
                FROM attendance a 
                JOIN employees e ON a.employee_id = e.id 
                LEFT JOIN departments dep ON e.department_id = dep.id 
                WHERE a.attendance_date = :dt 
                ORDER BY e.full_name ASC";
        return Database::fetchAll($sql, ['dt' => $date]);
    }

    public static function markAttendance(int $employeeId, string $date, string $status, ?string $checkIn = null, ?string $checkOut = null, string $notes = ''): bool {
        $existing = Database::fetchOne("SELECT id FROM attendance WHERE employee_id = :eid AND attendance_date = :dt", [
            'eid' => $employeeId,
            'dt'  => $date
        ]);

        if ($existing) {
            Database::update('attendance', [
                'status'         => cleanInput($status),
                'check_in_time'  => cleanInput($checkIn),
                'check_out_time' => cleanInput($checkOut),
                'notes'          => cleanInput($notes)
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            Database::insert('attendance', [
                'employee_id'     => $employeeId,
                'attendance_date' => $date,
                'check_in_time'   => cleanInput($checkIn),
                'check_out_time'  => cleanInput($checkOut),
                'status'          => cleanInput($status),
                'notes'           => cleanInput($notes)
            ]);
        }

        return true;
    }

    public static function getLeaves(?string $status = null): array {
        $sql = "SELECT l.*, e.employee_code, e.full_name, e.designation, dep.name as department_name 
                FROM leaves l 
                JOIN employees e ON l.employee_id = e.id 
                LEFT JOIN departments dep ON e.department_id = dep.id 
                WHERE 1=1";
        $params = [];
        if (!empty($status)) {
            $sql .= " AND l.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY l.id DESC";
        return Database::fetchAll($sql, $params);
    }

    public static function applyLeave(array $data): int {
        $start = new DateTime($data['start_date']);
        $end = new DateTime($data['end_date']);
        $days = $start->diff($end)->days + 1;

        $leaveId = Database::insert('leaves', [
            'employee_id' => (int)$data['employee_id'],
            'leave_type'  => cleanInput($data['leave_type']),
            'start_date'  => cleanInput($data['start_date']),
            'end_date'    => cleanInput($data['end_date']),
            'days_count'  => $days,
            'reason'      => cleanInput($data['reason']),
            'status'      => 'Pending'
        ]);

        $user = Auth::user();
        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Staff', $user['role_name'] ?? 'Staff', 'Apply Leave', 'Staff', "Leave request submitted for Employee ID #{$data['employee_id']}", $leaveId);

        return $leaveId;
    }

    public static function updateLeaveStatus(int $leaveId, string $status): bool {
        $user = Auth::user();
        $updated = Database::update('leaves', [
            'status'      => cleanInput($status),
            'approved_by' => $user['full_name'] ?? 'Administrator'
        ], 'id = :id', ['id' => $leaveId]);

        AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Admin', $user['role_name'] ?? 'Admin', 'Leave Status Update', 'Staff', "Updated leave #{$leaveId} status to {$status}", $leaveId);

        return $updated >= 0;
    }
}
