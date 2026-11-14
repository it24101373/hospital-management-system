<?php
/**
 * Hospital Management System (HMS)
 * System, Backup & Recovery Controller (SRS Section 4 & 10)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/AuditLog.php';

class SystemController {
    public static function createBackup(string $type = 'Manual'): ?array {
        if (!file_exists(DB_FILE)) {
            return null;
        }

        if (!is_dir(BACKUPS_DIR)) {
            @mkdir(BACKUPS_DIR, 0777, true);
        }

        $timestamp = date('Y-m-d_His');
        $backupName = 'hms_backup_' . strtolower($type) . '_' . $timestamp . '.sqlite';
        $destination = BACKUPS_DIR . '/' . $backupName;

        if (copy(DB_FILE, $destination)) {
            $fileSize = filesize($destination);
            $backupId = Database::insert('system_backups', [
                'backup_name' => $backupName,
                'file_path'   => $destination,
                'file_size'   => $fileSize,
                'backup_type' => $type,
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            $user = Auth::user();
            AuditLog::log($user['id'] ?? null, $user['username'] ?? 'System', $user['role_name'] ?? 'Admin', 'Create Backup', 'System Backup', "Created {$type} backup: {$backupName} ({$fileSize} bytes)", $backupId);

            return [
                'id'        => $backupId,
                'name'      => $backupName,
                'file_path' => $destination,
                'size'      => $fileSize,
                'type'      => $type,
                'date'      => date('Y-m-d H:i:s')
            ];
        }
        return null;
    }

    public static function listBackups(): array {
        return Database::fetchAll("SELECT * FROM system_backups ORDER BY created_at DESC");
    }

    public static function restoreBackup(string $fileName): bool {
        // Sanitize file name to prevent path traversal
        $safeName = basename($fileName);
        $sourcePath = BACKUPS_DIR . '/' . $safeName;

        if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
            return false;
        }

        // Create safety pre-restore backup first
        self::createBackup('Manual_Pre_Restore');

        // Copy backup over active database
        if (copy($sourcePath, DB_FILE)) {
            $user = Auth::user();
            AuditLog::log($user['id'] ?? null, $user['username'] ?? 'Admin', $user['role_name'] ?? 'Admin', 'Restore Backup', 'System Recovery', "Restored database from snapshot {$safeName}");
            return true;
        }
        return false;
    }

    public static function downloadBackup(string $fileName): void {
        $safeName = basename($fileName);
        $path = BACKUPS_DIR . '/' . $safeName;
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $safeName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
    }
}
