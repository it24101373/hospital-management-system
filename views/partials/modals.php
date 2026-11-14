<?php
// Pre-fetch lists for modal dropdowns
$modalPatients = Database::fetchAll("SELECT id, patient_code, full_name, phone FROM patients ORDER BY full_name ASC");
$modalDoctors = Database::fetchAll("SELECT d.id, d.doctor_code, d.full_name, d.specialization, d.consultation_fee, dep.name as department_name, dep.id as department_id FROM doctors d JOIN departments dep ON d.department_id = dep.id WHERE d.status = 'active' ORDER BY d.full_name ASC");
$modalDepartments = Database::fetchAll("SELECT id, name FROM departments ORDER BY name ASC");
$modalMedicines = Database::fetchAll("SELECT id, code, name, unit_price, stock_quantity FROM pharmacy_medicines WHERE status != 'Out of Stock' ORDER BY name ASC");
?>

<!-- 1. Modal: Register Patient (SRS Section 3.2) -->
<div id="modal-add-patient" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="user-plus" class="w-5 h-5"></i> Register New Patient
            </h3>
            <button onclick="closeModal('modal-add-patient')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=create_patient" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" required placeholder="e.g. John Doe" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth *</label>
                    <input type="date" name="dob" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gender *</label>
                    <select name="gender" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Blood Group</label>
                    <select name="blood_group" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="Unknown" selected>Unknown</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Phone *</label>
                    <input type="text" name="phone" required placeholder="+1 555-0199" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" placeholder="john.doe@example.com" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Residential Address</label>
                    <input type="text" name="address" placeholder="Street, City, Postal Code" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" placeholder="e.g. Jane Doe (Spouse)" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Emergency Contact Phone</label>
                    <input type="text" name="emergency_contact_phone" placeholder="+1 555-0198" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Patient Type</label>
                    <select name="patient_type" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="Outpatient" selected>Outpatient (OPD)</option>
                        <option value="Inpatient">Inpatient (IPD Admission)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Known Allergies</label>
                    <input type="text" name="allergies" placeholder="e.g. Penicillin, Peanuts (or None)" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-add-patient')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save & Register</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Modal: Book Appointment (SRS Section 3.4) -->
