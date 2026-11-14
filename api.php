<?php
/**
 * Hospital Management System (HMS)
 * Asynchronous JSON API Endpoints
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/controllers/PatientController.php';
require_once __DIR__ . '/controllers/BillingController.php';

$action = $_GET['action'] ?? '';

if ($action === 'switch_role') {
    $role = $_GET['role'] ?? '';
    if (array_key_exists($role, ROLES)) {
        if (Auth::quickSwitchRole($role)) {
            echo json_encode(['success' => true, 'role' => $role, 'role_label' => ROLES[$role]]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Role not available or inactive']);
    exit;
}

// Require login for other endpoints
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'check_session') {
    echo json_encode([
        'success'   => true,
        'user'      => Auth::user(),
        'remaining' => SESSION_LIFETIME - (time() - ($_SESSION['last_activity'] ?? time()))
    ]);
    exit;
}

if ($action === 'get_patient') {
    $id = (int)($_GET['id'] ?? 0);
    $p = PatientController::getById($id);
    echo json_encode(['success' => (bool)$p, 'patient' => $p]);
    exit;
}

if ($action === 'get_unbilled') {
    $patientId = (int)($_GET['patient_id'] ?? 0);
    $unbilled = BillingController::getPatientUnbilledItems($patientId);
    echo json_encode(['success' => true, 'data' => $unbilled]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid API action']);
