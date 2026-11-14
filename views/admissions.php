<?php
$pageTitle = "Inpatient & Bed Management (IPD)";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$status = $_GET['status'] ?? 'Admitted';
$admissions = AdmissionController::getAll($status);
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Inpatient Admissions & Ward Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Hospital bed occupancy, ward assignments, admission tracking, and discharge procedures</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modal-admit-patient')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="bed-single" class="w-3.5 h-3.5"></i> Admit Inpatient
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 mb-6">
            <a href="index.php?page=admissions&status=Admitted" class="px-4 py-2 rounded-lg text-xs font-bold transition <?= $status === 'Admitted' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                Currently Admitted
            </a>
            <a href="index.php?page=admissions&status=Discharged" class="px-4 py-2 rounded-lg text-xs font-bold transition <?= $status === 'Discharged' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                Discharge History
            </a>
            <a href="index.php?page=admissions&status=" class="px-4 py-2 rounded-lg text-xs font-bold transition <?= empty($status) ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                All Admissions
            </a>
        </div>

        <!-- Admissions Table -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Admission Code</th>
                            <th class="py-3 px-4">Patient Information</th>
                            <th class="py-3 px-4">Ward & Bed Number</th>
                            <th class="py-3 px-4">Attending Doctor</th>
                            <th class="py-3 px-4">Admission Date</th>
                            <th class="py-3 px-4">Diagnosis & Notes</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($admissions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">No admission records found for selected filter.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admissions as $adm): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-purple-700 whitespace-nowrap">
                                        <?= $adm['admission_code'] ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="index.php?page=patient_detail&id=<?= $adm['patient_id'] ?>" class="font-bold text-slate-900 hover:text-sky-600">
                                            <?= htmlspecialchars($adm['patient_name']) ?>
                                        </a>
                                        <div class="text-[11px] text-slate-400"><?= $adm['patient_code'] ?> | <?= htmlspecialchars($adm['patient_phone']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($adm['ward_name']) ?></div>
                                        <div class="text-[11px] text-purple-600 font-semibold">Room <?= htmlspecialchars($adm['room_number']) ?>, <?= htmlspecialchars($adm['bed_number']) ?></div>
                                        <div class="text-[10px] text-slate-400">$<?= number_format($adm['daily_bed_charge'], 2) ?>/day</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        <?= htmlspecialchars($adm['doctor_name']) ?>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800"><?= date('M d, Y', strtotime($adm['admission_date'])) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= date('H:i', strtotime($adm['admission_date'])) ?></div>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate text-slate-600">
                                        <?= htmlspecialchars($adm['diagnosis_summary'] ?: 'Inpatient medical observation') ?>
                                        <?php if ($adm['discharge_notes']): ?>
                                            <div class="text-[10px] text-slate-400 italic">Discharge: <?= htmlspecialchars($adm['discharge_notes']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge <?= $adm['status'] === 'Admitted' ? 'badge-success' : 'badge-gray' ?>">
                                            <?= $adm['status'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($adm['status'] === 'Admitted'): ?>
                                                <button onclick="promptDischarge(<?= $adm['id'] ?>, '<?= htmlspecialchars(addslashes($adm['patient_name'])) ?>')" class="px-2.5 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded text-xs font-semibold flex items-center gap-1">
                                                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Discharge
                                                </button>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-[11px]">Discharged <?= date('M d', strtotime($adm['discharge_date'])) ?></span>
                                            <?php endif; ?>
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

<!-- Modal: Discharge Inpatient (SRS Section 1.2) -->
<div id="modal-discharge" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="log-out" class="w-5 h-5"></i> Process Inpatient Discharge
            </h3>
            <button onclick="closeModal('modal-discharge')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=discharge_patient" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" id="discharge-adm-id" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Patient</label>
                <input type="text" id="discharge-patient-name" disabled class="w-full px-3 py-2 bg-slate-100 border rounded-lg text-sm text-slate-700">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Discharge Summary & Clinical Orders *</label>
                <textarea name="discharge_notes" required rows="3" placeholder="Condition at discharge: Stable. Follow-up instructions, post-discharge medications..." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none"></textarea>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-discharge')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold shadow-sm">Confirm Discharge</button>
            </div>
        </form>
    </div>
</div>

<script>
function promptDischarge(id, patientName) {
    document.getElementById('discharge-adm-id').value = id;
    document.getElementById('discharge-patient-name').value = patientName;
    openModal('modal-discharge');
}
</script>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