<div id="modal-book-appointment" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="calendar-plus" class="w-5 h-5"></i> Book Doctor Appointment
            </h3>
            <button onclick="closeModal('modal-book-appointment')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=book_appointment" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Patient *</label>
                <select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="">-- Choose Patient --</option>
                    <?php foreach ($modalPatients as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?> (<?= $p['patient_code'] ?> - <?= $p['phone'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Doctor & Department *</label>
                <select name="doctor_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="">-- Choose Doctor --</option>
                    <?php foreach ($modalDoctors as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?> - <?= htmlspecialchars($d['specialization']) ?> ($<?= number_format($d['consultation_fee'], 2) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Appointment Date *</label>
                    <input type="date" name="appointment_date" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Time Slot *</label>
                    <select name="appointment_time" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="09:00 AM">09:00 AM</option>
                        <option value="09:30 AM">09:30 AM</option>
                        <option value="10:00 AM">10:00 AM</option>
                        <option value="10:30 AM">10:30 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="11:30 AM">11:30 AM</option>
                        <option value="02:00 PM">02:00 PM</option>
                        <option value="02:30 PM">02:30 PM</option>
                        <option value="03:00 PM">03:00 PM</option>
                        <option value="03:30 PM">03:30 PM</option>
                        <option value="04:00 PM">04:00 PM</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reason / Symptoms</label>
                <input type="text" name="reason" placeholder="e.g. Chest pain, Follow-up consultation" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Initial Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="Scheduled">Scheduled</option>
                    <option value="Confirmed" selected>Confirmed</option>
                </select>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-book-appointment')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Confirm Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal: Add Doctor (SRS Section 3.3) -->
<div id="modal-add-doctor" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-5 h-5"></i> Add Doctor Profile
            </h3>
            <button onclick="closeModal('modal-add-doctor')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=create_doctor" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Doctor Full Name *</label>
                    <input type="text" name="full_name" required placeholder="e.g. Dr. John Adams, MD" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Department *</label>
                    <select name="department_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <?php foreach ($modalDepartments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Specialization *</label>
                    <input type="text" name="specialization" required placeholder="e.g. Interventional Cardiology" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Qualification</label>
                    <input type="text" name="qualification" placeholder="MBBS, MD, FACC" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Consultation Fee ($) *</label>
                    <input type="number" step="0.01" name="consultation_fee" required value="75.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Room / Clinic Number</label>
                    <input type="text" name="room_number" placeholder="Room 201, Block B" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Schedule Days</label>
                    <input type="text" name="schedule_days" value="Mon,Tue,Wed,Thu,Fri" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Schedule Hours</label>
                    <input type="text" name="schedule_hours" value="09:00 - 17:00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" placeholder="+1 555-0145" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-add-doctor')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save Doctor</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Modal: Add Clinical Encounter / EMR (SRS Section 3.5) -->
<div id="modal-create-emr" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 overflow-hidden border border-slate-200 max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="clipboard-plus" class="w-5 h-5"></i> Record Clinical Diagnosis & Prescription
            </h3>
            <button onclick="closeModal('modal-create-emr')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=create_emr" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Patient *</label>
                    <select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">-- Choose Patient --</option>
                        <?php foreach ($modalPatients as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?> (<?= $p['patient_code'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Attending Doctor *</label>
                    <select name="doctor_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">-- Choose Doctor --</option>
                        <?php foreach ($modalDoctors as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?> (<?= $d['specialization'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Chief Complaint *</label>
                    <input type="text" name="chief_complaint" required placeholder="e.g. Acute chest discomfort, headache, fever for 3 days" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Clinical Diagnosis (with ICD-10 if applicable) *</label>
                    <input type="text" name="diagnosis" required placeholder="e.g. Essential Hypertension (I10) / Acute Upper Respiratory Infection" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>

            <!-- Patient Vitals Sub-section -->
            <div class="p-3 bg-sky-50 rounded-lg border border-sky-100">
                <h4 class="text-xs font-bold text-sky-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <i data-lucide="activity" class="w-4 h-4"></i> Patient Vitals Examination
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Blood Pressure</label>
                        <input type="text" name="vitals_bp" placeholder="120/80" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Heart Rate (bpm)</label>
                        <input type="number" name="vitals_heart_rate" placeholder="72" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Temp (°C)</label>
                        <input type="number" step="0.1" name="vitals_temp" placeholder="36.8" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">SpO2 (%)</label>
                        <input type="number" name="vitals_spo2" placeholder="98" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="vitals_weight" placeholder="70" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Clinical Notes & Findings</label>
                <textarea name="clinical_notes" rows="2" placeholder="Physical examination notes, auscultation findings, system review..." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Treatment Plan & Follow-up</label>
                <textarea name="treatment_plan" rows="2" placeholder="Lifestyle advice, dietary restriction, follow up in 2 weeks..." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none"></textarea>
            </div>

            <!-- Prescriptions Sub-section -->
            <div class="border-t pt-3">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="pill" class="w-4 h-4 text-emerald-600"></i> Prescribed Medicines
                    </h4>
                    <button type="button" onclick="addPrescriptionRow()" class="text-xs text-sky-600 hover:text-sky-800 font-bold flex items-center gap-1">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Add Medicine
                    </button>
                </div>
                <div id="prescription-rows" class="space-y-2">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200 text-sm items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Medicine Name & Strength</label>
                            <input type="text" name="prescriptions[0][medicine_name]" placeholder="e.g. Paracetamol 650mg" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-sky-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Dosage</label>
                            <input type="text" name="prescriptions[0][dosage]" value="1 tablet" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Frequency</label>
                            <input type="text" name="prescriptions[0][frequency]" value="TDS (3x daily)" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Duration</label>
                            <input type="text" name="prescriptions[0][duration]" value="5 days" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-create-emr')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save Medical Record</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Modal: Order Laboratory Test (SRS Section 3.6) -->
<div id="modal-order-lab" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="flask-conical" class="w-5 h-5"></i> Order Laboratory Investigation
            </h3>
            <button onclick="closeModal('modal-order-lab')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=order_lab" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Patient *</label>
                <select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="">-- Choose Patient --</option>
                    <?php foreach ($modalPatients as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?> (<?= $p['patient_code'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Referring Doctor</label>
                <select name="doctor_id" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <?php foreach ($modalDoctors as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?> (<?= $d['specialization'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Test Name *</label>
                <input type="text" name="test_name" required placeholder="e.g. Complete Blood Count (CBC) or Fasting Lipid Panel" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="Hematology">Hematology</option>
                        <option value="Biochemistry">Biochemistry</option>
                        <option value="Microbiology">Microbiology</option>
                        <option value="Radiology & Imaging">Radiology & Imaging</option>
                        <option value="Pathology">Pathology</option>
                        <option value="Serology">Serology</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Test Cost ($) *</label>
                    <input type="number" step="0.01" name="cost" value="45.00" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Standard Reference Range (Optional)</label>
                <input type="text" name="reference_range" placeholder="e.g. 70-99 mg/dL or WBC: 4.5-11.0 x10^3/uL" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-order-lab')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Submit Test Order</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. Modal: Add Pharmacy Medicine (SRS Section 3.7) -->
<div id="modal-add-medicine" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="pill" class="w-5 h-5"></i> Add Medicine to Inventory
            </h3>
            <button onclick="closeModal('modal-add-medicine')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=create_medicine" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Brand Name & Strength *</label>
                    <input type="text" name="name" required placeholder="e.g. Amoxicillin 500mg" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Generic Name</label>
                    <input type="text" name="generic_name" placeholder="Amoxicillin Trihydrate" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Therapeutic Category *</label>
                    <select name="category" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="Antibiotics">Antibiotics</option>
                        <option value="Cardiovascular">Cardiovascular</option>
                        <option value="Analgesics & Antipyretic">Analgesics & Antipyretic</option>
                        <option value="Antidiabetic">Antidiabetic</option>
                        <option value="Gastrointestinal">Gastrointestinal</option>
                        <option value="Respiratory">Respiratory</option>
                        <option value="Vitamins & Minerals">Vitamins & Minerals</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dosage Form *</label>
                    <select name="dosage_form" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="Tablet">Tablet</option>
                        <option value="Capsule">Capsule</option>
                        <option value="Syrup / Liquid">Syrup / Liquid</option>
                        <option value="Vial / Injection">Vial / Injection</option>
                        <option value="Inhaler">Inhaler</option>
                        <option value="Ointment">Ointment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Price ($) *</label>
                    <input type="number" step="0.01" name="unit_price" required value="10.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Initial Stock Quantity *</label>
                    <input type="number" name="stock_quantity" required value="100" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reorder Alert Level *</label>
                    <input type="number" name="reorder_level" required value="20" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Batch Number</label>
                    <input type="text" name="batch_number" placeholder="BAT-2026-X" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Expiry Date *</label>
                    <input type="date" name="expiry_date" required value="<?= date('Y-m-d', strtotime('+1 year')) ?>" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Manufacturer</label>
                    <input type="text" name="manufacturer" placeholder="e.g. Pfizer, Novartis, GSK" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-add-medicine')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Add to Stock</button>
            </div>
        </form>
    </div>
</div>

<!-- 7. Modal: Generate Invoice (SRS Section 3.8) -->
<div id="modal-create-bill" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5"></i> Generate Hospital Invoice
            </h3>
            <button onclick="closeModal('modal-create-bill')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=create_bill" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Patient *</label>
                <select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="">-- Choose Patient --</option>
                    <?php foreach ($modalPatients as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?> (<?= $p['patient_code'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Consultation Charges ($)</label>
                    <input type="number" step="0.01" name="total_consultation" value="100.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Laboratory Charges ($)</label>
                    <input type="number" step="0.01" name="total_lab" value="0.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pharmacy Charges ($)</label>
                    <input type="number" step="0.01" name="total_pharmacy" value="0.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Admission/Bed Charges ($)</label>
                    <input type="number" step="0.01" name="total_admission" value="0.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Discount ($)</label>
                    <input type="number" step="0.01" name="discount" value="0.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tax Rate (e.g. 0.05 for 5%)</label>
                    <input type="number" step="0.01" name="tax_rate" value="0.05" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Notes</label>
                <input type="text" name="notes" placeholder="Standard consultation and medical services rendered" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-create-bill')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Generate Invoice</button>
            </div>
        </form>
    </div>
</div>

<!-- 8. Modal: Admit Patient (SRS Scope & DB Module 6) -->
<div id="modal-admit-patient" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="bed-single" class="w-5 h-5"></i> Admit Inpatient to Ward
            </h3>
            <button onclick="closeModal('modal-admit-patient')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=admit_patient" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Patient *</label>
                <select name="patient_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="">-- Choose Patient --</option>
                    <?php foreach ($modalPatients as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['full_name']) ?> (<?= $p['patient_code'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Attending Physician *</label>
                <select name="doctor_id" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <?php foreach ($modalDoctors as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['full_name']) ?> (<?= $d['specialization'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ward Name *</label>
                    <select name="ward_name" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="General Medical Ward">General Medical Ward</option>
                        <option value="Cardiology Care Unit (CCU)">Cardiology Care Unit (CCU)</option>
                        <option value="Intensive Care Unit (ICU)">Intensive Care Unit (ICU)</option>
                        <option value="Pediatric Ward">Pediatric Ward</option>
                        <option value="Trauma & Orthopedic Wing">Trauma & Orthopedic Wing</option>
                        <option value="VIP Private Deluxe">VIP Private Deluxe</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Room Number *</label>
                    <input type="text" name="room_number" required placeholder="e.g. 204" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bed Number *</label>
                    <input type="text" name="bed_number" required placeholder="e.g. Bed-03" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Daily Bed Charge ($) *</label>
                    <input type="number" step="0.01" name="daily_bed_charge" value="120.00" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Admission Diagnosis & Reason</label>
                <textarea name="diagnosis_summary" rows="2" placeholder="Clinical justification for inpatient admission..." class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none"></textarea>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-admit-patient')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Confirm Admission</button>
            </div>
        </form>
    </div>
</div>

<!-- 9. Modal: Register Employee (SRS Section 3.9) -->
<div id="modal-add-employee" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="badge-check" class="w-5 h-5"></i> Register Hospital Employee
            </h3>
            <button onclick="closeModal('modal-add-employee')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=create_employee" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Employee Full Name *</label>
                <input type="text" name="full_name" required placeholder="e.g. Amanda Cole" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <?php foreach ($modalDepartments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Role Type *</label>
                    <select name="role" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="doctor">Doctor</option>
                        <option value="nurse">Nurse</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="lab">Laboratory Staff</option>
                        <option value="pharmacist">Pharmacist</option>
                        <option value="accountant">Accountant</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Designation Title *</label>
                    <input type="text" name="designation" required placeholder="e.g. Senior Staff Nurse" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Monthly Salary ($) *</label>
                    <input type="number" step="0.01" name="salary" required value="5000.00" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Phone *</label>
                    <input type="text" name="phone" required placeholder="+1 555-0188" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" placeholder="amanda.c@apexmemorial.org" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-add-employee')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save Employee</button>
            </div>
        </form>
    </div>
</div>
