<?php
/**
 * Hospital Management System (HMS)
 * Main Configuration File
 */

// Define application root
define('APP_ROOT', dirname(__DIR__));
define('DATA_DIR', APP_ROOT . '/data');
define('UPLOADS_DIR', APP_ROOT . '/public/uploads');
define('BACKUPS_DIR', APP_ROOT . '/backups');
define('SESSIONS_DIR', APP_ROOT . '/sessions');
define('DB_FILE', APP_ROOT . '/database.sqlite');

// Ensure local writable directories exist
foreach ([UPLOADS_DIR, BACKUPS_DIR, SESSIONS_DIR] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Hospital Details
define('HOSPITAL_NAME', 'Apex Memorial Hospital');
define('HOSPITAL_TAGLINE', 'Excellence in Healthcare & Patient Care');
define('HOSPITAL_ADDRESS', '45 Healthcare Boulevard, Medical District, Tech City');
define('HOSPITAL_PHONE', '+1 (555) 234-5678 / +1 (555) 234-5679');
define('HOSPITAL_EMAIL', 'info@apexmemorial.org');
define('HOSPITAL_WEBSITE', 'www.apexmemorialhospital.org');
define('HOSPITAL_TAX_NO', 'HMS-TX-998822');

// Currency & Formatting
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i');

// Security & Session Settings
define('SESSION_LIFETIME', 1800); // 30 minutes automatic session timeout as per SRS Section 4

// Available User Roles (as per SRS Section 2 & 3.1)
define('ROLES', [
    'admin'        => 'Administrator',
    'doctor'       => 'Doctor',
    'nurse'        => 'Nurse',
    'receptionist' => 'Receptionist',
    'lab'          => 'Laboratory Staff',
    'pharmacist'   => 'Pharmacist',
    'accountant'   => 'Accountant'
]);

// Initialize PHP Session securely with local session directory
if (session_status() === PHP_SESSION_NONE) {
    if (!file_exists(SESSIONS_DIR)) {
        @mkdir(SESSIONS_DIR, 0777, true);
    }
    @session_save_path(SESSIONS_DIR);
    if (!headers_sent()) {
        @ini_set('session.cookie_httponly', 1);
        @ini_set('session.use_only_cookies', 1);
    }
    @session_start();
}

// Session Timeout Checker (Section 4 Non-Functional Security Requirement)
function checkSessionTimeout() {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            // Log session timeout
            if (class_exists('AuditLog')) {
                AuditLog::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role_name'], 'Logout', 'Security', 'User session expired due to inactivity');
            }
            session_unset();
            session_destroy();
            header('Location: index.php?page=login&timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}

// CSRF Token generator and checker
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper to sanitize inputs
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
