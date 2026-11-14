<?php
$pageTitle = "Billing & Financial Invoicing";
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/partials/navbar.php';

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$bills = BillingController::getAll($search, $status);
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Hospital Billing & Invoicing System</h1>
                <p class="text-xs text-slate-500 mt-0.5">Itemized invoicing, consultations, lab fees, medication charges, and payment receipting</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="index.php?action=export_csv&type=revenue" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="w-3.5 h-3.5 text-slate-500"></i> Export Revenue CSV
                </a>
                <button onclick="openModal('modal-create-bill')" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i> Generate New Invoice
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="card-clean p-4 mb-6">
            <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="hidden" name="page" value="billing">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search invoice number, patient code or name..." class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">All Payment Statuses</option>
                        <option value="Paid" <?= $status === 'Paid' ? 'selected' : '' ?>>Paid Full</option>
                        <option value="Partial" <?= $status === 'Partial' ? 'selected' : '' ?>>Partially Paid</option>
                        <option value="Unpaid" <?= $status === 'Unpaid' ? 'selected' : '' ?>>Unpaid / Outstanding</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || !empty($status)): ?>
                        <a href="index.php?page=billing" class="px-3 py-2 border border-slate-300 hover:bg-slate-100 rounded-lg text-xs text-slate-600 flex items-center justify-center">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Invoices Table -->
        <div class="card-clean overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Patient Details</th>
                            <th class="py-3 px-4">Bill Date</th>
                            <th class="py-3 px-4">Charges Breakdown</th>
                            <th class="py-3 px-4">Grand Total</th>
                            <th class="py-3 px-4">Paid Amount</th>
                            <th class="py-3 px-4">Balance Due</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($bills)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-400">No invoices found matching criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bills as $b): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-sky-700 whitespace-nowrap">
                                        <?= $b['invoice_number'] ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="index.php?page=patient_detail&id=<?= $b['patient_id'] ?>" class="font-bold text-slate-900 hover:text-sky-600">
                                            <?= htmlspecialchars($b['patient_name']) ?>
                                        </a>
                                        <div class="text-[11px] text-slate-400"><?= $b['patient_code'] ?> | <?= htmlspecialchars($b['patient_phone']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                        <?= date('M d, Y', strtotime($b['bill_date'])) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-[11px] text-slate-600 flex flex-wrap gap-1">
                                            <?php if ($b['total_consultation'] > 0): ?>
                                                <span class="bg-slate-100 px-1.5 py-0.5 rounded">Cons: $<?= number_format($b['total_consultation'], 0) ?></span>
                                            <?php endif; ?>
                                            <?php if ($b['total_lab'] > 0): ?>
                                                <span class="bg-amber-50 text-amber-800 px-1.5 py-0.5 rounded">Lab: $<?= number_format($b['total_lab'], 0) ?></span>
                                            <?php endif; ?>
                                            <?php if ($b['total_pharmacy'] > 0): ?>
                                                <span class="bg-emerald-50 text-emerald-800 px-1.5 py-0.5 rounded">Pharm: $<?= number_format($b['total_pharmacy'], 0) ?></span>
                                            <?php endif; ?>
                                            <?php if ($b['total_admission'] > 0): ?>
                                                <span class="bg-purple-50 text-purple-800 px-1.5 py-0.5 rounded">Bed: $<?= number_format($b['total_admission'], 0) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900">$<?= number_format($b['grand_total'], 2) ?></td>
                                    <td class="py-3 px-4 font-semibold text-emerald-600">$<?= number_format($b['paid_amount'], 2) ?></td>
                                    <td class="py-3 px-4 font-bold <?= $b['balance_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>">
                                        $<?= number_format($b['balance_amount'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php 
                                        $stClass = match($b['payment_status']) {
                                            'Paid'    => 'badge-success',
                                            'Partial' => 'badge-warning',
                                            default   => 'badge-danger'
                                        };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= $b['payment_status'] ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($b['balance_amount'] > 0): ?>
                                                <button onclick="promptPayment(<?= $b['id'] ?>, '<?= $b['invoice_number'] ?>', <?= $b['balance_amount'] ?>)" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold flex items-center gap-1 shadow-sm">
                                                    <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i> Pay
                                                </button>
                                            <?php endif; ?>
                                            <a href="index.php?page=print_invoice&id=<?= $b['id'] ?>" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 rounded text-xs font-semibold text-slate-700 flex items-center gap-1" title="Print Invoice / Receipt">
                                                <i data-lucide="printer" class="w-3.5 h-3.5 text-sky-600"></i> Receipt
                                            </a>
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

<!-- Modal: Record Payment (SRS Section 3.8 Receive Payment) -->
<div id="modal-record-payment" class="modal-backdrop">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-emerald-700 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5"></i> Receive Patient Payment
            </h3>
            <button onclick="closeModal('modal-record-payment')" class="text-emerald-200 hover:text-white">&times;</button>
        </div>
        <form action="index.php?action=record_payment" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="bill_id" id="pay-bill-id" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Number</label>
                <input type="text" id="pay-invoice-num" disabled class="w-full px-3 py-2 bg-slate-100 border rounded-lg text-sm font-mono text-slate-800">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Amount ($) *</label>
                <input type="number" step="0.01" name="amount" id="pay-amount" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-slate-900">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method *</label>
                <select name="payment_method" required class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="Cash">Cash</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="UPI">UPI / Digital QR</option>
                    <option value="Insurance">Health Insurance</option>
                    <option value="Bank Transfer">Bank Wire Transfer</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref / Cheque No / Slip</label>
                <input type="text" name="transaction_reference" placeholder="e.g. AUTH-VISA-88992 or Cash Counter 1" class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="pt-4 border-t flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-record-payment')" class="px-4 py-2 border rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">Process Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function promptPayment(billId, invoiceNo, balance) {
    document.getElementById('pay-bill-id').value = billId;
    document.getElementById('pay-invoice-num').value = invoiceNo + ' (Balance Due: $' + balance.toFixed(2) + ')';
    document.getElementById('pay-amount').value = balance.toFixed(2);
    openModal('modal-record-payment');
}
</script>

<?php require_once __DIR__ . '/partials/modals.php'; ?>
<?php require_once __DIR__ . '/partials/footer.php'; ?>
