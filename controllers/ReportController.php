<?php
/**
 * Hospital Management System (HMS)
 * Reports & Analytics Controller (SRS Section 3.10)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

class ReportController {
    public static function getDashboardSummary(): array {
        $today = date('Y-m-d');
        
        $totalPatients = (int)Database::fetchOne("SELECT count(*) as cnt FROM patients")['cnt'];
        $todayAppointments = (int)Database::fetchOne("SELECT count(*) as cnt FROM appointments WHERE appointment_date = :td", ['td' => $today])['cnt'];
        $revenueTotal = (float)Database::fetchOne("SELECT sum(amount) as s FROM payments")['s'];
        $labRequests = (int)Database::fetchOne("SELECT count(*) as cnt FROM laboratory_tests WHERE sample_status != 'Completed'")['cnt'];
        
        // Pharmacy Alerts count
        $lowStockCount = (int)Database::fetchOne("SELECT count(*) as cnt FROM pharmacy_medicines WHERE stock_quantity <= reorder_level")['cnt'];
        $expiringCount = (int)Database::fetchOne("SELECT count(*) as cnt FROM pharmacy_medicines WHERE expiry_date <= date('now', '+30 days')")['cnt'];
        
        // Bed Occupancy
        $admittedCount = (int)Database::fetchOne("SELECT count(*) as cnt FROM admissions WHERE status = 'Admitted'")['cnt'];
        $totalBeds = 20; // Hospital standard capacity
        $bedOccupancyRate = round(($admittedCount / $totalBeds) * 100);

        // Recent appointments
        $recentAppointments = Database::fetchAll("
            SELECT a.*, p.full_name as patient_name, d.full_name as doctor_name 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.id 
            JOIN doctors d ON a.doctor_id = d.id 
            ORDER BY a.appointment_date DESC, a.appointment_time ASC LIMIT 5
        ");

        // Recent audit activities
        $recentAudit = Database::fetchAll("SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT 6");

        return [
            'total_patients'      => $totalPatients,
            'today_appointments'  => $todayAppointments,
            'total_revenue'       => $revenueTotal,
            'lab_requests'        => $labRequests,
            'pharmacy_alerts'     => $lowStockCount + $expiringCount,
            'low_stock_count'     => $lowStockCount,
            'expiring_count'      => $expiringCount,
            'admitted_count'      => $admittedCount,
            'total_beds'          => $totalBeds,
            'bed_occupancy_rate'  => $bedOccupancyRate,
            'recent_appointments' => $recentAppointments,
            'recent_audit'        => $recentAudit
        ];
    }

    public static function getPatientReports(): array {
        $byGender = Database::fetchAll("SELECT gender, count(*) as count FROM patients GROUP BY gender");
        $byType = Database::fetchAll("SELECT patient_type, count(*) as count FROM patients GROUP BY patient_type");
        $byBlood = Database::fetchAll("SELECT blood_group, count(*) as count FROM patients GROUP BY blood_group");
        $total = Database::fetchOne("SELECT count(*) as cnt FROM patients")['cnt'];
        return [
            'total'     => $total,
            'by_gender' => $byGender,
            'by_type'   => $byType,
            'by_blood'  => $byBlood
        ];
    }

    public static function getAppointmentReports(): array {
        $byStatus = Database::fetchAll("SELECT status, count(*) as count FROM appointments GROUP BY status");
        $byDoctor = Database::fetchAll("
            SELECT d.full_name, count(a.id) as count 
            FROM doctors d 
            LEFT JOIN appointments a ON d.id = a.doctor_id 
            GROUP BY d.id
        ");
        $byDepartment = Database::fetchAll("
            SELECT dep.name, count(a.id) as count 
            FROM departments dep 
            LEFT JOIN appointments a ON dep.id = a.department_id 
            GROUP BY dep.id
        ");
        return [
            'by_status'     => $byStatus,
            'by_doctor'     => $byDoctor,
            'by_department' => $byDepartment
        ];
    }

    public static function getRevenueReports(): array {
        $totalBilled = (float)Database::fetchOne("SELECT sum(grand_total) as s FROM bills")['s'];
        $totalPaid = (float)Database::fetchOne("SELECT sum(amount) as s FROM payments")['s'];
        $totalOutstanding = (float)Database::fetchOne("SELECT sum(balance_amount) as s FROM bills")['s'];

        $byMethod = Database::fetchAll("SELECT payment_method, sum(amount) as total FROM payments GROUP BY payment_method");
        $recentPayments = Database::fetchAll("
            SELECT pay.*, p.full_name as patient_name, b.invoice_number 
            FROM payments pay 
            JOIN patients p ON pay.patient_id = p.id 
            JOIN bills b ON pay.bill_id = b.id 
            ORDER BY pay.payment_date DESC LIMIT 10
        ");

        return [
            'total_billed'      => $totalBilled,
            'total_paid'        => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'by_method'         => $byMethod,
            'recent_payments'   => $recentPayments
        ];
    }

    public static function getPharmacyReports(): array {
        $totalItems = (int)Database::fetchOne("SELECT count(*) as cnt FROM pharmacy_medicines")['cnt'];
        $inventoryValue = (float)Database::fetchOne("SELECT sum(unit_price * stock_quantity) as s FROM pharmacy_medicines")['s'];
        $lowStock = Database::fetchAll("SELECT * FROM pharmacy_medicines WHERE stock_quantity <= reorder_level");
        $expired = Database::fetchAll("SELECT * FROM pharmacy_medicines WHERE expiry_date < date('now')");

        return [
            'total_items'     => $totalItems,
            'inventory_value' => $inventoryValue,
            'low_stock'       => $lowStock,
            'expired'         => $expired
        ];
    }

    public static function getLaboratoryReports(): array {
        $byStatus = Database::fetchAll("SELECT sample_status, count(*) as count FROM laboratory_tests GROUP BY sample_status");
        $abnormalCount = (int)Database::fetchOne("SELECT count(*) as cnt FROM laboratory_tests WHERE is_abnormal = 1")['cnt'];
        $byCategory = Database::fetchAll("SELECT category, count(*) as count FROM laboratory_tests GROUP BY category");

        return [
            'by_status'      => $byStatus,
            'abnormal_count' => $abnormalCount,
            'by_category'    => $byCategory
        ];
    }

    public static function getStaffReports(): array {
        $totalStaff = (int)Database::fetchOne("SELECT count(*) as cnt FROM employees")['cnt'];
        $byDept = Database::fetchAll("
            SELECT dep.name, count(e.id) as count 
            FROM departments dep 
            LEFT JOIN employees e ON dep.id = e.department_id 
            GROUP BY dep.id
        ");
        $todayAttendance = Database::fetchAll("
            SELECT status, count(*) as count 
            FROM attendance 
            WHERE attendance_date = :td 
            GROUP BY status
        ", ['td' => date('Y-m-d')]);

        return [
            'total_staff'      => $totalStaff,
            'by_department'    => $byDept,
            'today_attendance' => $todayAttendance
        ];
    }

    public static function exportCsv(string $type): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=hms_' . $type . '_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');

        if ($type === 'patients') {
            fputcsv($output, ['ID', 'Patient Code', 'Full Name', 'DOB', 'Gender', 'Blood Group', 'Phone', 'Type', 'Status']);
            $rows = Database::fetchAll("SELECT id, patient_code, full_name, dob, gender, blood_group, phone, patient_type, status FROM patients ORDER BY id ASC");
            foreach ($rows as $row) fputcsv($output, $row);
        } elseif ($type === 'revenue') {
            fputcsv($output, ['Payment Code', 'Invoice No', 'Patient', 'Amount', 'Payment Method', 'Reference', 'Date']);
            $rows = Database::fetchAll("SELECT pay.payment_code, b.invoice_number, p.full_name, pay.amount, pay.payment_method, pay.transaction_reference, pay.payment_date FROM payments pay JOIN bills b ON pay.bill_id = b.id JOIN patients p ON pay.patient_id = p.id");
            foreach ($rows as $row) fputcsv($output, $row);
        } elseif ($type === 'pharmacy') {
            fputcsv($output, ['Code', 'Name', 'Generic Name', 'Category', 'Dosage Form', 'Unit Price', 'Stock Quantity', 'Expiry Date', 'Status']);
            $rows = Database::fetchAll("SELECT code, name, generic_name, category, dosage_form, unit_price, stock_quantity, expiry_date, status FROM pharmacy_medicines");
            foreach ($rows as $row) fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
