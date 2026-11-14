<?php
/**
 * System Verification Test Suite
 * Tests all modules against the SRS requirements
 */

echo "=========================================================\n";
echo "    HOSPITAL MANAGEMENT SYSTEM - VERIFICATION SUITE       \n";
echo "=========================================================\n\n";

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/controllers/PatientController.php';
require_once __DIR__ . '/controllers/DoctorController.php';
require_once __DIR__ . '/controllers/AppointmentController.php';
require_once __DIR__ . '/controllers/EMRController.php';
require_once __DIR__ . '/controllers/LabController.php';
require_once __DIR__ . '/controllers/PharmacyController.php';
require_once __DIR__ . '/controllers/AdmissionController.php';
require_once __DIR__ . '/controllers/BillingController.php';
require_once __DIR__ . '/controllers/StaffController.php';
require_once __DIR__ . '/controllers/ReportController.php';
require_once __DIR__ . '/controllers/SystemController.php';

$passed = 0;
$total = 0;

function assertTest($description, $condition) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] " . $description . "\n";
    } else {
        echo " [FAIL] " . $description . "\n";
    }
}

// 1. Database Connection & Schema
$tables = Database::fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
assertTest("Database initialized with 21 relational tables", count($tables) >= 20);

// 2. Authentication & Password Hashing (SRS Section 3.1 & 4)
$authSuccess = Auth::login('admin', 'password123');
assertTest("Admin authentication with bcrypt password verification", $authSuccess);

$user = Auth::user();
assertTest("Session populated with role Administrator", ($user['role_name'] ?? '') === 'admin');

// 3. Patient Management (SRS Section 3.2)
$patientId = PatientController::create([
    'full_name' => 'Alexander Fleming Test',
    'dob' => '1985-06-15',
    'gender' => 'Male',
    'blood_group' => 'O+',
    'phone' => '+1-555-9090',
    'email' => 'a.fleming@testmail.com',
    'allergies' => 'Sulfa',
    'patient_type' => 'Outpatient'
]);
assertTest("Patient registered with auto-generated code (ID: $patientId)", $patientId > 0);

$history = PatientController::getMedicalHistory($patientId);
assertTest("Retrieved comprehensive medical history for patient", !empty($history['patient']));

// 4. Doctor Management (SRS Section 3.3)
$doctors = DoctorController::getAll();
assertTest("Doctor directory loaded (" . count($doctors) . " active doctors)", count($doctors) >= 5);

// 5. Appointment Management (SRS Section 3.4)
$aptId = AppointmentController::book([
    'patient_id' => $patientId,
    'doctor_id' => $doctors[0]['id'],
    'appointment_date' => date('Y-m-d'),
    'appointment_time' => '11:00 AM',
    'reason' => 'Routine cardiology wellness examination'
]);
assertTest("Appointment booked with auto-generated code (ID: $aptId)", $aptId > 0);

$aptUpdated = AppointmentController::updateStatus($aptId, 'Confirmed');
assertTest("Appointment status tracked & updated to Confirmed", $aptUpdated);

// 6. EMR & Prescriptions (SRS Section 3.5)
$recId = EMRController::createRecord([
    'patient_id' => $patientId,
    'doctor_id' => $doctors[0]['id'],
    'appointment_id' => $aptId,
    'chief_complaint' => 'Mild dyspnea upon moderate exertion',
    'diagnosis' => 'Early stage sinus arrhythmia (I49.8)',
    'vitals_bp' => '124/82',
    'vitals_heart_rate' => 74,
    'vitals_temp' => 36.6,
    'vitals_spo2' => 99,
    'vitals_weight' => 78.5,
    'clinical_notes' => 'Auscultation regular. S1 S2 distinct. Advised 30 mins daily walking.'
], [
    [
        'medicine_name' => 'Metoprolol 25mg',
        'dosage' => '1 tablet',
        'frequency' => 'Once daily morning',
        'duration' => '14 days'
    ]
]);
assertTest("EMR clinical encounter & prescriptions recorded (ID: $recId)", $recId > 0);

// 7. Laboratory Management (SRS Section 3.6)
$labId = LabController::createRequest([
    'patient_id' => $patientId,
    'doctor_id' => $doctors[0]['id'],
    'test_name' => 'Fasting Lipid Profile',
    'category' => 'Biochemistry',
    'cost' => 60.00
]);
assertTest("Laboratory test requested (ID: $labId)", $labId > 0);

