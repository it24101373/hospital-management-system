<?php
$recordId = (int)($_GET['record_id'] ?? 0);
$record = EMRController::getById($recordId);

if (!$record) {
    die("Medical record not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - <?= htmlspecialchars($record['patient_name']) ?> - <?= HOSPITAL_NAME ?></title>
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
                &larr; Back to Medical Records
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5">
                Print Prescription
            </button>
        </div>

        <!-- Doctor Header & Hospital Letterhead -->
        <div class="flex justify-between items-start border-b-2 border-sky-600 pb-5 mb-5">
            <div>
                <h1 class="text-xl font-black text-sky-800 tracking-tight"><?= htmlspecialchars($record['doctor_name']) ?></h1>
                <p class="text-xs font-semibold text-slate-700"><?= htmlspecialchars($record['specialization']) ?></p>
                <p class="text-[11px] text-slate-500"><?= htmlspecialchars($record['qualification'] ?: 'Senior Medical Officer') ?></p>
            </div>
            <div class="text-right">
                <h2 class="text-lg font-black text-slate-900"><?= HOSPITAL_NAME ?></h2>
                <p class="text-xs text-slate-500"><?= HOSPITAL_ADDRESS ?></p>
                <p class="text-xs text-slate-500"><?= HOSPITAL_PHONE ?></p>
            </div>
        </div>

        <!-- Patient Demographics & Vitals Row -->
        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 text-xs mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Patient Name</span>
                    <strong class="text-slate-900 text-sm"><?= htmlspecialchars($record['patient_name']) ?></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Patient ID / Gender / Age</span>
                    <span class="font-mono text-slate-700 font-bold"><?= $record['patient_code'] ?></span> | <?= $record['gender'] ?>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Blood Group</span>
                    <span class="font-bold text-rose-600"><?= $record['blood_group'] ?: 'Unknown' ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Encounter Date</span>
                    <span class="font-semibold text-slate-800"><?= date('M d, Y H:i', strtotime($record['record_date'])) ?></span>
                </div>
            </div>

            <!-- Vitals Bar -->
            <div class="pt-2 border-t border-slate-200/80 flex flex-wrap gap-4 text-[11px]">
                <span>BP: <strong><?= $record['vitals_bp'] ?: '-' ?></strong></span>
                <span>Pulse: <strong><?= $record['vitals_heart_rate'] ? $record['vitals_heart_rate'] . ' bpm' : '-' ?></strong></span>
                <span>Temp: <strong><?= $record['vitals_temp'] ? $record['vitals_temp'] . ' °C' : '-' ?></strong></span>
                <span>SpO2: <strong><?= $record['vitals_spo2'] ? $record['vitals_spo2'] . ' %' : '-' ?></strong></span>
                <span>Weight: <strong><?= $record['vitals_weight'] ? $record['vitals_weight'] . ' kg' : '-' ?></strong></span>
                <?php if ($record['allergies'] && $record['allergies'] !== 'None'): ?>
                    <span class="text-rose-600 font-bold">⚠️ Allergies: <?= htmlspecialchars($record['allergies']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Clinical Diagnosis -->
        <div class="mb-6 space-y-1 text-xs">
            <div><strong class="text-slate-900">Chief Complaint:</strong> <?= htmlspecialchars($record['chief_complaint']) ?></div>
            <div><strong class="text-sky-800">Clinical Diagnosis:</strong> <?= htmlspecialchars($record['diagnosis']) ?></div>
            <?php if ($record['clinical_notes']): ?>
                <div class="text-slate-600 text-[11px]"><strong class="text-slate-800">Clinical Observations:</strong> <?= htmlspecialchars($record['clinical_notes']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Rx Prescription Section -->
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-2xl font-black font-serif italic text-sky-800">℞</span>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Medical Prescription</span>
            </div>

            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase border-y border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Medicine & Strength</th>
                        <th class="py-2.5 px-3">Dosage</th>
                        <th class="py-2.5 px-3">Frequency</th>
                        <th class="py-2.5 px-3">Duration</th>
                        <th class="py-2.5 px-3">Instructions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    $pIndex = 1;
                    foreach ($record['prescriptions'] as $pr): 
                    ?>
                        <tr>
                            <td class="py-2.5 px-3 text-slate-400"><?= $pIndex++ ?></td>
                            <td class="py-2.5 px-3 font-bold text-slate-900"><?= htmlspecialchars($pr['medicine_name']) ?></td>
                            <td class="py-2.5 px-3 text-slate-700"><?= htmlspecialchars($pr['dosage']) ?></td>
                            <td class="py-2.5 px-3 text-slate-700"><?= htmlspecialchars($pr['frequency']) ?></td>
                            <td class="py-2.5 px-3 text-slate-700"><?= htmlspecialchars($pr['duration']) ?></td>
                            <td class="py-2.5 px-3 text-slate-500"><?= htmlspecialchars($pr['instructions'] ?: 'As directed') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($record['treatment_plan']): ?>
            <div class="p-3 bg-slate-50 rounded border text-xs mb-8">
                <strong class="text-slate-800 block mb-1">Follow-up & Lifestyle Advice:</strong>
                <p class="text-slate-600"><?= htmlspecialchars($record['treatment_plan']) ?></p>
            </div>
        <?php endif; ?>

        <!-- Signature -->
        <div class="flex justify-between items-end border-t pt-8 mt-12 text-xs">
            <div class="text-[10px] text-slate-400">
                Printed on: <?= date('Y-m-d H:i:s') ?> | System Electronic Record
            </div>
            <div class="text-right">
                <div class="w-48 border-b border-slate-400 mb-1 pb-8 text-center text-slate-300 italic">
                    Doctor Signature
                </div>
                <div class="font-bold text-slate-800"><?= htmlspecialchars($record['doctor_name']) ?></div>
                <div class="text-[10px] text-slate-500">Registered Medical Practitioner</div>
            </div>
        </div>
    </div>
</body>
</html>
