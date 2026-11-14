<?php
$pageTitle = "Backup & Disaster Recovery";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$backups = SystemController::listBackups();
$dbSize = file_exists(DB_FILE) ? filesize(DB_FILE) : 0;
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Database Backup & Disaster Recovery</h1>
                <p class="text-xs text-slate-500 mt-0.5">Automated snapshots, point-in-time recovery, database redundancy, and system archives (SRS Section 10)</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php?action=create_backup" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="database-backup" class="w-4 h-4"></i> Create Instant Full Backup
                </a>
            </div>
        </div>

        <!-- Strategy Cards (SRS Section 10 Specifications) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <div class="card-clean p-5 border-l-4 border-l-emerald-500">
                <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs uppercase mb-1">
                    <i data-lucide="clock" class="w-4 h-4"></i> Automatic Daily Backup
                </div>
                <div class="text-slate-900 font-black text-xl mt-1">Active (Daily 02:00 UTC)</div>
                <p class="text-xs text-slate-500 mt-1">Automatic nightly database snapshot with cryptographic checksum verification and zero-downtime capture.</p>
            </div>

            <div class="card-clean p-5 border-l-4 border-l-sky-500">
                <div class="flex items-center gap-2 text-sky-800 font-bold text-xs uppercase mb-1">
                    <i data-lucide="archive" class="w-4 h-4"></i> Weekly Full Backup
                </div>
                <div class="text-slate-900 font-black text-xl mt-1">Every Sunday 00:00</div>
                <p class="text-xs text-slate-500 mt-1">Complete compressed archive of database relations, documents repository, and audit trails.</p>
            </div>

            <div class="card-clean p-5 border-l-4 border-l-purple-500">
                <div class="flex items-center gap-2 text-purple-800 font-bold text-xs uppercase mb-1">
                    <i data-lucide="shield-check" class="w-4 h-4"></i> Disaster Recovery Plan
                </div>
                <div class="text-slate-900 font-black text-xl mt-1">RPO &lt; 1 hr | RTO &lt; 5 min</div>
                <p class="text-xs text-slate-500 mt-1">Pre-restore automatic rollback snapshot ensures zero data corruption risk during restoration.</p>
            </div>
        </div>

        <!-- Current Database Statistics -->
        <div class="card-clean p-5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-sky-50/50 border-sky-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Active SQLite Primary Storage</h3>
                    <p class="text-xs text-slate-500">Path: <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200"><?= DB_FILE ?></code></p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] font-bold uppercase">Current Database Size</span>
                    <strong class="text-slate-800 font-mono text-sm"><?= number_format($dbSize / 1024, 1) ?> KB</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] font-bold uppercase">Stored Backups</span>
                    <strong class="text-slate-800 font-mono text-sm"><?= count($backups) ?> snapshots</strong>
                </div>
            </div>
        </div>

        <!-- Backups List Table -->
        <div class="card-clean overflow-hidden">
            <div class="p-4 bg-slate-100 border-b border-slate-200 font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center justify-between">
                <span>Backup Archive Snapshots</span>
                <span class="text-slate-400 font-normal">Location: /backups/</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase">
                        <tr>
                            <th class="py-3 px-4">Backup Snapshot File</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Created Timestamp</th>
                            <th class="py-3 px-4">File Size</th>
                            <th class="py-3 px-4 text-right">Disaster Recovery Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($backups)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-8 text-slate-400">
                                    <i data-lucide="database" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    No backup archives created yet. Click "Create Instant Full Backup" to generate one now.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($backups as $b): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                        <?= htmlspecialchars($b['backup_name']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge <?= $b['backup_type'] === 'Daily' ? 'badge-info' : 'badge-purple' ?>">
                                            <?= $b['backup_type'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?= date('M d, Y H:i:s', strtotime($b['created_at'])) ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-700">
                                        <?= number_format($b['file_size'] / 1024, 1) ?> KB
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="index.php?action=download_backup&file=<?= urlencode($b['backup_name']) ?>" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded text-xs font-semibold text-slate-700 flex items-center gap-1" title="Download SQLite Snapshot">
                                                <i data-lucide="download" class="w-3.5 h-3.5 text-sky-600"></i> Download
                                            </a>
                                            <a href="index.php?action=restore_backup&file=<?= urlencode($b['backup_name']) ?>" onclick="return confirm('WARNING: Are you sure you want to restore database snapshot <?= htmlspecialchars(addslashes($b['backup_name'])) ?>? A safety pre-restore backup will be created automatically.')" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded text-xs font-semibold text-amber-900 flex items-center gap-1" title="Point-in-Time Restore">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-amber-700"></i> Restore
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
