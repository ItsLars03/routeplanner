<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Drivers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0) 100%);
            border-left: 4px solid #f59e0b;
            color: white;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #f59e0b; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#F9FAFB] flex h-screen overflow-hidden text-slate-800">
    @include('partials.sidebar', ['active' => 'drivers'])

    <div class="flex-1 flex flex-col min-w-0 relative overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 z-20 shadow-sm">
            <div class="flex items-center gap-4 md:gap-6">
                <div class="bg-orange-50 px-4 py-2 rounded-2xl border border-orange-100 flex items-center gap-3">
                    <i class="fas fa-id-card text-orange-600 text-sm"></i>
                    <span class="text-sm font-bold text-slate-700">Driver Management</span>
                </div>
            </div>

            <button id="reloadBtn" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-orange-500/30 transition-all active:scale-95">
                Ververs lijst
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            <div id="alert" class="hidden mb-4 rounded-xl px-4 py-3 text-sm font-semibold"></div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="xl:col-span-1 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                        <h2 id="formTitle" class="text-xl font-black">Nieuwe driver</h2>
                        <p class="text-xs text-slate-400 mt-1">Voeg een chauffeur toe of bewerk een bestaande.</p>
                    </div>

                    <form id="driverForm" class="p-6 space-y-4">
                        <input type="hidden" id="driverId">

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Voornaam</label>
                            <input id="firstname" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Achternaam</label>
                            <input id="lastname" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">E-mail</label>
                            <input id="email" type="email" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="submit" id="submitBtn" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white rounded-xl px-4 py-2.5 text-sm font-bold shadow-lg shadow-orange-500/20 transition-all active:scale-95">
                                Driver opslaan
                            </button>
                            <button type="button" id="cancelEditBtn" class="hidden flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold transition-all">
                                Bewerken annuleren
                            </button>
                        </div>
                    </form>
                </section>

                <section class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black">Drivers overzicht</h2>
                            <p id="countLabel" class="text-xs text-slate-400 mt-1">0 drivers geladen</p>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input id="searchInput" type="text" placeholder="Zoek op naam of e-mail" class="bg-slate-100 text-slate-800 rounded-xl pl-9 pr-3 py-2 text-sm outline-none border-2 border-transparent focus:border-orange-500/20 w-64 max-w-full">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-xs">
                                <tr>
                                    <th class="text-left px-4 py-3">ID</th>
                                    <th class="text-left px-4 py-3">Voornaam</th>
                                    <th class="text-left px-4 py-3">Achternaam</th>
                                    <th class="text-left px-4 py-3">E-mail</th>
                                    <th class="text-left px-4 py-3">Acties</th>
                                </tr>
                            </thead>
                            <tbody id="driversBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        const apiUrl = '/api/drivers';

        const elements = {
            alert: document.getElementById('alert'),
            form: document.getElementById('driverForm'),
            formTitle: document.getElementById('formTitle'),
            submitBtn: document.getElementById('submitBtn'),
            cancelEditBtn: document.getElementById('cancelEditBtn'),
            reloadBtn: document.getElementById('reloadBtn'),
            searchInput: document.getElementById('searchInput'),
            driversBody: document.getElementById('driversBody'),
            countLabel: document.getElementById('countLabel'),
            driverId: document.getElementById('driverId'),
            firstname: document.getElementById('firstname'),
            lastname: document.getElementById('lastname'),
            email: document.getElementById('email'),
        };

        let drivers = [];

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function showAlert(message, type = 'success') {
            elements.alert.className = 'mb-4 rounded-xl px-4 py-3 text-sm font-semibold';
            if (type === 'error') {
                elements.alert.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-100');
            } else {
                elements.alert.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-100');
            }
            elements.alert.textContent = message;
            elements.alert.classList.remove('hidden');
        }

        function clearAlert() {
            elements.alert.classList.add('hidden');
        }

        function getFormData() {
            return {
                firstname: elements.firstname.value.trim(),
                lastname: elements.lastname.value.trim(),
                email: elements.email.value.trim(),
            };
        }

        function resetForm() {
            elements.form.reset();
            elements.driverId.value = '';
            elements.formTitle.textContent = 'Nieuwe driver';
            elements.submitBtn.textContent = 'Driver opslaan';
            elements.cancelEditBtn.classList.add('hidden');
        }

        function fillForm(driver) {
            elements.driverId.value = driver.id;
            elements.firstname.value = driver.firstname || '';
            elements.lastname.value = driver.lastname || '';
            elements.email.value = driver.email || '';
            elements.formTitle.textContent = 'Driver bewerken';
            elements.submitBtn.textContent = 'Driver updaten';
            elements.cancelEditBtn.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function renderRows() {
            const search = elements.searchInput.value.trim().toLowerCase();
            const filtered = drivers.filter((driver) => {
                return (
                    String(driver.firstname || '').toLowerCase().includes(search) ||
                    String(driver.lastname || '').toLowerCase().includes(search) ||
                    String(driver.email || '').toLowerCase().includes(search)
                );
            });

            elements.countLabel.textContent = filtered.length + ' drivers geladen';

            if (!filtered.length) {
                elements.driversBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 font-medium">Nog geen drivers gevonden.</td>
                    </tr>
                `;
                return;
            }

            elements.driversBody.innerHTML = filtered.map((driver) => `
                <tr class="hover:bg-orange-50/40 transition">
                    <td class="px-4 py-3 font-bold text-slate-500">${escapeHtml(driver.id)}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">${escapeHtml(driver.firstname || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(driver.lastname || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(driver.email || '-')}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button data-action="edit" data-id="${escapeHtml(driver.id)}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Bewerk</button>
                            <button data-action="delete" data-id="${escapeHtml(driver.id)}" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition">Verwijder</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        async function loadDrivers() {
            clearAlert();
            try {
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error('Laden van drivers mislukt.');
                }
                drivers = await response.json();
                renderRows();
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function saveDriver(event) {
            event.preventDefault();
            clearAlert();

            const id = elements.driverId.value;
            const payload = getFormData();
            const isUpdate = Boolean(id);
            const url = isUpdate ? `${apiUrl}/${id}` : apiUrl;
            const method = isUpdate ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Opslaan is mislukt.');
                }

                resetForm();
                await loadDrivers();
                showAlert(isUpdate ? 'Driver succesvol bijgewerkt.' : 'Driver succesvol toegevoegd.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function deleteDriver(id) {
            const confirmed = window.confirm('Weet je zeker dat je deze driver wilt verwijderen?');
            if (!confirmed) {
                return;
            }

            clearAlert();
            try {
                const response = await fetch(`${apiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Verwijderen is mislukt.');
                }

                if (String(elements.driverId.value) === String(id)) {
                    resetForm();
                }

                await loadDrivers();
                showAlert('Driver verwijderd.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        elements.form.addEventListener('submit', saveDriver);
        elements.cancelEditBtn.addEventListener('click', resetForm);
        elements.reloadBtn.addEventListener('click', loadDrivers);
        elements.searchInput.addEventListener('input', renderRows);

        elements.driversBody.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }

            const id = button.dataset.id;
            const driver = drivers.find((item) => String(item.id) === String(id));
            if (!driver) {
                return;
            }

            if (button.dataset.action === 'edit') {
                fillForm(driver);
            }

            if (button.dataset.action === 'delete') {
                deleteDriver(id);
            }
        });

        loadDrivers();
    </script>
</body>
</html>
