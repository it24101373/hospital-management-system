<?php
$pageTitle = "Reports & Hospital Analytics";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$patRep = ReportController::getPatientReports();
$aptRep = ReportController::getAppointmentReports();
$revRep = ReportController::getRevenueReports();
$pharmRep = ReportController::getPharmacyReports();
$labRep = ReportController::getLaboratoryReports();
$staffRep = ReportController::getStaffReports();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Reports & Clinical Intelligence</h1>
                <p class="text-xs text-slate-500 mt-0.5">Statistical analysis, performance metrics, and compliance exports (SRS Section 3.10)</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="index.php?action=export_csv&type=patients" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-sky-600"></i> Patients CSV
                </a>
                <a href="index.php?action=export_csv&type=revenue" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-600"></i> Revenue CSV
                </a>
                <a href="index.php?action=export_csv&type=pharmacy" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-amber-600"></i> Inventory CSV
                </a>
            </div>
        </div>

        <!-- 6 Report Categories as per SRS Section 3.10 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- 1. Patient Reports -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-4 h-4 text-sky-600"></i> Patient Demographics
                    </h3>
                    <span class="badge badge-info"><?= $patRep['total'] ?> Total</span>
                </div>
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold block mb-1">Gender Distribution:</span>
                        <div class="flex gap-2">
                            <?php foreach ($patRep['by_gender'] as $g): ?>
                                <span class="px-2 py-1 bg-slate-100 rounded text-slate-700 font-medium">
                                    <?= $g['gender'] ?>: <strong><?= $g['count'] ?></strong>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block mb-1">Patient Classification:</span>
                        <div class="flex gap-2">
                            <?php foreach ($patRep['by_type'] as $t): ?>
                                <span class="px-2 py-1 <?= $t['patient_type'] === 'Inpatient' ? 'bg-purple-100 text-purple-800' : 'bg-sky-100 text-sky-800' ?> rounded font-medium">
                                    <?= $t['patient_type'] ?>: <strong><?= $t['count'] ?></strong>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Revenue Reports -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-600"></i> Financial Performance
                    </h3>
                    <span class="badge badge-success">Income Report</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Gross Invoiced:</span>
                        <span class="font-bold text-slate-900">$<?= number_format($revRep['total_billed'], 2) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Total Collected:</span>
                        <span class="font-bold text-emerald-600">$<?= number_format($revRep['total_paid'], 2) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Outstanding Balance:</span>
                        <span class="font-bold text-rose-600">$<?= number_format($revRep['total_outstanding'], 2) ?></span>
                    </div>
                    <div class="mt-3 pt-2 border-t">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase mb-1">Payment Gateways:</span>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach ($revRep['by_method'] as $pm): ?>
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium text-slate-700">
                                    <?= $pm['payment_method'] ?>: $<?= number_format($pm['total'], 0) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Appointment Reports -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i> Appointment Analytics
                    </h3>
                    <span class="badge badge-purple">Visits</span>
                </div>
                <div class="space-y-2 text-xs">
                    <span class="text-slate-400 block font-semibold mb-1">Status Overview:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <?php foreach ($aptRep['by_status'] as $as): ?>
                            <span class="px-2 py-1 bg-slate-100 rounded font-medium text-slate-700">
                                <?= $as['status'] ?>: <strong><?= $as['count'] ?></strong>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 pt-2 border-t">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase mb-1">Doctor Caseload:</span>
                        <div class="space-y-1">
                            <?php foreach ($aptRep['by_doctor'] as $ad): ?>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-slate-600 truncate"><?= htmlspecialchars($ad['full_name']) ?></span>
                                    <span class="font-bold text-slate-800"><?= $ad['count'] ?> consultations</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Pharmacy Reports -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="pill" class="w-4 h-4 text-teal-600"></i> Pharmacy Valuation & Stock
                    </h3>
                    <span class="badge badge-info"><?= $pharmRep['total_items'] ?> SKU</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Total Inventory Valuation:</span>
                        <span class="font-bold text-slate-900">$<?= number_format($pharmRep['inventory_value'], 2) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Low Stock SKUs:</span>
                        <span class="font-bold text-amber-600"><?= count($pharmRep['low_stock']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Expired SKUs:</span>
                        <span class="font-bold text-rose-600"><?= count($pharmRep['expired']) ?></span>
                    </div>
                </div>
            </div>

            <!-- 5. Laboratory Reports -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="flask-conical" class="w-4 h-4 text-amber-600"></i> Diagnostic Lab Reports
                    </h3>
                    <span class="badge badge-warning">Diagnostics</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Critical / Abnormal Flags:</span>
                        <span class="font-bold text-rose-600"><?= $labRep['abnormal_count'] ?> flagged</span>
                    </div>
                    <div class="mt-3 pt-2 border-t">
                        <span class="text-slate-400 block text-[10px] font-bold uppercase mb-1">Test Status Breakdown:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($labRep['by_status'] as $ls): ?>
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium text-slate-700">
                                    <?= $ls['sample_status'] ?>: <strong><?= $ls['count'] ?></strong>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Staff Reports -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-3 border-b pb-2">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                        <i data-lucide="badge-check" class="w-4 h-4 text-purple-600"></i> Workforce & Staff Reports
                    </h3>
                    <span class="badge badge-purple"><?= $staffRep['total_staff'] ?> Employees</span>
                </div>
                <div class="space-y-2 text-xs">
                    <span class="text-slate-400 block font-semibold mb-1">Today's Attendance Metrics:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <?php foreach ($staffRep['today_attendance'] as $ta): ?>
                            <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium text-slate-700">
                                <?= $ta['status'] ?>: <strong><?= $ta['count'] ?></strong>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Settlements Ledger -->
        <div class="card-clean p-5">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Recent Financial Settlements & Receipts</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase">
                        <tr>
                            <th class="py-2.5 px-3">Receipt Code</th>
                            <th class="py-2.5 px-3">Invoice #</th>
                            <th class="py-2.5 px-3">Patient</th>
                            <th class="py-2.5 px-3">Amount Paid</th>
                            <th class="py-2.5 px-3">Method</th>
                            <th class="py-2.5 px-3">Transaction Reference</th>
                            <th class="py-2.5 px-3">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($revRep['recent_payments'] as $rp): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-2 px-3 font-mono font-bold text-sky-700"><?= $rp['payment_code'] ?></td>
                                <td class="py-2 px-3 font-mono text-slate-600"><?= $rp['invoice_number'] ?></td>
                                <td class="py-2 px-3 font-bold text-slate-800"><?= htmlspecialchars($rp['patient_name']) ?></td>
                                <td class="py-2 px-3 font-bold text-emerald-600">$<?= number_format($rp['amount'], 2) ?></td>
                                <td class="py-2 px-3 text-slate-700"><?= htmlspecialchars($rp['payment_method']) ?></td>
                                <td class="py-2 px-3 font-mono text-slate-500 text-[11px]"><?= htmlspecialchars($rp['transaction_reference'] ?: '-') ?></td>
                                <td class="py-2 px-3 text-slate-500"><?= date('M d, Y H:i', strtotime($rp['payment_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
