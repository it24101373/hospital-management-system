<?php
$pageTitle = "Sign In";
require_once __DIR__ . '/partials/header.php';
?>

<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900">
    <div class="w-full max-w-md">
        <!-- Hospital Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-500 text-white shadow-xl shadow-sky-500/30 mb-3">
                <i data-lucide="cross" class="w-8 h-8 fill-white"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight"><?= HOSPITAL_NAME ?></h1>
            <p class="text-xs text-sky-200 mt-1"><?= HOSPITAL_TAGLINE ?></p>
        </div>

        <!-- Login Card (SRS Section 7 Login Page) -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-slate-100">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Hospital Portal Sign In</h2>
                <p class="text-xs text-slate-500 mt-0.5">Enter your hospital staff credentials to continue</p>
            </div>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                    <span>Session timed out due to 30 minutes of inactivity. Please sign in again.</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <span>Invalid username or password. Please try again.</span>
                </div>
            <?php endif; ?>

            <form action="index.php?action=login" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                        <input type="text" name="username" id="login-username" required placeholder="e.g. admin" value="admin" class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                        <input type="password" name="password" id="login-password" required placeholder="••••••••" value="password123" class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg shadow-md shadow-sky-600/30 transition flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Sign In to HMS</span>
                </button>
            </form>

            <!-- 1-Click Role Fill Buttons for quick evaluation (SRS Section 2 Roles) -->
            <div class="mt-6 pt-6 border-t border-slate-200">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5 text-center">
                    Quick Login Demo Roles (1-Click Fill)
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillLogin('admin', 'password123')" class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded text-left font-medium">
                        👑 Administrator
                    </button>
                    <button type="button" onclick="fillLogin('dr.johnson', 'password123')" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-left font-medium">
                        🩺 Doctor (Cardio)
                    </button>
                    <button type="button" onclick="fillLogin('nurse.emily', 'password123')" class="px-2.5 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 border border-pink-200 rounded text-left font-medium">
                        🩹 Head Nurse
                    </button>
                    <button type="button" onclick="fillLogin('receptionist', 'password123')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded text-left font-medium">
                        📋 Receptionist
                    </button>
                    <button type="button" onclick="fillLogin('labtech', 'password123')" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded text-left font-medium">
                        🔬 Laboratory Tech
                    </button>
                    <button type="button" onclick="fillLogin('pharmacist', 'password123')" class="px-2.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 rounded text-left font-medium">
                        💊 Pharmacist
                    </button>
                    <button type="button" onclick="fillLogin('accountant', 'password123')" class="col-span-2 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded text-left font-medium text-center">
                        💵 Hospital Accountant
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center text-xs text-slate-400">
            &copy; <?= date('Y') ?> <?= HOSPITAL_NAME ?>. All rights reserved.
        </div>
    </div>
</div>

<script>
function fillLogin(user, pass) {
    document.getElementById('login-username').value = user;
    document.getElementById('login-password').value = pass;
    showToast('Credentials filled for ' + user, 'info');
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
