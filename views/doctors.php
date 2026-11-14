<?php
$pageTitle = "Medical Staff & Doctors";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$search = $_GET['search'] ?? '';
$deptId = !empty($_GET['department_id']) ? (int)$_GET['department_id'] : null;

$doctors = DoctorController::getAll($search, $deptId);
$departments = DoctorController::getDepartments();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Doctor & Specialist Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Physicians directory, department assignments, consulting fees, and roster schedules</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modal-add-doctor')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Doctor Profile
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="hidden" name="page" value="doctors">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search doctor by name or specialization..." class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <select name="department_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>" <?= $deptId === (int)$dept['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || $deptId): ?>
                        <a href="index.php?page=doctors" class="px-3 py-2 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Doctors Directory Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($doctors as $d): ?>
                <div class="card-clean p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-black text-lg border border-sky-300">
                                <?= strtoupper(substr(str_replace('Dr. ', '', $d['full_name']), 0, 1)) ?>
                            </div>
                            <span class="badge badge-success"><?= $d['status'] ?></span>
                        </div>
                        <h3 class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($d['full_name']) ?></h3>
                        <div class="text-xs font-semibold text-sky-600"><?= htmlspecialchars($d['specialization']) ?></div>
                        <div class="text-[11px] text-slate-500 mt-1"><?= htmlspecialchars($d['qualification'] ?: 'Senior Consultant') ?></div>

                        <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Department:</span>
                                <span class="font-semibold text-slate-700"><?= htmlspecialchars($d['department_name']) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Clinic / Room:</span>
                                <span class="font-semibold text-slate-700"><?= htmlspecialchars($d['room_number'] ?: 'Main OPD') ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Consultation Fee:</span>
                                <span class="font-bold text-emerald-600">$<?= number_format($d['consultation_fee'], 2) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Roster Days:</span>
                                <span class="font-semibold text-slate-700"><?= htmlspecialchars($d['schedule_days']) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Hours:</span>
                                <span class="font-mono text-slate-700"><?= htmlspecialchars($d['schedule_hours']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t flex items-center justify-between gap-2">
                        <span class="text-[10px] font-mono text-slate-400 font-bold"><?= $d['doctor_code'] ?></span>
                        <button onclick="openModal('modal-book-appointment')" class="px-3 py-1.5 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded text-xs font-semibold flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Book Appointment
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
