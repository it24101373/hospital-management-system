<?php
/**
 * Hospital Management System (HMS)
 * Authentication & Access Control (RBAC)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once APP_ROOT . '/models/AuditLog.php';

class Auth {
    public static function check(): bool {
        checkSessionTimeout();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return [
            'id'        => $_SESSION['user_id'],
            'username'  => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role_name' => $_SESSION['role_name'],
            'role_label'=> $_SESSION['role_label'],
            'email'     => $_SESSION['email'] ?? ''
        ];
    }

    public static function role(): ?string {
        return $_SESSION['role_name'] ?? null;
    }

    public static function hasRole($roles): bool {
        if (!self::check()) {
            return false;
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        $userRole = self::role();
        // Administrator always has access to everything
        if ($userRole === 'admin') {
            return true;
        }
        return in_array($userRole, $roles, true);
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            setFlash('error', 'Please login to access the system.');
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function requireRole($roles): void {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            setFlash('error', 'Access Denied: You do not have permission to view this section.');
            header('Location: index.php?page=dashboard');
            exit;
        }
    }

    public static function login(string $username, string $password): bool {
        $sql = "SELECT u.*, r.name as role_name, r.label as role_label 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.username = :username AND u.status = 'active'";
        $user = Database::fetchOne($sql, ['username' => $username]);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['role_label'] = $user['role_label'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();

            AuditLog::log($user['id'], $user['username'], $user['role_name'], 'Login', 'Security', 'User successfully authenticated');
            return true;
        }

        AuditLog::log(null, $username, 'Unknown', 'Failed Login', 'Security', 'Failed login attempt with username: ' . $username);
        return false;
    }

    public static function logout(): void {
        if (isset($_SESSION['user_id'])) {
            AuditLog::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role_name'], 'Logout', 'Security', 'User signed out');
        }
        $_SESSION = [];
        if (session_id() != '' || headers_sent()) {
            session_destroy();
        }
    }

    public static function quickSwitchRole(string $targetRole): bool {
        // Find first active user with target role
        $sql = "SELECT u.*, r.name as role_name, r.label as role_label 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE r.name = :role AND u.status = 'active' LIMIT 1";
        $user = Database::fetchOne($sql, ['role' => $targetRole]);
        if ($user) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['role_label'] = $user['role_label'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();

            AuditLog::log($user['id'], $user['username'], $user['role_name'], 'Role Switch', 'Security', 'Switched session to role: ' . $targetRole);
            return true;
        }
        return false;
    }

    public static function changePassword(int $userId, string $oldPassword, string $newPassword): bool {
        $user = Database::fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            return false;
        }
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::update('users', ['password_hash' => $newHash], 'id = :id', ['id' => $userId]);
        AuditLog::log($userId, $user['username'], $_SESSION['role_name'] ?? 'User', 'Password Change', 'Security', 'Password updated successfully');
        return true;
    }
}
