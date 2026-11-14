<?php
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole = Auth::role();

// Navigation Item Definition with Role-based access control
$navItems = [
    [
        'id'    => 'dashboard',
        'label' => 'Dashboard',
        'icon'  => 'layout-dashboard',
        'roles' => ['admin', 'doctor', 'nurse', 'receptionist', 'lab', 'pharmacist', 'accountant']
    ],
    [
        'id'    => 'patients',
        'label' => 'Patient Management',
        'icon'  => 'users',
        'roles' => ['admin', 'doctor', 'nurse', 'receptionist', 'accountant']
    ],
    [
        'id'    => 'appointments',
        'label' => 'Appointments',
        'icon'  => 'calendar-days',
        'roles' => ['admin', 'doctor', 'nurse', 'receptionist']
    ],
    [
        'id'    => 'doctors',
        'label' => 'Doctors & Depts',
        'icon'  => 'stethoscope',
        'roles' => ['admin', 'doctor', 'receptionist']
    ],
    [
        'id'    => 'emr',
        'label' => 'Electronic Medical Records',
        'icon'  => 'clipboard-plus',
        'roles' => ['admin', 'doctor', 'nurse']
    ],
    [
        'id'    => 'laboratory',
        'label' => 'Laboratory',
        'icon'  => 'flask-conical',
        'roles' => ['admin', 'doctor', 'lab']
    ],
    [
        'id'    => 'pharmacy',
        'label' => 'Pharmacy Inventory',
        'icon'  => 'pill',
        'roles' => ['admin', 'doctor', 'pharmacist']
    ],
    [
        'id'    => 'admissions',
        'label' => 'Inpatient / Beds',
        'icon'  => 'bed-single',
        'roles' => ['admin', 'doctor', 'nurse', 'receptionist']
    ],
    [
        'id'    => 'billing',
        'label' => 'Billing & Invoices',
        'icon'  => 'receipt',
        'roles' => ['admin', 'accountant', 'receptionist']
    ],
    [
        'id'    => 'staff',
        'label' => 'Staff & Attendance',
        'icon'  => 'badge-check',
        'roles' => ['admin', 'accountant']
    ],
    [
        'id'    => 'reports',
        'label' => 'Reports & Analytics',
        'icon'  => 'bar-chart-3',
        'roles' => ['admin', 'accountant', 'doctor']
    ],
    [
        'id'    => 'audit_logs',
        'label' => 'Security Audit Trail',
        'icon'  => 'shield-alert',
        'roles' => ['admin']
    ],
    [
        'id'    => 'backup_restore',
        'label' => 'Backup & Recovery',
        'icon'  => 'database-backup',
        'roles' => ['admin']
    ]
];
?>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-auto flex flex-col no-print shadow-xl">
    <!-- Top Branding in Sidebar -->
    <div class="h-16 flex items-center justify-between px-6 bg-slate-950 border-b border-slate-800">
        <a href="index.php?page=dashboard" class="flex items-center gap-2.5 font-bold text-white text-base">
            <span class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center font-black shadow-md shadow-sky-500/30">
                +
            </span>
            <span class="tracking-wide">HMS APEX</span>
        </a>
        <button class="lg:hidden text-slate-400 hover:text-white" onclick="document.getElementById('sidebar').classList.add('-translate-x-full')">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Navigation List -->
    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <div class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
            Clinical & Hospital Modules
        </div>

        <?php foreach ($navItems as $item): 
            $allowed = ($userRole === 'admin') || in_array($userRole, $item['roles'], true);
            if (!$allowed) continue;
            $isActive = ($currentPage === $item['id']);
        ?>
            <a href="index.php?page=<?= $item['id'] ?>" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $isActive ? 'bg-sky-600 text-white shadow-sm shadow-sky-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
                <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 <?= $isActive ? 'text-white' : 'text-slate-400' ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Quick Help & Hospital Details Footer in Sidebar -->
    <div class="p-4 bg-slate-950 border-t border-slate-800 text-xs text-slate-400 space-y-2">
        <div class="flex items-center justify-between">
            <span class="font-semibold text-slate-300">Emergency Hotline</span>
            <span class="text-rose-400 font-bold">Ext. 911</span>
        </div>
        <div class="text-[11px] text-slate-500 leading-tight">
            <?= HOSPITAL_ADDRESS ?>
        </div>
    </div>
</aside>