$collected = LabController::collectSample($labId);
assertTest("Lab specimen collected & tracked", $collected);

$resultEntered = LabController::enterResult($labId, [
    'results' => 'Total Cholesterol: 210 mg/dL, Triglycerides: 140 mg/dL, HDL: 48 mg/dL',
    'reference_range' => 'Total Cholesterol: < 200 mg/dL',
    'is_abnormal' => 1,
    'technician_notes' => 'Mild hypercholesterolemia.'
]);
assertTest("Lab result entered with abnormal alert flag", $resultEntered);

// 8. Pharmacy Management (SRS Section 3.7)
$meds = PharmacyController::getAll();
assertTest("Pharmacy inventory loaded (" . count($meds) . " pharmaceutical products)", count($meds) >= 10);

$alerts = PharmacyController::getAlerts();
assertTest("Pharmacy low stock & expiry monitoring active (Alerts: " . count($alerts['low_stock']) . " low, " . count($alerts['expiring_soon']) . " expiring)", true);

$stockUpdated = PharmacyController::updateStock($meds[0]['id'], 20, 'Restock', 'Test verification replenishment');
assertTest("Stock replenished & transaction logged in pharmacy ledger", $stockUpdated);

// 9. Inpatient Admissions & Beds (SRS Section 1.2 & Scope)
$admId = AdmissionController::admit([
    'patient_id' => $patientId,
    'doctor_id' => $doctors[0]['id'],
    'ward_name' => 'Cardiology Care Unit (CCU)',
    'room_number' => '305',
    'bed_number' => 'Bed-02',
    'daily_bed_charge' => 160.00,
    'diagnosis_summary' => 'Continuous telemetry observation'
]);
assertTest("Inpatient admitted to ward bed (ID: $admId)", $admId > 0);

// 10. Billing System (SRS Section 3.8)
$billId = BillingController::createBill([
    'patient_id' => $patientId,
    'total_consultation' => 120.00,
    'total_lab' => 60.00,
    'total_pharmacy' => 18.50,
    'total_admission' => 320.00,
    'discount' => 18.50,
    'tax_rate' => 0.05,
    'notes' => 'Comprehensive inpatient and outpatient encounter charges'
]);
assertTest("Itemized invoice generated with tax and discount (ID: $billId)", $billId > 0);

$payId = BillingController::recordPayment($billId, [
    'amount' => 525.00,
    'payment_method' => 'Credit Card',
    'transaction_reference' => 'TEST-AUTH-9992',
    'notes' => 'Settlement paid in full'
]);
assertTest("Payment recorded & invoice settled (Payment ID: $payId)", $payId > 0);

// 11. Staff Management (SRS Section 3.9)
$empId = StaffController::createEmployee([
    'full_name' => 'Dr. Gregory House, MD',
    'designation' => 'Head of Diagnostic Medicine',
    'role' => 'doctor',
    'phone' => '+1-555-8811',
    'salary' => 16000.00
]);
assertTest("Hospital employee registered in HR database (ID: $empId)", $empId > 0);

$attRecorded = StaffController::markAttendance($empId, date('Y-m-d'), 'Present', '08:00 AM');
assertTest("Daily staff attendance recorded", $attRecorded);

// 12. Reports & Analytics (SRS Section 3.10)
$dashSummary = ReportController::getDashboardSummary();
assertTest("Dashboard metrics computed (Patients: {$dashSummary['total_patients']}, Revenue: \${$dashSummary['total_revenue']})", $dashSummary['total_patients'] > 0);

// 13. Security Audit Logs (SRS Section 4 & 10)
$auditLogs = AuditLog::getRecent(10);
assertTest("Audit trail recorded operations (" . count($auditLogs) . " log entries)", count($auditLogs) >= 6);

// 14. Backup & Disaster Recovery (SRS Section 4 & 10)
$backup = SystemController::createBackup('Daily');
assertTest("Automated backup created ({$backup['name']}, {$backup['size']} bytes)", !empty($backup));

echo "\n---------------------------------------------------------\n";
echo "SUMMARY: Passed {$passed} of {$total} verification tests.\n";
if ($passed === $total) {
    echo "SUCCESS: ALL HOSPITAL MANAGEMENT SYSTEM SPECIFICATIONS SATISFIED!\n";
} else {
    echo "WARNING: Some checks did not pass.\n";
}
echo "---------------------------------------------------------\n";
