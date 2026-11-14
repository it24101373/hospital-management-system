<?php
$pageTitle = "Security Audit Trail";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$module = $_GET['module'] ?? '';
$action = $_GET['action_filter'] ?? '';
$logs = AuditLog::getRecent(200, $module ?: null, $action ?: null);
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Security Audit Trail & Compliance Log</h1>
                <p class="text-xs text-slate-500 mt-0.5">Immutable tracking of user authentication, patient records access, clinical modifications, and billing transactions</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-purple flex items-center gap-1">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Tamper-Evident System Audit
                </span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="hidden" name="page" value="audit_logs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Filter by Module</label>
                    <select name="module" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Modules</option>
                        <option value="Security" <?= $module === 'Security' ? 'selected' : '' ?>>Security & Auth</option>
                        <option value="Patients" <?= $module === 'Patients' ? 'selected' : '' ?>>Patient Management</option>
                        <option value="Appointments" <?= $module === 'Appointments' ? 'selected' : '' ?>>Appointments</option>
                        <option value="EMR" <?= $module === 'EMR' ? 'selected' : '' ?>>EMR & Prescriptions</option>
                        <option value="Laboratory" <?= $module === 'Laboratory' ? 'selected' : '' ?>>Laboratory</option>
                        <option value="Pharmacy" <?= $module === 'Pharmacy' ? 'selected' : '' ?>>Pharmacy</option>
                        <option value="Billing" <?= $module === 'Billing' ? 'selected' : '' ?>>Billing & Payments</option>
                        <option value="Admissions" <?= $module === 'Admissions' ? 'selected' : '' ?>>Admissions</option>
                        <option value="Staff" <?= $module === 'Staff' ? 'selected' : '' ?>>Staff & Attendance</option>
                        <option value="System Backup" <?= $module === 'System Backup' ? 'selected' : '' ?>>System Backup</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Filter by Action</label>
                    <input type="text" name="action_filter" value="<?= htmlspecialchars($action) ?>" placeholder="e.g. Login, Register, Record Payment..." class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter Logs</button>
                    <?php if (!empty($module) || !empty($action)): ?>
                        <a href="index.php?page=audit_logs" class="px-3 py-1.5 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Audit Trail Table -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Timestamp</th>
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Module</th>
                            <th class="py-3 px-4">Action</th>
                            <th class="py-3 px-4">Audit Details</th>
                            <th class="py-3 px-4 font-mono">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400">No audit log records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $lg): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-2.5 px-4 font-mono text-slate-500 whitespace-nowrap">
                                        <?= date('Y-m-d H:i:s', strtotime($lg['timestamp'])) ?>
                                    </td>
                                    <td class="py-2.5 px-4 font-bold text-slate-900">
                                        <?= htmlspecialchars($lg['username']) ?>
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <span class="badge badge-purple"><?= htmlspecialchars($lg['user_role']) ?></span>
                                    </td>
                                    <td class="py-2.5 px-4 font-semibold text-sky-700">
                                        <?= htmlspecialchars($lg['module']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 font-bold text-slate-800">
                                        <?= htmlspecialchars($lg['action']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 text-slate-600 max-w-md">
                                        <?= htmlspecialchars($lg['details']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 font-mono text-slate-400 text-[11px] whitespace-nowrap">
                                        <?= htmlspecialchars($lg['ip_address']) ?>
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
