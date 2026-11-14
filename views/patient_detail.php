<?php
$patientId = (int)($_GET['id'] ?? 0);
$history = PatientController::getMedicalHistory($patientId);

if (empty($history) || empty($history['patient'])) {
    setFlash('error', 'Patient not found.');
    header('Location: index.php?page=patients');
    exit;
}

$patient = $history['patient'];
$pageTitle = $patient['full_name'] . " - Medical History";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Back Link & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="index.php?page=patients" class="p-2 bg-white border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <?= htmlspecialchars($patient['full_name']) ?>
                        <span class="text-sm font-mono px-2 py-0.5 rounded bg-sky-100 text-sky-800 border border-sky-300 font-bold">
                            <?= $patient['patient_code'] ?>
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500">Centralized Electronic Medical Record & Comprehensive Treatment History</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="openModal('modal-create-emr')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="clipboard-plus" class="w-3.5 h-3.5"></i> Add Clinical Encounter
                </button>
                <button onclick="openModal('modal-order-lab')" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="flask-conical" class="w-3.5 h-3.5 text-amber-600"></i> Order Lab Test
                </button>
                <button onclick="openModal('modal-admit-patient')" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="bed-single" class="w-3.5 h-3.5 text-purple-600"></i> Admit Inpatient
                </button>
                <button onclick="openModal('modal-upload-doc')" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i> Upload Document
                </button>
            </div>
        </div>

        <!-- Patient Demographics & Profile Summary Card -->
        <div class="card-clean p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <div class="text-[11px] font-bold uppercase text-slate-400">Demographics</div>
                    <div class="text-sm font-bold text-slate-800 mt-1"><?= $patient['gender'] ?>, <?= $patient['dob'] ?></div>
                    <div class="text-xs text-slate-500 mt-0.5">Blood Group: <strong class="text-rose-600 font-bold"><?= $patient['blood_group'] ?></strong></div>
                    <div class="mt-2">
                        <span class="badge <?= $patient['patient_type'] === 'Inpatient' ? 'badge-purple' : 'badge-info' ?>">
                            <?= $patient['patient_type'] ?> Status
                        </span>
                    </div>
                </div>

                <div>
                    <div class="text-[11px] font-bold uppercase text-slate-400">Contact Information</div>
                    <div class="text-xs text-slate-700 mt-1 flex items-center gap-1.5">
                        <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i> <?= htmlspecialchars($patient['phone']) ?>
                    </div>
                    <?php if ($patient['email']): ?>
                        <div class="text-xs text-slate-700 mt-1 flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i> <?= htmlspecialchars($patient['email']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i> <?= htmlspecialchars($patient['address'] ?: 'Not provided') ?>
                    </div>
                </div>

                <div>
                    <div class="text-[11px] font-bold uppercase text-slate-400">Emergency Contact</div>
                    <div class="text-xs font-semibold text-slate-800 mt-1"><?= htmlspecialchars($patient['emergency_contact_name'] ?: 'None listed') ?></div>
                    <div class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($patient['emergency_contact_phone'] ?: '-') ?></div>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
                    <div class="text-[11px] font-bold uppercase text-rose-800 flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i> Known Allergies & Alerts
                    </div>
                    <div class="text-xs font-bold text-rose-900 mt-2">
                        <?= htmlspecialchars($patient['allergies'] ?: 'No known drug or environmental allergies reported.') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Tabs: Clinical Records, Appointments, Labs, Admissions, Invoices, Documents -->
        <div class="space-y-6">
            <!-- 1. Clinical Encounters & Prescriptions (EMR) -->
            <div class="card-clean p-6">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-sky-600"></i> Clinical Diagnoses & Treatment History
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold"><?= count($history['records']) ?> Encounter(s) Recorded</span>
                </div>

                <?php if (empty($history['records'])): ?>
                    <div class="text-center py-6 text-slate-400 text-xs">No clinical encounters recorded yet for this patient.</div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($history['records'] as $rec): ?>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/80 pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-slate-900"><?= htmlspecialchars($rec['diagnosis']) ?></span>
                                        <span class="text-xs text-slate-500 font-medium">by <?= htmlspecialchars($rec['doctor_name']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-400"><?= date('M d, Y H:i', strtotime($rec['record_date'])) ?></span>
                                        <a href="index.php?page=print_prescription&record_id=<?= $rec['id'] ?>" target="_blank" class="px-2 py-1 bg-white hover:bg-slate-100 border rounded text-[11px] font-semibold text-sky-700 flex items-center gap-1">
                                            <i data-lucide="printer" class="w-3 h-3"></i> Print Rx
                                        </a>
                                    </div>
                                </div>

                                <div class="text-xs text-slate-700">
                                    <strong class="text-slate-900">Chief Complaint:</strong> <?= htmlspecialchars($rec['chief_complaint']) ?>
                                </div>

                                <!-- Vitals Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs bg-white p-2.5 rounded-lg border border-slate-200">
                                    <div><span class="text-slate-400 text-[10px] block">BP</span> <strong><?= $rec['vitals_bp'] ?: '-' ?></strong></div>
                                    <div><span class="text-slate-400 text-[10px] block">Heart Rate</span> <strong><?= $rec['vitals_heart_rate'] ? $rec['vitals_heart_rate'] . ' bpm' : '-' ?></strong></div>
                                    <div><span class="text-slate-400 text-[10px] block">Temp</span> <strong><?= $rec['vitals_temp'] ? $rec['vitals_temp'] . ' °C' : '-' ?></strong></div>
                                    <div><span class="text-slate-400 text-[10px] block">SpO2</span> <strong><?= $rec['vitals_spo2'] ? $rec['vitals_spo2'] . ' %' : '-' ?></strong></div>
                                    <div><span class="text-slate-400 text-[10px] block">Weight</span> <strong><?= $rec['vitals_weight'] ? $rec['vitals_weight'] . ' kg' : '-' ?></strong></div>
                                </div>

                                <?php if ($rec['clinical_notes']): ?>
                                    <div class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200">
                                        <strong class="text-slate-800">Doctor Notes:</strong> <?= htmlspecialchars($rec['clinical_notes']) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Prescriptions Table -->
                                <?php if (!empty($rec['prescriptions'])): ?>
                                    <div class="mt-2">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1 flex items-center gap-1">
                                            <i data-lucide="pill" class="w-3.5 h-3.5 text-emerald-600"></i> Prescribed Medications
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left text-xs bg-white rounded-lg border border-slate-200">
                                                <thead class="bg-slate-50 text-slate-600 font-semibold border-b">
                                                    <tr>
                                                        <th class="py-1.5 px-3">Medicine</th>
                                                        <th class="py-1.5 px-3">Dosage</th>
                                                        <th class="py-1.5 px-3">Frequency</th>
                                                        <th class="py-1.5 px-3">Duration</th>
                                                        <th class="py-1.5 px-3">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    <?php foreach ($rec['prescriptions'] as $pr): ?>
                                                        <tr>
                                                            <td class="py-1.5 px-3 font-semibold text-slate-800"><?= htmlspecialchars($pr['medicine_name']) ?></td>
                                                            <td class="py-1.5 px-3 text-slate-600"><?= htmlspecialchars($pr['dosage']) ?></td>
                                                            <td class="py-1.5 px-3 text-slate-600"><?= htmlspecialchars($pr['frequency']) ?></td>
                                                            <td class="py-1.5 px-3 text-slate-600"><?= htmlspecialchars($pr['duration']) ?></td>
                                                            <td class="py-1.5 px-3">
                                                                <span class="badge <?= $pr['is_dispensed'] ? 'badge-success' : 'badge-warning' ?>">
                                                                    <?= $pr['is_dispensed'] ? 'Dispensed' : 'Pending Dispense' ?>
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
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2. Laboratory Investigations (SRS Section 3.6) -->
            <div class="card-clean p-6">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="flask-conical" class="w-5 h-5 text-amber-600"></i> Laboratory Test Results
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold"><?= count($history['lab_tests']) ?> Test(s)</span>
                </div>

                <?php if (empty($history['lab_tests'])): ?>
                    <div class="text-center py-6 text-slate-400 text-xs">No laboratory investigations ordered for this patient yet.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-600 font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Test Code</th>
                                    <th class="py-2.5 px-3">Test Name & Category</th>
                                    <th class="py-2.5 px-3">Order Date</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Findings / Results</th>
                                    <th class="py-2.5 px-3 text-right">Report</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($history['lab_tests'] as $lt): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-2.5 px-3 font-mono font-bold text-sky-700"><?= $lt['test_code'] ?></td>
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-slate-900"><?= htmlspecialchars($lt['test_name']) ?></div>
                                            <div class="text-[10px] text-slate-500"><?= htmlspecialchars($lt['category']) ?></div>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-600"><?= date('M d, Y', strtotime($lt['order_date'])) ?></td>
                                        <td class="py-2.5 px-3">
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
                                        <td class="py-2.5 px-3 max-w-md">
                                            <?php if ($lt['results']): ?>
                                                <div class="text-xs <?= $lt['is_abnormal'] ? 'text-rose-700 font-semibold' : 'text-slate-800' ?>">
                                                    <?= $lt['is_abnormal'] ? '⚠️ ' : '' ?><?= htmlspecialchars($lt['results']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-slate-400 italic">Pending analysis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                            <?php if ($lt['sample_status'] === 'Completed'): ?>
                                                <a href="index.php?page=print_lab_report&id=<?= $lt['id'] ?>" target="_blank" class="px-2 py-1 bg-white hover:bg-slate-100 border rounded text-[11px] font-semibold text-sky-700 inline-flex items-center gap-1">
                                                    <i data-lucide="printer" class="w-3 h-3"></i> Report
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 3. Inpatient Admissions & Bed Allocations (SRS Scope & DB Module 6) -->
            <div class="card-clean p-6">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="bed-single" class="w-5 h-5 text-purple-600"></i> Inpatient Admissions (IPD)
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold"><?= count($history['admissions']) ?> Admission(s)</span>
                </div>

                <?php if (empty($history['admissions'])): ?>
                    <div class="text-center py-6 text-slate-400 text-xs">Patient has no inpatient admission records.</div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($history['admissions'] as $adm): ?>
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-purple-700"><?= $adm['admission_code'] ?></span>
                                        <span class="badge <?= $adm['status'] === 'Admitted' ? 'badge-success' : 'badge-gray' ?>"><?= $adm['status'] ?></span>
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($adm['ward_name']) ?> - Room <?= $adm['room_number'] ?>, <?= $adm['bed_number'] ?></span>
                                    </div>
                                    <div class="text-slate-500 mt-1">
                                        Attending: <strong><?= htmlspecialchars($adm['doctor_name']) ?></strong> | Admitted: <?= date('M d, Y H:i', strtotime($adm['admission_date'])) ?>
                                        <?= $adm['discharge_date'] ? ' | Discharged: ' . date('M d, Y H:i', strtotime($adm['discharge_date'])) : '' ?>
                                    </div>
                                    <?php if ($adm['discharge_notes']): ?>
                                        <div class="mt-1 text-slate-600 bg-white p-2 rounded border border-slate-200">
                                            <strong>Discharge Summary:</strong> <?= htmlspecialchars($adm['discharge_notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <div class="text-slate-400 text-[11px]">Bed Charge</div>
                                    <div class="font-bold text-slate-900">$<?= number_format($adm['daily_bed_charge'], 2) ?> / day</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 4. Patient Uploaded Medical Documents (SRS Section 3.2: Upload Documents) -->
            <div class="card-clean p-6">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-teal-600"></i> Medical Documents & Scans (SRS 3.2)
                    </h3>
                    <button onclick="openModal('modal-upload-doc')" class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded text-xs font-semibold flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Upload Document
                    </button>
                </div>

                <?php if (empty($history['documents'])): ?>
                    <div class="text-center py-6 text-slate-400 text-xs">No documents uploaded for this patient. Click "Upload Document" to attach diagnostic scans, referral letters, or lab slips.</div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <?php foreach ($history['documents'] as $doc): ?>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-start gap-3 text-xs">
                                <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center flex-shrink-0 font-bold uppercase text-[10px]">
                                    <?= htmlspecialchars($doc['file_type']) ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-800 truncate" title="<?= htmlspecialchars($doc['original_name']) ?>">
                                        <?= htmlspecialchars($doc['original_name']) ?>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 truncate"><?= htmlspecialchars($doc['description'] ?: 'Diagnostic record') ?></div>
                                    <div class="text-[10px] text-slate-400 mt-1">Uploaded <?= date('M d, Y', strtotime($doc['upload_date'])) ?> by <?= htmlspecialchars($doc['uploaded_by']) ?></div>
                                </div>
                                <a href="public/uploads/<?= urlencode($doc['file_name']) ?>" target="_blank" download class="p-1.5 text-sky-600 hover:bg-sky-100 rounded" title="Download">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Modal: Upload Patient Document (SRS Section 3.2) -->
<div id="modal-upload-doc" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5"></i> Upload Medical Document
            </h3>
            <button onclick="closeModal('modal-upload-doc')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=upload_patient_doc" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select File (PDF, PNG, JPG, DOC) *</label>
                <input type="file" name="document" required class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Description / Notes</label>
                <input type="text" name="description" placeholder="e.g. Chest X-Ray Scan, Prior Blood Test Slip" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-upload-doc')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Upload File</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
