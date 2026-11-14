<?php
$billId = (int)($_GET['id'] ?? 0);
$bill = BillingController::getById($billId);

if (!$bill) {
    die("Invoice not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($bill['invoice_number']) ?> - <?= HOSPITAL_NAME ?></title>
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
                &larr; Back to System
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5">
                Print Official Receipt
            </button>
        </div>

        <!-- Header / Hospital Letterhead -->
        <div class="flex justify-between items-start border-b-2 border-sky-600 pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-black text-sky-800 tracking-tight flex items-center gap-2">
                    <span><?= HOSPITAL_NAME ?></span>
                </h1>
                <p class="text-xs text-slate-500 mt-1"><?= HOSPITAL_TAGLINE ?></p>
                <p class="text-xs text-slate-600 mt-0.5"><?= HOSPITAL_ADDRESS ?></p>
                <p class="text-xs text-slate-600"><?= HOSPITAL_PHONE ?> | <?= HOSPITAL_EMAIL ?></p>
                <p class="text-[11px] text-slate-400 mt-1">Tax ID: <?= HOSPITAL_TAX_NO ?></p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-sky-50 text-sky-700 border border-sky-200 rounded font-bold text-xs uppercase tracking-wider mb-2">
                    Official Tax Invoice
                </span>
                <div class="text-lg font-mono font-black text-slate-900"><?= htmlspecialchars($bill['invoice_number']) ?></div>
                <div class="text-xs text-slate-500 mt-1">Date: <strong><?= date('M d, Y', strtotime($bill['bill_date'])) ?></strong></div>
                <div class="text-xs text-slate-500">Due: <strong><?= date('M d, Y', strtotime($bill['due_date'])) ?></strong></div>
                <div class="mt-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold <?= $bill['payment_status'] === 'Paid' ? 'bg-emerald-100 text-emerald-800' : ($bill['payment_status'] === 'Partial' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                        Status: <?= strtoupper($bill['payment_status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Patient Billed To Details -->
        <div class="grid grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-200 text-xs mb-6">
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Billed Patient</div>
                <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($bill['patient_name']) ?></div>
                <div class="text-slate-600 mt-0.5">Patient ID: <strong class="font-mono"><?= $bill['patient_code'] ?></strong></div>
                <div class="text-slate-600">Gender / DOB: <?= $bill['gender'] ?>, <?= $bill['dob'] ?></div>
                <div class="text-slate-600">Phone: <?= htmlspecialchars($bill['patient_phone']) ?></div>
            </div>
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Billing Details & Notes</div>
                <div class="text-slate-700"><?= htmlspecialchars($bill['notes'] ?: 'Hospital care and clinical services') ?></div>
                <div class="text-slate-500 mt-2">Address: <?= htmlspecialchars($bill['address'] ?: 'On record') ?></div>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="w-full text-left text-xs mb-6">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase border-y border-slate-200">
                <tr>
                    <th class="py-2.5 px-3">#</th>
                    <th class="py-2.5 px-3">Service Description</th>
                    <th class="py-2.5 px-3">Category</th>
                    <th class="py-2.5 px-3 text-center">Qty</th>
                    <th class="py-2.5 px-3 text-right">Unit Price</th>
                    <th class="py-2.5 px-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php 
                $seq = 1;
                if (!empty($bill['items'])):
                    foreach ($bill['items'] as $item):
                ?>
                    <tr>
                        <td class="py-2.5 px-3 text-slate-400"><?= $seq++ ?></td>
                        <td class="py-2.5 px-3 font-semibold text-slate-800"><?= htmlspecialchars($item['description']) ?></td>
                        <td class="py-2.5 px-3 text-slate-500"><?= htmlspecialchars($item['item_type']) ?></td>
                        <td class="py-2.5 px-3 text-center"><?= $item['quantity'] ?></td>
                        <td class="py-2.5 px-3 text-right">$<?= number_format($item['unit_price'], 2) ?></td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">$<?= number_format($item['total_price'], 2) ?></td>
                    </tr>
                <?php 
                    endforeach;
                else: 
                ?>
                    <?php if ($bill['total_consultation'] > 0): ?>
                        <tr>
                            <td class="py-2.5 px-3 text-slate-400"><?= $seq++ ?></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">Specialist Clinical Consultation</td>
                            <td class="py-2.5 px-3 text-slate-500">Consultation</td>
                            <td class="py-2.5 px-3 text-center">1</td>
                            <td class="py-2.5 px-3 text-right">$<?= number_format($bill['total_consultation'], 2) ?></td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900">$<?= number_format($bill['total_consultation'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($bill['total_lab'] > 0): ?>
                        <tr>
                            <td class="py-2.5 px-3 text-slate-400"><?= $seq++ ?></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">Diagnostic Laboratory Investigations</td>
                            <td class="py-2.5 px-3 text-slate-500">Laboratory</td>
                            <td class="py-2.5 px-3 text-center">1</td>
                            <td class="py-2.5 px-3 text-right">$<?= number_format($bill['total_lab'], 2) ?></td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900">$<?= number_format($bill['total_lab'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($bill['total_pharmacy'] > 0): ?>
                        <tr>
                            <td class="py-2.5 px-3 text-slate-400"><?= $seq++ ?></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">Dispensed Pharmacy Medications</td>
                            <td class="py-2.5 px-3 text-slate-500">Pharmacy</td>
                            <td class="py-2.5 px-3 text-center">1</td>
                            <td class="py-2.5 px-3 text-right">$<?= number_format($bill['total_pharmacy'], 2) ?></td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900">$<?= number_format($bill['total_pharmacy'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($bill['total_admission'] > 0): ?>
                        <tr>
                            <td class="py-2.5 px-3 text-slate-400"><?= $seq++ ?></td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">Inpatient Ward Bed & Nursing Charges</td>
                            <td class="py-2.5 px-3 text-slate-500">Admission</td>
                            <td class="py-2.5 px-3 text-center">1</td>
                            <td class="py-2.5 px-3 text-right">$<?= number_format($bill['total_admission'], 2) ?></td>
                            <td class="py-2.5 px-3 text-right font-bold text-slate-900">$<?= number_format($bill['total_admission'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Totals Calculation Box -->
        <div class="flex justify-end mb-6">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-800">$<?= number_format($bill['subtotal'], 2) ?></span>
                </div>
                <?php if ($bill['discount'] > 0): ?>
                    <div class="flex justify-between text-emerald-600">
                        <span>Discount:</span>
                        <span class="font-bold">-$<?= number_format($bill['discount'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between text-slate-600">
                    <span>Healthcare Tax (5%):</span>
                    <span class="font-bold text-slate-800">$<?= number_format($bill['tax'], 2) ?></span>
                </div>
                <div class="flex justify-between text-base font-black text-slate-900 border-t-2 pt-2">
                    <span>Grand Total:</span>
                    <span>$<?= number_format($bill['grand_total'], 2) ?></span>
                </div>
                <div class="flex justify-between text-emerald-700 font-bold border-t pt-1">
                    <span>Total Amount Paid:</span>
                    <span>$<?= number_format($bill['paid_amount'], 2) ?></span>
                </div>
                <div class="flex justify-between text-rose-700 font-black border-t pt-1 text-sm">
                    <span>Balance Payable:</span>
                    <span>$<?= number_format($bill['balance_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Payments Settled Log -->
        <?php if (!empty($bill['payments'])): ?>
            <div class="mb-8 border-t pt-4">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Recorded Payment Settlements</div>
                <div class="space-y-1 text-xs">
                    <?php foreach ($bill['payments'] as $pay): ?>
                        <div class="p-2 bg-slate-50 rounded border flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-sky-700"><?= $pay['payment_code'] ?></span> | 
                                <span class="font-semibold text-slate-800"><?= $pay['payment_method'] ?></span> 
                                (Ref: <?= htmlspecialchars($pay['transaction_reference']) ?>)
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-emerald-700">$<?= number_format($pay['amount'], 2) ?></span>
                                <span class="text-slate-400 text-[10px] ml-2"><?= date('M d, Y H:i', strtotime($pay['payment_date'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Signatures & Disclaimer Footer -->
        <div class="grid grid-cols-2 gap-8 border-t pt-8 mt-8 text-xs text-slate-500">
            <div>
                <p class="text-[11px] leading-relaxed">
                    Thank you for choosing <?= HOSPITAL_NAME ?>. For inquiries regarding this invoice, please reach out to our billing desk at <?= HOSPITAL_PHONE ?>.
                </p>
            </div>
            <div class="text-right flex flex-col items-end">
                <div class="w-48 border-b border-slate-400 mb-2 pb-6 text-center text-slate-400 italic">
                    Hospital Cashier Stamp
                </div>
                <div class="font-bold text-slate-800">Authorized Accounts Signatory</div>
                <div class="text-[10px] text-slate-400"><?= HOSPITAL_NAME ?> Accounts Dept.</div>
            </div>
        </div>
    </div>
</body>
</html>
