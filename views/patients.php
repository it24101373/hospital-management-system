<?php
$pageTitle = "Patient Directory & Records";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$patients = PatientController::getAll($search, $type);
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Action & Search Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Patient Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Comprehensive patient registration, medical history, documents, and records</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php?action=export_csv&type=patients" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i> Export CSV
                </a>
                <button onclick="openModal('modal-add-patient')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Register New Patient
                </button>
            </div>
        </div>

        <!-- Search and Filters Box -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="page" value="patients">
                <div class="sm:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by patient ID, full name, phone number, or email..." class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Patient Types</option>
                        <option value="Outpatient" <?= $type === 'Outpatient' ? 'selected' : '' ?>>Outpatient (OPD)</option>
                        <option value="Inpatient" <?= $type === 'Inpatient' ? 'selected' : '' ?>>Inpatient (IPD)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || !empty($type)): ?>
                        <a href="index.php?page=patients" class="px-3 py-2 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Patients Master Table -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="patient-table">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Patient Code</th>
                            <th class="py-3 px-4">Full Name</th>
                            <th class="py-3 px-4">DOB / Age</th>
                            <th class="py-3 px-4">Gender</th>
                            <th class="py-3 px-4">Blood</th>
                            <th class="py-3 px-4">Contact Phone</th>
                            <th class="py-3 px-4">Allergies</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-8 text-slate-400">
                                    <i data-lucide="user-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    No patient records found matching your search.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $p): 
                                $dobYear = date('Y', strtotime($p['dob']));
                                $age = date('Y') - $dobYear;
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700 whitespace-nowrap">
                                        <?= htmlspecialchars($p['patient_code']) ?>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        <a href="index.php?page=patient_detail&id=<?= $p['id'] ?>" class="hover:text-sky-600">
                                            <?= htmlspecialchars($p['full_name']) ?>
                                        </a>
                                        <?php if ($p['email']): ?>
                                            <div class="text-[10px] text-slate-400 font-normal"><?= htmlspecialchars($p['email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                                        <?= $p['dob'] ?> <span class="text-slate-400">(<?= $age ?> yrs)</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700"><?= $p['gender'] ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            <?= htmlspecialchars($p['blood_group'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 whitespace-nowrap"><?= htmlspecialchars($p['phone']) ?></td>
                                    <td class="py-3 px-4 max-w-xs truncate">
                                        <?php if (!empty($p['allergies']) && $p['allergies'] !== 'None' && $p['allergies'] !== 'None known'): ?>
                                            <span class="text-rose-600 font-semibold" title="<?= htmlspecialchars($p['allergies']) ?>">
                                                ⚠️ <?= htmlspecialchars($p['allergies']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge <?= $p['patient_type'] === 'Inpatient' ? 'badge-purple' : 'badge-info' ?>">
                                            <?= $p['patient_type'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge badge-success"><?= $p['status'] ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="index.php?page=patient_detail&id=<?= $p['id'] ?>" class="px-2.5 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded text-xs font-semibold flex items-center gap-1" title="View Full Medical History & Documents">
                                                <i data-lucide="folder-clock" class="w-3.5 h-3.5"></i> Records
                                            </a>
                                            <button onclick="openModal('modal-book-appointment')" class="p-1 text-slate-400 hover:text-sky-600" title="Book Appointment">
                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                            </button>
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
