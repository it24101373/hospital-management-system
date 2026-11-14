<?php
/**
 * Hospital Management System (HMS)
 * Audit Log Model
 */

require_once __DIR__ . '/../config/database.php';

class AuditLog {
    public static function log(
        ?int $userId,
        string $username,
        string $role,
        string $action,
        string $module,
        string $details = '',
        ?int $recordId = null
    ): void {
        try {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            Database::insert('audit_logs', [
                'user_id'    => $userId,
                'username'   => $username ?: 'System',
                'user_role'  => $role ?: 'System',
                'action'     => $action,
                'module'     => $module,
                'record_id'  => $recordId,
                'ip_address' => $ipAddress,
                'details'    => $details,
                'timestamp'  => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Failed to write audit log: " . $e->getMessage());
        }
    }

    public static function getRecent(int $limit = 100, ?string $module = null, ?string $action = null): array {
        $sql = "SELECT * FROM audit_logs WHERE 1=1";
        $params = [];
        if ($module) {
            $sql .= " AND module = :module";
            $params['module'] = $module;
        }
        if ($action) {
            $sql .= " AND action = :action";
            $params['action'] = $action;
        }
        $sql .= " ORDER BY timestamp DESC LIMIT :limit";
        
        $stmt = Database::getConnection()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
