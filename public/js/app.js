/**
 * Hospital Management System (HMS)
 * Frontend Interactions & Client Operations
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide Icons if available
    if (window.lucide) {
        lucide.createIcons();
    }

    // Modal Handlers
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    };

    // Close on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(m => {
                m.classList.remove('active');
            });
            document.body.style.overflow = 'auto';
        }
    });

    // Toast Notifications
    window.showToast = function(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const bgColors = {
            success: 'bg-emerald-600 text-white',
            error: 'bg-rose-600 text-white',
            info: 'bg-sky-600 text-white',
            warning: 'bg-amber-500 text-white'
        };

        toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg shadow-xl text-sm font-medium transition-all duration-300 transform translate-y-2 opacity-0 ${bgColors[type] || bgColors.info}`;
        toast.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-auto opacity-75 hover:opacity-100">&times;</button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    // Quick role switcher button listener
    document.querySelectorAll('[data-switch-role]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const role = btn.getAttribute('data-switch-role');
            try {
                const res = await fetch(`api.php?action=switch_role&role=${encodeURIComponent(role)}`);
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Could not switch role: ' + (data.message || 'Error'));
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // Prescription Row Adder in EMR Form
    window.addPrescriptionRow = function() {
        const container = document.getElementById('prescription-rows');
        if (!container) return;

        const index = container.children.length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-5 gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200 text-sm relative items-end';
        row.innerHTML = `
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Medicine Name & Strength</label>
                <input type="text" name="prescriptions[${index}][medicine_name]" required placeholder="e.g. Amoxicillin 500mg" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-sky-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dosage</label>
                <input type="text" name="prescriptions[${index}][dosage]" value="1 tablet" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-sky-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Frequency</label>
                <input type="text" name="prescriptions[${index}][frequency]" value="Twice daily" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-sky-500 outline-none">
            </div>
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Duration</label>
                    <input type="text" name="prescriptions[${index}][duration]" value="5 days" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-sky-500 outline-none">
                </div>
                <button type="button" onclick="this.closest('.grid').remove()" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded mt-4">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        if (window.lucide) lucide.createIcons();
    };

    // Client-side quick table search filter
    window.filterTable = function(inputId, tableId) {
        const input = document.getElementById(inputId);
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            let visible = false;
            const cells = rows[i].getElementsByTagName('td');
            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const text = cells[j].textContent || cells[j].innerText;
                    if (text.toLowerCase().indexOf(filter) > -1) {
                        visible = true;
                        break;
                    }
                }
            }
            rows[i].style.display = visible ? '' : 'none';
        }
    };
});
