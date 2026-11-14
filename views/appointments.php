<?php
$pageTitle = "Appointment Scheduling";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$date = $_GET['date'] ?? '';
$doctorId = !empty($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;
$status = $_GET['status'] ?? '';

$appointments = AppointmentController::getAll($date, $doctorId, $status);
$doctors = DoctorController::getAll();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Action & Search Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Appointment Scheduling</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage doctor appointments, patient slots, rescheduling, and status updates</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modal-book-appointment')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i> Schedule Appointment
                </button>
            </div>
        </div>

        <!-- Filter Bar (SRS Section 7 Appointment Screen) -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="page" value="appointments">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Appointment Date</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Doctor</label>
                    <select name="doctor_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Doctors</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $doctorId === (int)$d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['full_name']) ?> (<?= $d['department_code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="Scheduled" <?= $status === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="Confirmed" <?= $status === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                    <?php if (!empty($date) || $doctorId || !empty($status)): ?>
                        <a href="index.php?page=appointments" class="px-3 py-1.5 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Appointments Table & Status Tracking -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Date & Slot</th>
                            <th class="py-3 px-4">Patient Details</th>
                            <th class="py-3 px-4">Doctor & Dept</th>
                            <th class="py-3 px-4">Reason / Notes</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Update Status / Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-400">
                                    <i data-lucide="calendar-x" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    No appointments found matching your filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $apt): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700 whitespace-nowrap">
                                        <?= $apt['appointment_code'] ?>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900"><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></div>
                                        <div class="text-[11px] text-sky-600 font-medium"><?= $apt['appointment_time'] ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="index.php?page=patient_detail&id=<?= $apt['patient_id'] ?>" class="font-bold text-slate-900 hover:text-sky-600">
                                            <?= htmlspecialchars($apt['patient_name']) ?>
                                        </a>
                                        <div class="text-[11px] text-slate-400"><?= $apt['patient_code'] ?> | <?= htmlspecialchars($apt['patient_phone']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($apt['doctor_name']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= htmlspecialchars($apt['department_name']) ?> (<?= $apt['room_number'] ?>)</div>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate text-slate-600">
                                        <?= htmlspecialchars($apt['reason']) ?>
                                        <?php if ($apt['notes']): ?>
                                            <div class="text-[10px] text-slate-400 italic"><?= htmlspecialchars($apt['notes']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $stClass = match($apt['status']) {
                                            'Confirmed' => 'badge-success',
                                            'Scheduled' => 'badge-info',
                                            'Completed' => 'badge-purple',
                                            'Cancelled' => 'badge-danger',
                                            default     => 'badge-gray'
                                        };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= $apt['status'] ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($apt['status'] !== 'Completed' && $apt['status'] !== 'Cancelled'): ?>
                                                <a href="index.php?action=update_appointment_status&id=<?= $apt['id'] ?>&status=Confirmed" class="px-2 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded text-[11px] font-semibold" title="Confirm">
                                                    Confirm
                                                </a>
                                                <a href="index.php?action=update_appointment_status&id=<?= $apt['id'] ?>&status=Completed" class="px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded text-[11px] font-semibold" title="Complete Encounter">
                                                    Complete
                                                </a>
                                                <button onclick="promptReschedule(<?= $apt['id'] ?>, '<?= $apt['appointment_date'] ?>')" class="px-2 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded text-[11px] font-semibold" title="Reschedule">
                                                    Reschedule
                                                </button>
                                                <a href="index.php?action=cancel_appointment&id=<?= $apt['id'] ?>" onclick="return confirm('Cancel this appointment?')" class="p-1 text-rose-500 hover:bg-rose-50 rounded" title="Cancel Appointment">
                                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-[11px]">Encounter <?= $apt['status'] ?></span>
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

<!-- Modal: Reschedule Appointment -->
<div id="modal-reschedule" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="calendar-clock" class="w-5 h-5"></i> Reschedule Appointment
            </h3>
            <button onclick="closeModal('modal-reschedule')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=reschedule_appointment" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" id="reschedule-apt-id" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">New Appointment Date *</label>
                <input type="date" name="new_date" id="reschedule-date" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">New Time Slot *</label>
                <select name="new_time" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                    <option value="09:00 AM">09:00 AM</option>
                    <option value="09:30 AM">09:30 AM</option>
                    <option value="10:00 AM">10:00 AM</option>
                    <option value="10:30 AM">10:30 AM</option>
                    <option value="11:00 AM">11:00 AM</option>
                    <option value="02:00 PM">02:00 PM</option>
                    <option value="02:30 PM">02:30 PM</option>
                    <option value="03:00 PM">03:00 PM</option>
                    <option value="03:30 PM">03:30 PM</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reason for Rescheduling</label>
                <input type="text" name="reason" placeholder="e.g. Patient requested morning slot" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-reschedule')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Save Reschedule</button>
            </div>
        </form>
    </div>
</div>

<script>
function promptReschedule(id, currentDate) {
    document.getElementById('reschedule-apt-id').value = id;
    document.getElementById('reschedule-date').value = currentDate;
    openModal('modal-reschedule');
}
</script>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
