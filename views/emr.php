<?php
$pageTitle = "Electronic Medical Records (EMR)";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$records = EMRController::getAll();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Electronic Medical Records (EMR)</h1>
                <p class="text-xs text-slate-500 mt-0.5">Clinical documentation, physical examination vitals, diagnoses, and e-prescriptions</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modal-create-emr')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="clipboard-plus" class="w-3.5 h-3.5"></i> New Clinical Encounter
                </button>
            </div>
        </div>

        <!-- Medical Records List -->
        <div class="space-y-4">
            <?php if (empty($records)): ?>
                <div class="card-clean p-12 text-center text-slate-400">
                    <i data-lucide="folder-x" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
                    <p class="text-sm">No electronic medical records found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($records as $r): ?>
                    <div class="card-clean p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b pb-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                                    <i data-lucide="activity" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <a href="index.php?page=patient_detail&id=<?= $r['patient_id'] ?>" class="text-base font-bold text-slate-900 hover:text-sky-600">
                                        <?= htmlspecialchars($r['patient_name']) ?>
                                    </a>
                                    <span class="text-xs text-slate-400 font-mono ml-1">(<?= $r['patient_code'] ?>)</span>
                                    <div class="text-xs text-slate-500">
                                        Attending: <strong class="text-slate-700"><?= htmlspecialchars($r['doctor_name']) ?></strong> (<?= htmlspecialchars($r['specialization']) ?>)
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400"><?= date('M d, Y H:i', strtotime($r['record_date'])) ?></span>
                                <a href="index.php?page=print_prescription&record_id=<?= $r['id'] ?>" target="_blank" class="px-2.5 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 rounded text-xs font-semibold text-slate-700 flex items-center gap-1">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-sky-600"></i> Print Rx
                                </a>
                            </div>
                        </div>

                        <!-- Diagnosis & Chief Complaint -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs mb-3">
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Chief Complaint</div>
                                <div class="text-slate-800 font-medium"><?= htmlspecialchars($r['chief_complaint']) ?></div>
                            </div>
                            <div class="p-3 bg-sky-50 rounded-lg border border-sky-100">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-sky-700 mb-1">Clinical Diagnosis</div>
                                <div class="text-sky-950 font-bold"><?= htmlspecialchars($r['diagnosis']) ?></div>
                            </div>
                        </div>

                        <!-- Vitals -->
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200 mb-3">
                            <div><span class="text-slate-400 text-[10px] block">BP</span> <strong><?= $r['vitals_bp'] ?: '-' ?></strong></div>
                            <div><span class="text-slate-400 text-[10px] block">Pulse</span> <strong><?= $r['vitals_heart_rate'] ? $r['vitals_heart_rate'] . ' bpm' : '-' ?></strong></div>
                            <div><span class="text-slate-400 text-[10px] block">Temperature</span> <strong><?= $r['vitals_temp'] ? $r['vitals_temp'] . ' °C' : '-' ?></strong></div>
                            <div><span class="text-slate-400 text-[10px] block">Oxygen (SpO2)</span> <strong><?= $r['vitals_spo2'] ? $r['vitals_spo2'] . ' %' : '-' ?></strong></div>
                            <div><span class="text-slate-400 text-[10px] block">Weight</span> <strong><?= $r['vitals_weight'] ? $r['vitals_weight'] . ' kg' : '-' ?></strong></div>
                        </div>

                        <?php if ($r['clinical_notes'] || $r['treatment_plan']): ?>
                            <div class="text-xs text-slate-600 space-y-1 mb-3">
                                <?php if ($r['clinical_notes']): ?>
                                    <div><strong class="text-slate-800">Clinical Notes:</strong> <?= htmlspecialchars($r['clinical_notes']) ?></div>
                                <?php endif; ?>
                                <?php if ($r['treatment_plan']): ?>
                                    <div><strong class="text-slate-800">Treatment Plan:</strong> <?= htmlspecialchars($r['treatment_plan']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Prescriptions Table -->
                        <?php if (!empty($r['prescriptions'])): ?>
                            <div class="border-t pt-3">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1">
                                    <i data-lucide="pill" class="w-3.5 h-3.5 text-emerald-600"></i> Prescribed Medications (<?= count($r['prescriptions']) ?>)
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs bg-white rounded border border-slate-200">
                                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b">
                                            <tr>
                                                <th class="py-1.5 px-3">Medicine Name</th>
                                                <th class="py-1.5 px-3">Dosage</th>
                                                <th class="py-1.5 px-3">Frequency</th>
                                                <th class="py-1.5 px-3">Duration</th>
                                                <th class="py-1.5 px-3">Instructions</th>
                                                <th class="py-1.5 px-3">Dispense Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php foreach ($r['prescriptions'] as $pr): ?>
                                                <tr>
                                                    <td class="py-1.5 px-3 font-semibold text-slate-900"><?= htmlspecialchars($pr['medicine_name']) ?></td>
                                                    <td class="py-1.5 px-3 text-slate-600"><?= htmlspecialchars($pr['dosage']) ?></td>
                                                    <td class="py-1.5 px-3 text-slate-600"><?= htmlspecialchars($pr['frequency']) ?></td>
                                                    <td class="py-1.5 px-3 text-slate-600"><?= htmlspecialchars($pr['duration']) ?></td>
                                                    <td class="py-1.5 px-3 text-slate-500"><?= htmlspecialchars($pr['instructions'] ?: 'Standard administration') ?></td>
                                                    <td class="py-1.5 px-3">
                                                        <span class="badge <?= $pr['is_dispensed'] ? 'badge-success' : 'badge-warning' ?>">
                                                            <?= $pr['is_dispensed'] ? 'Dispensed' : 'Pending Pharmacy' ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
