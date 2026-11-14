<?php
$pageTitle = "Hospital Operations Dashboard";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$summary = ReportController::getDashboardSummary();
$user = Auth::user();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Dashboard Top Header & Quick Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Clinical & Operational Dashboard</h1>
                <p class="text-xs text-slate-500 mt-0.5">Real-time health center analytics, patient queue, and resource allocations</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="openModal('modal-add-patient')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Register Patient
                </button>
                <button onclick="openModal('modal-book-appointment')" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-600"></i> Book Appointment
                </button>
                <button onclick="openModal('modal-create-bill')" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-600"></i> New Invoice
                </button>
            </div>
        </div>

        <!-- 5 KPI Cards Specified in SRS Section 7 + Inpatient Bed KPI -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <!-- 1. Total Patients -->
            <div class="card-clean p-4 border-l-4 border-l-sky-500 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Patients</div>
                    <div class="text-2xl font-black text-slate-900 mt-1"><?= $summary['total_patients'] ?></div>
                    <div class="text-[11px] text-sky-600 font-medium mt-0.5">Registered files</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 2. Today's Appointments -->
            <div class="card-clean p-4 border-l-4 border-l-indigo-500 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Today's Visits</div>
                    <div class="text-2xl font-black text-slate-900 mt-1"><?= $summary['today_appointments'] ?></div>
                    <div class="text-[11px] text-indigo-600 font-medium mt-0.5"><?= date('M d, Y') ?></div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="calendar-check-2" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 3. Revenue Summary -->
            <div class="card-clean p-4 border-l-4 border-l-emerald-500 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Revenue Summary</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">$<?= number_format($summary['total_revenue'], 0) ?></div>
                    <div class="text-[11px] text-emerald-600 font-medium mt-0.5">Settled income</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 4. Laboratory Requests -->
            <div class="card-clean p-4 border-l-4 border-l-amber-500 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lab Requests</div>
                    <div class="text-2xl font-black text-slate-900 mt-1"><?= $summary['lab_requests'] ?></div>
                    <div class="text-[11px] text-amber-600 font-medium mt-0.5">Pending tests</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="flask-conical" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 5. Pharmacy Alerts -->
            <div class="card-clean p-4 border-l-4 border-l-rose-500 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pharmacy Alerts</div>
                    <div class="text-2xl font-black text-rose-600 mt-1"><?= $summary['pharmacy_alerts'] ?></div>
                    <div class="text-[11px] text-rose-500 font-medium mt-0.5"><?= $summary['low_stock_count'] ?> low / <?= $summary['expiring_count'] ?> exp</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
            </div>

            <!-- 6. Inpatient Bed Occupancy (SRS Scope) -->
            <div class="card-clean p-4 border-l-4 border-l-teal-500 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bed Occupancy</div>
                    <div class="text-2xl font-black text-slate-900 mt-1"><?= $summary['bed_occupancy_rate'] ?>%</div>
                    <div class="text-[11px] text-teal-600 font-medium mt-0.5"><?= $summary['admitted_count'] ?>/<?= $summary['total_beds'] ?> admitted</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="bed-single" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Charts Section (SRS Performance & Analytics) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Revenue & Activity Chart -->
            <div class="card-clean p-5 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Hospital Financial Overview</h3>
                        <p class="text-xs text-slate-500">Monthly consultation, laboratory, pharmacy, and admission billing trends</p>
                    </div>
                    <span class="badge badge-info">Fiscal Year <?= date('Y') ?></span>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Departmental Appointments Distribution -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Specialty Caseload</h3>
                        <p class="text-xs text-slate-500">Active patients by clinical department</p>
                    </div>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Today's Appointments & Live Audit Trail -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Appointments Table (SRS Section 7) -->
            <div class="card-clean p-5 lg:col-span-2 overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Today's Appointment Schedule</h3>
                        <p class="text-xs text-slate-500">Scheduled clinical consultations and check-ins</p>
                    </div>
                    <a href="index.php?page=appointments" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center gap-1">
                        View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-y border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3">Time</th>
                                <th class="py-2.5 px-3">Patient</th>
                                <th class="py-2.5 px-3">Doctor</th>
                                <th class="py-2.5 px-3">Reason</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($summary['recent_appointments'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-400">No appointments scheduled for today yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($summary['recent_appointments'] as $apt): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2.5 px-3 font-semibold text-slate-700 whitespace-nowrap"><?= $apt['appointment_time'] ?></td>
                                        <td class="py-2.5 px-3 font-bold text-slate-900"><?= htmlspecialchars($apt['patient_name']) ?></td>
                                        <td class="py-2.5 px-3 text-slate-600"><?= htmlspecialchars($apt['doctor_name']) ?></td>
                                        <td class="py-2.5 px-3 text-slate-500 max-w-xs truncate"><?= htmlspecialchars($apt['reason']) ?></td>
                                        <td class="py-2.5 px-3">
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
                                        <td class="py-2.5 px-3 text-right">
                                            <a href="index.php?page=patient_detail&id=<?= $apt['patient_id'] ?>" class="p-1 text-sky-600 hover:text-sky-800" title="View Patient History">
                                                <i data-lucide="external-link" class="w-4 h-4 inline"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Live Security Audit Feed (SRS Section 4 & 10) -->
            <div class="card-clean p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Security Audit Trail</h3>
                        <p class="text-xs text-slate-500">Live operational & access log</p>
                    </div>
                    <a href="index.php?page=audit_logs" class="text-xs font-semibold text-sky-600 hover:text-sky-800">View Logs</a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($summary['recent_audit'] as $log): ?>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/60 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-800 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                                <span class="text-[10px] text-slate-400"><?= date('H:i', strtotime($log['timestamp'])) ?></span>
                            </div>
                            <div class="text-slate-600 text-[11px] line-clamp-1"><?= htmlspecialchars($log['details']) ?></div>
                            <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                                <span>User: <strong class="text-slate-600"><?= htmlspecialchars($log['username']) ?></strong></span>
                                <span class="font-mono"><?= htmlspecialchars($log['module']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Dialogs Include -->
<?php require_once __DIR__ . '/partials/modals.php'; ?>

<!-- Chart.js Initializers -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Revenue Chart
    const revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
                datasets: [
                    {
                        label: 'Clinical Consultation',
                        data: [4200, 5100, 4800, 5900, 6400, 7100],
                        backgroundColor: '#0284c7',
                        borderRadius: 4
                    },
                    {
                        label: 'Laboratory & Pharmacy',
                        data: [2800, 3200, 3600, 4100, 4500, 4900],
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Inpatient Admissions',
                        data: [6500, 7200, 6900, 8100, 8900, 9500],
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return '$' + val; }
                        }
                    }
                }
            }
        });
    }

    // 2. Department Chart
    const deptCtx = document.getElementById('deptChart');
    if (deptCtx) {
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'General Medicine'],
                datasets: [{
                    data: [35, 20, 15, 18, 12],
                    backgroundColor: ['#0284c7', '#6366f1', '#ec4899', '#f59e0b', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                },
                cutout: '70%'
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
