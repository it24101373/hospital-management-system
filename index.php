<?php
/**
 * Hospital Management System (HMS)
 * Main Front Controller & Application Router
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Controllers
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

// Route action handlers (POST or GET operations)
$action = $_GET['action'] ?? null;

if ($action) {
    // 1. Authentication Actions
    if ($action === 'login') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            if (Auth::login($username, $password)) {
                setFlash('success', 'Welcome back, ' . $_SESSION['full_name'] . '!');
                header('Location: index.php?page=dashboard');
                exit;
            } else {
                header('Location: index.php?page=login&error=1');
                exit;
            }
        }
    } elseif ($action === 'logout') {
        Auth::logout();
        setFlash('info', 'You have been successfully signed out.');
        header('Location: index.php?page=login');
        exit;
    }

    // Require authentication for all system operations
    Auth::requireLogin();

    // 2. Patient Actions
    if ($action === 'create_patient' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = PatientController::create($_POST);
        setFlash('success', 'Patient registered successfully with patient code.');
        header('Location: index.php?page=patient_detail&id=' . $id);
        exit;
    } elseif ($action === 'upload_patient_doc' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $patientId = (int)$_POST['patient_id'];
        if (isset($_FILES['document']) && PatientController::uploadDocument($patientId, $_FILES['document'], $_POST['description'] ?? '')) {
            setFlash('success', 'Document uploaded and attached to medical record.');
        } else {
            setFlash('error', 'Failed to upload document. Please verify file format.');
        }
        header('Location: index.php?page=patient_detail&id=' . $patientId);
        exit;
    }

    // 3. Appointment Actions
    elseif ($action === 'book_appointment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = AppointmentController::book($_POST);
        setFlash('success', 'Appointment scheduled successfully.');
        header('Location: index.php?page=appointments');
        exit;
    } elseif ($action === 'update_appointment_status') {
        $id = (int)$_GET['id'];
        $status = $_GET['status'] ?? 'Confirmed';
        AppointmentController::updateStatus($id, $status);
        setFlash('success', "Appointment status updated to {$status}.");
        header('Location: index.php?page=appointments');
        exit;
    } elseif ($action === 'reschedule_appointment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        AppointmentController::reschedule($id, $_POST['new_date'], $_POST['new_time'], $_POST['reason'] ?? '');
        setFlash('success', 'Appointment rescheduled successfully.');
        header('Location: index.php?page=appointments');
        exit;
    } elseif ($action === 'cancel_appointment') {
        $id = (int)$_GET['id'];
        AppointmentController::cancel($id, 'User cancelled from schedule');
        setFlash('info', 'Appointment has been cancelled.');
        header('Location: index.php?page=appointments');
        exit;
    }

    // 4. Doctor Actions
    elseif ($action === 'create_doctor' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        DoctorController::create($_POST);
        setFlash('success', 'Doctor profile created successfully.');
        header('Location: index.php?page=doctors');
        exit;
    }

    // 5. Clinical Encounter & EMR Actions
    elseif ($action === 'create_emr' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $prescriptions = $_POST['prescriptions'] ?? [];
        $recId = EMRController::createRecord($_POST, $prescriptions);
        setFlash('success', 'Clinical encounter and prescriptions recorded successfully.');
        header('Location: index.php?page=patient_detail&id=' . (int)$_POST['patient_id']);
        exit;
    }

    // 6. Laboratory Actions
    elseif ($action === 'order_lab' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $labId = LabController::createRequest($_POST);
        setFlash('success', 'Laboratory test ordered successfully.');
        header('Location: index.php?page=laboratory');
        exit;
    } elseif ($action === 'collect_lab_sample') {
        $labId = (int)$_GET['id'];
        LabController::collectSample($labId);
        setFlash('success', 'Sample collected and marked in analysis.');
        header('Location: index.php?page=laboratory');
        exit;
    } elseif ($action === 'enter_lab_result' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $labId = (int)$_POST['id'];
        LabController::enterResult($labId, $_POST);
        setFlash('success', 'Diagnostic lab results recorded and report generated.');
        header('Location: index.php?page=laboratory');
        exit;
    }

    // 7. Pharmacy Actions
    elseif ($action === 'create_medicine' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        PharmacyController::createMedicine($_POST);
        setFlash('success', 'Medication added to inventory.');
        header('Location: index.php?page=pharmacy');
        exit;
    } elseif ($action === 'restock_medicine' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int)$_POST['id'];
        $qty = (int)$_POST['quantity'];
        PharmacyController::updateStock($id, $qty, 'Restock', $_POST['notes'] ?? '');
        setFlash('success', "Stock replenished (+{$qty} units).");
        header('Location: index.php?page=pharmacy');
        exit;
    } elseif ($action === 'dispense_prescription') {
        $prescId = (int)$_GET['id'];
        PharmacyController::dispensePrescription($prescId);
        setFlash('success', 'Prescription dispensed and stock updated.');
        header('Location: index.php?page=pharmacy');
        exit;
    }

    // 8. Inpatient Admissions Actions
    elseif ($action === 'admit_patient' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        AdmissionController::admit($_POST);
        setFlash('success', 'Patient admitted to inpatient ward.');
        header('Location: index.php?page=admissions');
        exit;
    } elseif ($action === 'discharge_patient' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $admId = (int)$_POST['id'];
        AdmissionController::discharge($admId, $_POST['discharge_notes'] ?? '');
        setFlash('success', 'Patient discharged successfully.');
        header('Location: index.php?page=admissions');
        exit;
    }

    // 9. Billing Actions
    elseif ($action === 'create_bill' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $billId = BillingController::createBill($_POST);
        setFlash('success', 'Hospital invoice generated successfully.');
        header('Location: index.php?page=print_invoice&id=' . $billId);
        exit;
    } elseif ($action === 'record_payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $billId = (int)$_POST['bill_id'];
        BillingController::recordPayment($billId, $_POST);
        setFlash('success', 'Payment recorded and receipt generated.');
        header('Location: index.php?page=billing');
        exit;
    }

    // 10. Staff Actions
    elseif ($action === 'create_employee' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        StaffController::createEmployee($_POST);
        setFlash('success', 'Employee registered in hospital HR directory.');
        header('Location: index.php?page=staff');
        exit;
    } elseif ($action === 'mark_attendance') {
        $empId = (int)$_GET['emp_id'];
        $st = $_GET['status'];
        StaffController::markAttendance($empId, date('Y-m-d'), $st, date('h:i A'));
        setFlash('success', "Attendance status updated to {$st}.");
        header('Location: index.php?page=staff&tab=attendance');
        exit;
    } elseif ($action === 'apply_leave' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        StaffController::applyLeave($_POST);
        setFlash('success', 'Leave request submitted.');
        header('Location: index.php?page=staff&tab=leaves');
        exit;
    } elseif ($action === 'update_leave_status') {
        $leaveId = (int)$_GET['id'];
        $st = $_GET['status'];
        StaffController::updateLeaveStatus($leaveId, $st);
        setFlash('success', "Leave request {$st}.");
        header('Location: index.php?page=staff&tab=leaves');
        exit;
    }

    // 11. Backup & Recovery Actions
    elseif ($action === 'create_backup') {
        $bk = SystemController::createBackup('Manual');
        if ($bk) {
            setFlash('success', 'Full database snapshot created successfully: ' . $bk['name']);
        } else {
            setFlash('error', 'Failed to create backup.');
        }
        header('Location: index.php?page=backup_restore');
        exit;
    } elseif ($action === 'restore_backup') {
        $file = $_GET['file'] ?? '';
        if (SystemController::restoreBackup($file)) {
            setFlash('success', 'Database successfully restored from snapshot ' . htmlspecialchars($file));
        } else {
            setFlash('error', 'Failed to restore database.');
        }
        header('Location: index.php?page=backup_restore');
        exit;
    } elseif ($action === 'download_backup') {
        $file = $_GET['file'] ?? '';
        SystemController::downloadBackup($file);
        exit;
    }

    // 12. CSV Export
    elseif ($action === 'export_csv') {
        $type = $_GET['type'] ?? 'patients';
        ReportController::exportCsv($type);
        exit;
    }
}

// Route Page Views
$page = $_GET['page'] ?? 'dashboard';

// If not logged in, force login page
if (!Auth::check() && $page !== 'login') {
    header('Location: index.php?page=login');
    exit;
}

// If already logged in and visiting login page, redirect to dashboard
if (Auth::check() && $page === 'login') {
    header('Location: index.php?page=dashboard');
    exit;
}

// View Dispatcher
$viewFile = match($page) {
    'login'          => __DIR__ . '/views/login.php',
    'dashboard'      => __DIR__ . '/views/dashboard.php',
    'patients'       => __DIR__ . '/views/patients.php',
    'patient_detail' => __DIR__ . '/views/patient_detail.php',
    'appointments'   => __DIR__ . '/views/appointments.php',
    'doctors'        => __DIR__ . '/views/doctors.php',
    'emr'            => __DIR__ . '/views/emr.php',
    'laboratory'     => __DIR__ . '/views/laboratory.php',
    'pharmacy'       => __DIR__ . '/views/pharmacy.php',
    'admissions'     => __DIR__ . '/views/admissions.php',
    'billing'        => __DIR__ . '/views/billing.php',
    'staff'          => __DIR__ . '/views/staff.php',
    'reports'        => __DIR__ . '/views/reports.php',
    'audit_logs'     => __DIR__ . '/views/audit_logs.php',
    'backup_restore' => __DIR__ . '/views/backup_restore.php',
    'print_invoice'      => __DIR__ . '/views/print_invoice.php',
    'print_prescription' => __DIR__ . '/views/print_prescription.php',
    'print_lab_report'   => __DIR__ . '/views/print_lab_report.php',
    default          => null
};

if ($viewFile && file_exists($viewFile)) {
    require_once $viewFile;
} else {
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1><p><a href='index.php'>Return to Dashboard</a></p>";
}
