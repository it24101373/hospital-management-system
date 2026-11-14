<?php
$pageTitle = "Laboratory & Diagnostics";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$labTests = LabController::getAll($status, $search);
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clinical Laboratory Management</h1>
                <p class="text-xs text-slate-500 mt-0.5">Test orders, phlebotomy & specimen tracking, result entry, and diagnostic reporting</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modal-order-lab')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i> Order Laboratory Test
                </button>
            </div>
        </div>

        <!-- Filter Bar (SRS Section 7) -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="hidden" name="page" value="laboratory">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by test code, test name, or patient..." class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Sample Statuses</option>
                        <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending Specimen</option>
                        <option value="Sample Collected" <?= $status === 'Sample Collected' ? 'selected' : '' ?>>Sample Collected</option>
                        <option value="In Analysis" <?= $status === 'In Analysis' ? 'selected' : '' ?>>In Analysis</option>
                        <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || !empty($status)): ?>
                        <a href="index.php?page=laboratory" class="px-3 py-2 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tests Master Table -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Test Code</th>
                            <th class="py-3 px-4">Patient Details</th>
                            <th class="py-3 px-4">Investigation & Category</th>
                            <th class="py-3 px-4">Doctor</th>
                            <th class="py-3 px-4">Sample Status</th>
                            <th class="py-3 px-4">Findings & Ranges</th>
                            <th class="py-3 px-4 text-right">Laboratory Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($labTests)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400">
                                    <i data-lucide="flask-round" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    No laboratory tests found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($labTests as $lt): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700 whitespace-nowrap">
                                        <?= $lt['test_code'] ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="index.php?page=patient_detail&id=<?= $lt['patient_id'] ?>" class="font-bold text-slate-900 hover:text-sky-600">
                                            <?= htmlspecialchars($lt['patient_name']) ?>
                                        </a>
                                        <div class="text-[11px] text-slate-400"><?= $lt['patient_code'] ?> | <?= htmlspecialchars($lt['gender']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($lt['test_name']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= htmlspecialchars($lt['category']) ?> ($<?= number_format($lt['cost'], 2) ?>)</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?= htmlspecialchars($lt['doctor_name'] ?: 'Attending Staff') ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $stClass = match($lt['sample_status']) {
                                            'Completed'        => 'badge-success',
                                            'Sample Collected' => 'badge-info',
                                            'In Analysis'      => 'badge-purple',
                                            default            => 'badge-warning'
                                        };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= $lt['sample_status'] ?></span>
                                    </td>
                                    <td class="py-3 px-4 max-w-sm">
                                        <?php if ($lt['results']): ?>
                                            <div class="font-medium <?= $lt['is_abnormal'] ? 'text-rose-700 font-bold' : 'text-slate-800' ?>">
                                                <?= $lt['is_abnormal'] ? '⚠️ ' : '' ?><?= htmlspecialchars($lt['results']) ?>
                                            </div>
                                            <?php if ($lt['reference_range']): ?>
                                                <div class="text-[10px] text-slate-400">Ref: <?= htmlspecialchars($lt['reference_range']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">No results entered</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($lt['sample_status'] === 'Pending'): ?>
                                                <a href="index.php?action=collect_lab_sample&id=<?= $lt['id'] ?>" class="px-2.5 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded text-xs font-semibold flex items-center gap-1">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Collect Sample
                                                </a>
                                            <?php elseif ($lt['sample_status'] === 'Sample Collected' || $lt['sample_status'] === 'In Analysis'): ?>
                                                <button onclick="promptEnterResult(<?= $lt['id'] ?>, '<?= htmlspecialchars(addslashes($lt['test_name'])) ?>', '<?= htmlspecialchars(addslashes($lt['reference_range'] ?? '')) ?>')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold flex items-center gap-1 shadow-sm">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Enter Results
                                                </button>
                                            <?php elseif ($lt['sample_status'] === 'Completed'): ?>
                                                <a href="index.php?page=print_lab_report&id=<?= $lt['id'] ?>" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded text-xs font-semibold text-slate-700 flex items-center gap-1">
                                                    <i data-lucide="printer" class="w-3.5 h-3.5 text-sky-600"></i> Print Report
                                                </a>
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

<!-- Modal: Enter Lab Test Results (SRS Section 3.6 Result Entry) -->
<div id="modal-enter-result" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="file-check" class="w-5 h-5"></i> Enter Diagnostic Results
            </h3>
            <button onclick="closeModal('modal-enter-result')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=enter_lab_result" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" id="result-test-id" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Test Title</label>
                <input type="text" id="result-test-name" disabled class="w-full px-3 py-2 bg-slate-100 border rounded-lg text-sm text-slate-700">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Investigation Findings & Values *</label>
                <textarea name="results" required rows="3" placeholder="e.g. Fasting Glucose: 118 mg/dL (Elevated), HbA1c: 6.2%" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reference Range</label>
                <input type="text" name="reference_range" id="result-ref-range" placeholder="e.g. Fasting: 70-99 mg/dL" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="flex items-center gap-2 p-3 bg-amber-50 rounded-lg border border-amber-200">
                <input type="checkbox" name="is_abnormal" id="is_abnormal" value="1" class="w-4 h-4 text-rose-600 rounded">
                <label for="is_abnormal" class="text-xs font-semibold text-amber-900 cursor-pointer">
                    Flag as Critical / Abnormal Result (Highlights in red alert for clinicians)
                </label>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Technician Remarks & Instrument Notes</label>
                <input type="text" name="technician_notes" placeholder="Analyzed on Roche Cobas c311. Calibrated OK." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-enter-result')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save & Complete Test</button>
            </div>
        </form>
    </div>
</div>

<script>
function promptEnterResult(id, testName, refRange) {
    document.getElementById('result-test-id').value = id;
    document.getElementById('result-test-name').value = testName;
    document.getElementById('result-ref-range').value = refRange;
    openModal('modal-enter-result');
}
</script>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
