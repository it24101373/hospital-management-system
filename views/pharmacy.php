<?php
$pageTitle = "Pharmacy & Inventory";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$medicines = PharmacyController::getAll($search, $category, $status);
$alerts = PharmacyController::getAlerts();
$pendingPrescriptions = PharmacyController::getPendingPrescriptions();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pharmacy Management System</h1>
                <p class="text-xs text-slate-500 mt-0.5">Medication inventory, stock replenishment, batch expiry tracking, and prescription dispensing</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php?action=export_csv&type=pharmacy" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i> Export Inventory CSV
                </a>
                <button onclick="openModal('modal-add-medicine')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add New Medicine
                </button>
            </div>
        </div>

        <!-- Expiry & Low Stock Warning Banners (SRS Section 3.7 Expiry Monitoring) -->
        <?php if (!empty($alerts['low_stock']) || !empty($alerts['expiring_soon'])): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <?php if (!empty($alerts['low_stock'])): ?>
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                        <div class="font-bold flex items-center gap-1.5 text-amber-800 mb-1">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i> Low Stock Reorder Warning (<?= count($alerts['low_stock']) ?> Items)
                        </div>
                        <div class="text-[11px] text-amber-700 mb-2">The following items have reached or fallen below minimum safety stock levels:</div>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($alerts['low_stock'] as $ls): ?>
                                <span class="px-2 py-0.5 bg-white border border-amber-300 rounded text-[10px] font-semibold">
                                    <?= htmlspecialchars($ls['name']) ?> (<strong><?= $ls['stock_quantity'] ?> left</strong> / min <?= $ls['reorder_level'] ?>)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($alerts['expiring_soon'])): ?>
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
                        <div class="font-bold flex items-center gap-1.5 text-rose-800 mb-1">
                            <i data-lucide="clock-alert" class="w-4 h-4 text-rose-600"></i> Expiry Alert: Expiring Within 30 Days (<?= count($alerts['expiring_soon']) ?> Items)
                        </div>
                        <div class="text-[11px] text-rose-700 mb-2">Priority attention needed for near-expiry pharmaceutical batches:</div>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($alerts['expiring_soon'] as $ex): ?>
                                <span class="px-2 py-0.5 bg-white border border-rose-300 rounded text-[10px] font-semibold text-rose-800">
                                    <?= htmlspecialchars($ex['name']) ?> (Exp: <strong><?= $ex['expiry_date'] ?></strong>)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Pending Prescriptions Section (SRS 3.7 Prescription Processing) -->
        <?php if (!empty($pendingPrescriptions)): ?>
            <div class="card-clean p-5 mb-6 bg-gradient-to-r from-sky-50 to-white border-sky-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-sky-900 flex items-center gap-1.5">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-sky-600"></i> Prescriptions Awaiting Pharmacy Dispense
                    </h3>
                    <span class="badge badge-warning"><?= count($pendingPrescriptions) ?> Pending Dispense</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($pendingPrescriptions as $pRx): ?>
                        <div class="p-3 bg-white rounded-lg border border-slate-200 text-xs shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($pRx['medicine_name']) ?></div>
                                <div class="text-[11px] text-slate-500">Patient: <strong><?= htmlspecialchars($pRx['patient_name']) ?></strong></div>
                                <div class="text-[11px] text-slate-500">Prescribed by: <?= htmlspecialchars($pRx['doctor_name']) ?></div>
                                <div class="mt-1 text-[11px] text-slate-600 bg-slate-50 p-1.5 rounded">
                                    <?= htmlspecialchars($pRx['dosage']) ?> | <?= htmlspecialchars($pRx['frequency']) ?> (<?= htmlspecialchars($pRx['duration']) ?>)
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-t flex justify-end">
                                <a href="index.php?action=dispense_prescription&id=<?= $pRx['id'] ?>" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold flex items-center gap-1 shadow-sm">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Dispense Medication
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search & Category Filters -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="page" value="pharmacy">
                <div class="sm:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search medicine, generic name, code, manufacturer..." class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Stock Statuses</option>
                        <option value="Low Stock" <?= $status === 'Low Stock' ? 'selected' : '' ?>>Low Stock</option>
                        <option value="Expiring Soon" <?= $status === 'Expiring Soon' ? 'selected' : '' ?>>Expiring Soon (30d)</option>
                        <option value="Expired" <?= $status === 'Expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || !empty($status)): ?>
                        <a href="index.php?page=pharmacy" class="px-3 py-2 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Medicines Inventory Table -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Brand & Generic Name</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Form</th>
                            <th class="py-3 px-4">Batch & Expiry</th>
                            <th class="py-3 px-4">Unit Price</th>
                            <th class="py-3 px-4">In Stock / Reorder</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Stock Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($medicines)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-400">No medicines found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($medicines as $m): 
                                $isNearExpiry = strtotime($m['expiry_date']) <= strtotime('+30 days');
                                $isExpired = strtotime($m['expiry_date']) < time();
                            ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700 whitespace-nowrap"><?= $m['code'] ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($m['name']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= htmlspecialchars($m['generic_name']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700"><?= htmlspecialchars($m['category']) ?></td>
                                    <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($m['dosage_form']) ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-mono text-slate-600 text-[11px]"><?= $m['batch_number'] ?></div>
                                        <div class="text-[11px] font-semibold <?= $isExpired ? 'text-rose-600 font-bold' : ($isNearExpiry ? 'text-amber-600' : 'text-slate-500') ?>">
                                            Exp: <?= $m['expiry_date'] ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-emerald-600">$<?= number_format($m['unit_price'], 2) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 text-sm"><?= $m['stock_quantity'] ?></span>
                                        <span class="text-slate-400 text-[11px]">/ min <?= $m['reorder_level'] ?></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $stClass = match($m['status']) {
                                            'Available'    => 'badge-success',
                                            'Low Stock'    => 'badge-warning',
                                            'Out of Stock' => 'badge-danger',
                                            'Expired'      => 'badge-danger',
                                            default        => 'badge-gray'
                                        };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= $m['status'] ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <button onclick="promptRestock(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['name'])) ?>', <?= $m['stock_quantity'] ?>)" class="px-2.5 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded text-xs font-semibold inline-flex items-center gap-1">
                                            <i data-lucide="package-plus" class="w-3.5 h-3.5"></i> Restock
                                        </button>
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

<!-- Modal: Restock Medicine -->
<div id="modal-restock" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-sky-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="package-plus" class="w-5 h-5"></i> Restock Medication
            </h3>
            <button onclick="closeModal('modal-restock')" class="text-sky-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=restock_medicine" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="id" id="restock-med-id" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Medicine</label>
                <input type="text" id="restock-med-name" disabled class="w-full px-3 py-2 bg-slate-100 border rounded-lg text-sm text-slate-700">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Add Quantity (Units) *</label>
                <input type="number" name="quantity" required min="1" value="50" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier / Restock Invoice Notes</label>
                <input type="text" name="notes" placeholder="e.g. Monthly replenishment batch" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-restock')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-sm font-semibold shadow-sm">Confirm Restock</button>
            </div>
        </form>
    </div>
</div>

<script>
function promptRestock(id, name, currentStock) {
    document.getElementById('restock-med-id').value = id;
    document.getElementById('restock-med-name').value = name + ' (Current: ' + currentStock + ')';
    openModal('modal-restock');
}
</script>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
