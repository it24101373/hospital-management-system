<?php
$pageTitle = "Staff & Human Resources";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$tab = $_GET['tab'] ?? 'employees';
$employees = StaffController::getEmployees();
$attendance = StaffController::getAttendance();
$leaves = StaffController::getLeaves();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Hospital Staff & Workforce Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Personnel registry, departmental assignments, daily attendance logs, and staff leaves</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modal-add-employee')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Register Employee
                </button>
                <button onclick="openModal('modal-apply-leave')" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-600"></i> Request Leave
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex items-center gap-2 mb-6 border-b pb-3">
            <a href="index.php?page=staff&tab=employees" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $tab === 'employees' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                <i data-lucide="users" class="w-4 h-4"></i> Employees Directory (<?= count($employees) ?>)
            </a>
            <a href="index.php?page=staff&tab=attendance" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $tab === 'attendance' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                <i data-lucide="check-square" class="w-4 h-4"></i> Today's Attendance (<?= date('M d') ?>)
            </a>
            <a href="index.php?page=staff&tab=leaves" class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 <?= $tab === 'leaves' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                <i data-lucide="calendar-off" class="w-4 h-4"></i> Leave Records (<?= count($leaves) ?>)
            </a>
        </div>

        <?php if ($tab === 'employees'): ?>
            <!-- Employees Table -->
            <div class="card-clean overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Code</th>
                                <th class="py-3 px-4">Employee Full Name</th>
                                <th class="py-3 px-4">Designation & Role</th>
                                <th class="py-3 px-4">Department</th>
                                <th class="py-3 px-4">Contact Phone</th>
                                <th class="py-3 px-4">Joining Date</th>
                                <th class="py-3 px-4">Monthly Salary</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($employees as $emp): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700 whitespace-nowrap"><?= $emp['employee_code'] ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($emp['full_name']) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= htmlspecialchars($emp['email'] ?: 'No email') ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($emp['designation']) ?></div>
                                        <div class="text-[11px] text-sky-600 uppercase font-bold"><?= htmlspecialchars($emp['role']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($emp['department_name'] ?: 'Administration') ?></td>
                                    <td class="py-3 px-4 text-slate-700 whitespace-nowrap"><?= htmlspecialchars($emp['phone']) ?></td>
                                    <td class="py-3 px-4 text-slate-600"><?= $emp['join_date'] ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900">$<?= number_format($emp['salary'], 2) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="badge badge-success"><?= $emp['status'] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab === 'attendance'): ?>
            <!-- Attendance Table -->
            <div class="card-clean overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Staff Code</th>
                                <th class="py-3 px-4">Employee</th>
                                <th class="py-3 px-4">Designation</th>
                                <th class="py-3 px-4">Check-In Time</th>
                                <th class="py-3 px-4">Check-Out Time</th>
                                <th class="py-3 px-4">Attendance Status</th>
                                <th class="py-3 px-4 text-right">Update Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($attendance as $att): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700"><?= $att['employee_code'] ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($att['full_name']) ?></td>
                                    <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($att['designation']) ?></td>
                                    <td class="py-3 px-4 font-mono text-slate-700"><?= $att['check_in_time'] ?: '-' ?></td>
                                    <td class="py-3 px-4 font-mono text-slate-700"><?= $att['check_out_time'] ?: '-' ?></td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $attClass = match($att['status']) {
                                            'Present' => 'badge-success',
                                            'Absent'  => 'badge-danger',
                                            'Half-Day'=> 'badge-warning',
                                            default   => 'badge-purple'
                                        };
                                        ?>
                                        <span class="badge <?= $attClass ?>"><?= $att['status'] ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="index.php?action=mark_attendance&emp_id=<?= $att['employee_id'] ?>&status=Present" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100">Present</a>
                                            <a href="index.php?action=mark_attendance&emp_id=<?= $att['employee_id'] ?>&status=Absent" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100">Absent</a>
                                            <a href="index.php?action=mark_attendance&emp_id=<?= $att['employee_id'] ?>&status=On-Leave" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100">Leave</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab === 'leaves'): ?>
            <!-- Leaves Table -->
            <div class="card-clean overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Staff Member</th>
                                <th class="py-3 px-4">Leave Type</th>
                                <th class="py-3 px-4">Date Range</th>
                                <th class="py-3 px-4">Days</th>
                                <th class="py-3 px-4">Reason</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Review Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($leaves)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-slate-400">No leave requests recorded.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leaves as $l): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900"><?= htmlspecialchars($l['full_name']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= $l['employee_code'] ?> | <?= htmlspecialchars($l['designation']) ?></div>
                                        </td>
                                        <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($l['leave_type']) ?></td>
                                        <td class="py-3 px-4 whitespace-nowrap text-slate-600"><?= $l['start_date'] ?> to <?= $l['end_date'] ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-900"><?= $l['days_count'] ?> day(s)</td>
                                        <td class="py-3 px-4 max-w-xs truncate text-slate-600"><?= htmlspecialchars($l['reason']) ?></td>
                                        <td class="py-3 px-4">
                                            <?php 
                                            $lClass = match($l['status']) {
                                                'Approved' => 'badge-success',
                                                'Rejected' => 'badge-danger',
                                                default    => 'badge-warning'
                                            };
                                            ?>
                                            <span class="badge <?= $lClass ?>"><?= $l['status'] ?></span>
                                            <?php if ($l['approved_by']): ?>
                                                <div class="text-[10px] text-slate-400 mt-0.5">by <?= htmlspecialchars($l['approved_by']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <?php if ($l['status'] === 'Pending'): ?>
                                                <div class="flex items-center justify-end gap-1">
                                                    <a href="index.php?action=update_leave_status&id=<?= $l['id'] ?>&status=Approved" class="px-2 py-1 bg-emerald-600 text-white hover:bg-emerald-700 rounded text-[11px] font-semibold">Approve</a>
                                                    <a href="index.php?action=update_leave_status&id=<?= $l['id'] ?>&status=Rejected" class="px-2 py-1 bg-rose-600 text-white hover:bg-rose-700 rounded text-[11px] font-semibold">Reject</a>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-[11px] font-medium">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Modal: Apply Leave (SRS Section 3.9) -->
<div id="modal-apply-leave" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5"></i> Submit Leave Request
            </h3>
            <button onclick="closeModal('modal-apply-leave')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=apply_leave" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Employee *</label>
                <select name="employee_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['full_name']) ?> (<?= $e['employee_code'] ?> - <?= $e['designation'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Leave Type *</label>
                <select name="leave_type" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Annual Leave">Annual Leave</option>
                    <option value="Casual Leave">Casual Leave</option>
                    <option value="Emergency Leave">Emergency Leave</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Start Date *</label>
                    <input type="date" name="start_date" required value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">End Date *</label>
                    <input type="date" name="end_date" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reason for Leave *</label>
                <input type="text" name="reason" required placeholder="Medical appointment, family event..." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-apply-leave')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
