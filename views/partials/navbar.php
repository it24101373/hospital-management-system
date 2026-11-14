<?php
$currentUser = Auth::user();
?>
<header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm no-print">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Left: Mobile Menu Toggle & Title -->
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="p-2 text-slate-500 rounded-lg hover:bg-slate-100 lg:hidden" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full')">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold text-sky-700 tracking-tight flex items-center gap-2">
                        <i data-lucide="cross" class="w-6 h-6 text-sky-600 fill-sky-100"></i>
                        <span><?= HOSPITAL_NAME ?></span>
                    </span>
                    <span class="hidden md:inline-block text-xs bg-sky-50 text-sky-700 px-2 py-0.5 rounded border border-sky-200 font-medium">HMS Enterprise v2.4</span>
                </div>
            </div>

            <!-- Center: Role Switcher Toolbar (SRS Section 2 - Instant Role Switching for Verification) -->
            <div class="hidden xl:flex items-center gap-1.5 bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs">
                <span class="text-slate-500 font-semibold px-2 flex items-center gap-1">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i> Test Roles:
                </span>
                <?php 
                $rolesList = [
                    'admin' => ['label' => 'Admin', 'color' => 'bg-purple-600'],
                    'doctor' => ['label' => 'Doctor', 'color' => 'bg-emerald-600'],
                    'nurse' => ['label' => 'Nurse', 'color' => 'bg-pink-600'],
                    'receptionist' => ['label' => 'Reception', 'color' => 'bg-blue-600'],
                    'lab' => ['label' => 'Lab', 'color' => 'bg-amber-600'],
                    'pharmacist' => ['label' => 'Pharmacy', 'color' => 'bg-teal-600'],
                    'accountant' => ['label' => 'Accounts', 'color' => 'bg-indigo-600']
                ];
                foreach ($rolesList as $rKey => $rInfo):
                    $isActive = ($currentUser['role_name'] ?? '') === $rKey;
                ?>
                    <button data-switch-role="<?= $rKey ?>" 
                            class="px-2 py-1 rounded font-medium transition <?= $isActive ? 'bg-white text-slate-900 shadow-sm font-bold border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' ?>">
                        <?= $rInfo['label'] ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Right: User Info & Actions -->
            <div class="flex items-center gap-3">
                <!-- Session Auto-Timeout Badge (SRS Section 4) -->
                <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200" title="Automatic Session Timeout: 30 minutes inactivity">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-500 animate-pulse"></i>
                    <span>Session: <strong class="text-slate-700">Active</strong></span>
                </div>

                <!-- User Profile & Dropdown -->
                <div class="relative flex items-center gap-2 pl-3 border-l border-slate-200">
                    <div class="w-9 h-9 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm border border-sky-300">
                        <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-tight"><?= htmlspecialchars($currentUser['full_name'] ?? 'User') ?></div>
                        <div class="text-[11px] font-medium text-sky-600 capitalize"><?= htmlspecialchars($currentUser['role_label'] ?? $currentUser['role_name']) ?></div>
                    </div>
                    <a href="index.php?action=logout" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Logout">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
