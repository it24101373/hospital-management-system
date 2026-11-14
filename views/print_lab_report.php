<?php
$testId = (int)($_GET['id'] ?? 0);
$test = LabController::getById($testId);

if (!$test) {
    die("Laboratory test record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Report <?= htmlspecialchars($test['test_code']) ?> - <?= HOSPITAL_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="public/css/custom.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 text-slate-800">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-slate-200 print-sheet">
        <!-- Print Toolbar -->
        <div class="no-print mb-6 flex items-center justify-between border-b pb-4">
            <button onclick="window.history.back()" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1">
                &larr; Back to Laboratory
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5">
                Print Diagnostic Report
            </button>
        </div>

        <!-- Header / Hospital & Lab Banner -->
        <div class="flex justify-between items-start border-b-2 border-amber-600 pb-5 mb-5">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight"><?= HOSPITAL_NAME ?></h1>
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider">Department of Diagnostic Pathology & Clinical Laboratory</p>
                <p class="text-[11px] text-slate-500 mt-1"><?= HOSPITAL_ADDRESS ?> | Ph: <?= HOSPITAL_PHONE ?></p>
            </div>
            <div class="text-right">
                <span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded font-bold text-xs">
                    Diagnostic Lab Report
                </span>
                <div class="font-mono font-bold text-slate-800 text-sm mt-1"><?= $test['test_code'] ?></div>
                <div class="text-[11px] text-slate-400">Order: <?= date('M d, Y H:i', strtotime($test['order_date'])) ?></div>
            </div>
        </div>

        <!-- Patient Demographics Card -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-lg border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Patient Name</span>
                <strong class="text-slate-900 text-sm"><?= htmlspecialchars($test['patient_name']) ?></strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Patient Code / Gender</span>
                <span class="font-mono text-slate-700 font-bold"><?= $test['patient_code'] ?></span> | <?= $test['gender'] ?>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Referring Physician</span>
                <span class="font-semibold text-slate-800"><?= htmlspecialchars($test['doctor_name'] ?: 'Clinical OPD') ?></span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Specimen Received</span>
                <span class="font-semibold text-slate-800"><?= $test['sample_collected_at'] ? date('M d, Y H:i', strtotime($test['sample_collected_at'])) : 'Standard collection' ?></span>
            </div>
        </div>

        <!-- Investigation Findings Details -->
        <div class="mb-6">
            <div class="flex items-center justify-between bg-slate-100 p-2.5 rounded font-bold text-xs text-slate-800 mb-3">
                <span>Test: <?= htmlspecialchars($test['test_name']) ?></span>
                <span class="text-slate-500 font-normal">Discipline: <?= htmlspecialchars($test['category']) ?></span>
            </div>

            <table class="w-full text-left text-xs mb-4">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase border-y border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Investigation Parameter</th>
                        <th class="py-2.5 px-3">Observed Result Value</th>
                        <th class="py-2.5 px-3">Standard Reference Interval</th>
                        <th class="py-2.5 px-3">Flag Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 px-3 font-semibold text-slate-800"><?= htmlspecialchars($test['test_name']) ?></td>
                        <td class="py-3 px-3 font-bold <?= $test['is_abnormal'] ? 'text-rose-700 text-sm' : 'text-slate-900' ?>">
                            <?= htmlspecialchars($test['results']) ?>
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-600"><?= htmlspecialchars($test['reference_range'] ?: 'Standard normal thresholds') ?></td>
                        <td class="py-3 px-3">
                            <?php if ($test['is_abnormal']): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    ABNORMAL / CRITICAL
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    NORMAL
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if ($test['technician_notes']): ?>
                <div class="p-3 bg-slate-50 rounded border text-xs">
                    <strong class="text-slate-700 block mb-1">Technologist Comments & Methodology:</strong>
                    <p class="text-slate-600"><?= htmlspecialchars($test['technician_notes']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Verification Signatures -->
        <div class="grid grid-cols-2 gap-8 border-t pt-8 mt-12 text-xs">
            <div>
                <p class="text-[10px] text-slate-400">
                    Conducted by: <strong><?= htmlspecialchars($test['conducted_by'] ?: 'Medical Laboratory Technologist') ?></strong><br>
                    Verification timestamp: <?= date('Y-m-d H:i:s', strtotime($test['result_entered_at'] ?? 'now')) ?>
                </p>
            </div>
            <div class="text-right flex flex-col items-end">
                <div class="w-48 border-b border-slate-400 mb-1 pb-8 text-center text-slate-300 italic">
                    Laboratory Seal & Signature
                </div>
                <div class="font-bold text-slate-800">Chief Medical Laboratory Scientist</div>
                <div class="text-[10px] text-slate-500">Board Certified Clinical Pathologist</div>
            </div>
        </div>
    </div>
</body>
</html>
