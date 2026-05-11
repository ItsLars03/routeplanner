<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Schades</title>
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
    @include('partials.sidebar', ['active' => 'damages'])

    <div class="flex-1 flex flex-col min-w-0 relative overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 z-20 shadow-sm">
            <div class="flex items-center gap-4 md:gap-6">
                <div class="bg-orange-50 px-4 py-2 rounded-2xl border border-orange-100 flex items-center gap-3">
                    <i class="fas fa-triangle-exclamation text-orange-600 text-sm"></i>
                    <span class="text-sm font-bold text-slate-700">Schade Rapportage</span>
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
                        <h2 id="formTitle" class="text-xl font-black">Nieuw schaderapport</h2>
                        <p class="text-xs text-slate-400 mt-1">Registreer nieuwe schade of update bestaande rapporten.</p>
                    </div>

                    <form id="damageForm" class="p-6 space-y-4">
                        <input type="hidden" id="damageId">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Kenteken</label>
                                <select id="vehicle_id" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                                    <option value="">Selecteer kenteken</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Chauffeur e-mail</label>
                                <select id="driver_id" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                                    <option value="">Selecteer chauffeur</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Meldingsdatum</label>
                                <input id="reported_date" type="date" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Locatie</label>
                                <input id="location" type="text" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Type schade</label>
                                <input id="damage_type" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Ernst</label>
                                <select id="severity" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Beschrijving</label>
                            <textarea id="description" rows="4" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Geschatte kosten (EUR)</label>
                                <input id="estimated_cost" type="number" min="0" step="0.01" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Status</label>
                                <select id="status" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In behandeling</option>
                                    <option value="resolved">Afgerond</option>
                                    <option value="rejected">Afgewezen</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="submit" id="submitBtn" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white rounded-xl px-4 py-2.5 text-sm font-bold shadow-lg shadow-orange-500/20 transition-all active:scale-95">
                                Rapport opslaan
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
                            <h2 class="text-xl font-black">Schade rapporten</h2>
                            <p id="countLabel" class="text-xs text-slate-400 mt-1">0 rapporten geladen</p>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input id="searchInput" type="text" placeholder="Zoek op type, locatie of status" class="bg-slate-100 text-slate-800 rounded-xl pl-9 pr-3 py-2 text-sm outline-none border-2 border-transparent focus:border-orange-500/20 w-64 max-w-full">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-xs">
                                <tr>
                                    <th class="text-left px-4 py-3">ID</th>
                                    <th class="text-left px-4 py-3">Datum</th>
                                    <th class="text-left px-4 py-3">Kenteken</th>
                                    <th class="text-left px-4 py-3">Chauffeur</th>
                                    <th class="text-left px-4 py-3">Type</th>
                                    <th class="text-left px-4 py-3">Ernst</th>
                                    <th class="text-left px-4 py-3">Status</th>
                                    <th class="text-left px-4 py-3">Locatie</th>
                                    <th class="text-left px-4 py-3">Acties</th>
                                </tr>
                            </thead>
                            <tbody id="damageBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        const apiUrl = '/api/damage-reports';

        const elements = {
            alert: document.getElementById('alert'),
            form: document.getElementById('damageForm'),
            formTitle: document.getElementById('formTitle'),
            submitBtn: document.getElementById('submitBtn'),
            cancelEditBtn: document.getElementById('cancelEditBtn'),
            reloadBtn: document.getElementById('reloadBtn'),
            searchInput: document.getElementById('searchInput'),
            countLabel: document.getElementById('countLabel'),
            damageBody: document.getElementById('damageBody'),
            damageId: document.getElementById('damageId'),
            vehicle_id: document.getElementById('vehicle_id'),
            driver_id: document.getElementById('driver_id'),
            reported_date: document.getElementById('reported_date'),
            location: document.getElementById('location'),
            damage_type: document.getElementById('damage_type'),
            severity: document.getElementById('severity'),
            description: document.getElementById('description'),
            estimated_cost: document.getElementById('estimated_cost'),
            status: document.getElementById('status'),
        };

        let damageReports = [];
        let vehicles = [];
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

        function resetForm() {
            elements.form.reset();
            elements.damageId.value = '';
            elements.formTitle.textContent = 'Nieuw schaderapport';
            elements.submitBtn.textContent = 'Rapport opslaan';
            elements.cancelEditBtn.classList.add('hidden');
        }

        function renderVehicleOptions() {
            const currentValue = String(elements.vehicle_id.value || '');
            const options = ['<option value="">Selecteer kenteken</option>']
                .concat(vehicles.map((vehicle) => `<option value="${escapeHtml(vehicle.id)}">${escapeHtml(vehicle.license_plate || 'Onbekend kenteken')}</option>`));

            elements.vehicle_id.innerHTML = options.join('');

            if (currentValue) {
                elements.vehicle_id.value = currentValue;
            }
        }

        function renderDriverOptions() {
            const currentValue = String(elements.driver_id.value || '');
            const options = ['<option value="">Selecteer chauffeur</option>']
                .concat(drivers.map((driver) => `<option value="${escapeHtml(driver.id)}">${escapeHtml(driver.email || 'Geen e-mail')}</option>`));

            elements.driver_id.innerHTML = options.join('');

            if (currentValue) {
                elements.driver_id.value = currentValue;
            }
        }

        async function loadLookupData() {
            try {
                const [vehicleResponse, driverResponse] = await Promise.all([
                    fetch('/api/vehicles', { headers: { 'Accept': 'application/json' } }),
                    fetch('/api/drivers', { headers: { 'Accept': 'application/json' } }),
                ]);

                vehicles = vehicleResponse.ok ? await vehicleResponse.json() : [];
                drivers = driverResponse.ok ? await driverResponse.json() : [];

                renderVehicleOptions();
                renderDriverOptions();
            } catch (error) {
                vehicles = [];
                drivers = [];
                renderVehicleOptions();
                renderDriverOptions();
            }
        }

        function getFormData() {
            return {
                vehicle_id: elements.vehicle_id.value ? Number(elements.vehicle_id.value) : null,
                driver_id: elements.driver_id.value ? Number(elements.driver_id.value) : null,
                reported_date: elements.reported_date.value,
                location: elements.location.value.trim() || null,
                damage_type: elements.damage_type.value.trim(),
                severity: elements.severity.value,
                description: elements.description.value.trim(),
                estimated_cost: elements.estimated_cost.value ? Number(elements.estimated_cost.value) : null,
                status: elements.status.value,
            };
        }

        function fillForm(item) {
            elements.damageId.value = item.id;
            elements.vehicle_id.value = item.vehicle_id ? String(item.vehicle_id) : '';
            elements.driver_id.value = item.driver_id ? String(item.driver_id) : '';
            elements.reported_date.value = item.reported_date || '';
            elements.location.value = item.location || '';
            elements.damage_type.value = item.damage_type || '';
            elements.severity.value = item.severity || 'low';
            elements.description.value = item.description || '';
            elements.estimated_cost.value = item.estimated_cost || '';
            elements.status.value = item.status || 'open';
            elements.formTitle.textContent = 'Schaderapport bewerken';
            elements.submitBtn.textContent = 'Rapport updaten';
            elements.cancelEditBtn.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function renderRows() {
            const search = elements.searchInput.value.trim().toLowerCase();
            const filtered = damageReports.filter((item) => {
                return (
                    String(item.damage_type || '').toLowerCase().includes(search) ||
                    String(item.location || '').toLowerCase().includes(search) ||
                    String(item.status || '').toLowerCase().includes(search) ||
                    String(item.severity || '').toLowerCase().includes(search) ||
                    String(item.vehicle?.license_plate || '').toLowerCase().includes(search) ||
                    String(item.driver?.email || '').toLowerCase().includes(search)
                );
            });

            elements.countLabel.textContent = filtered.length + ' rapporten geladen';

            if (!filtered.length) {
                elements.damageBody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-slate-400 font-medium">Nog geen schaderapporten gevonden.</td></tr>';
                return;
            }

            elements.damageBody.innerHTML = filtered.map((item) => `
                <tr class="hover:bg-orange-50/40 transition">
                    <td class="px-4 py-3 font-bold text-slate-500">${escapeHtml(item.id)}</td>
                    <td class="px-4 py-3">${escapeHtml(item.reported_date || '-')}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">${escapeHtml(item.vehicle?.license_plate || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(item.driver?.email || '-')}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">${escapeHtml(item.damage_type || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(item.severity || '-')}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold ${item.status === 'resolved' ? 'text-emerald-700' : 'text-slate-600'}">${escapeHtml(item.status || '-')}</span>
                            <button data-action="toggle-resolved" data-id="${escapeHtml(item.id)}" class="px-2 py-1 rounded-md text-[11px] font-bold ${item.status === 'resolved' ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'} transition">
                                ${item.status === 'resolved' ? 'Zet open' : 'Markeer opgelost'}
                            </button>
                        </div>
                    </td>
                    <td class="px-4 py-3">${escapeHtml(item.location || '-')}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button data-action="edit" data-id="${escapeHtml(item.id)}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Bewerk</button>
                            <button data-action="delete" data-id="${escapeHtml(item.id)}" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition">Verwijder</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        async function toggleResolved(item) {
            const newStatus = item.status === 'resolved' ? 'open' : 'resolved';

            clearAlert();
            try {
                const response = await fetch(`${apiUrl}/${item.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Status wijzigen is mislukt.');
                }

                await loadReports();
                showAlert(newStatus === 'resolved' ? 'Schade afgevinkt als opgelost.' : 'Schade teruggezet naar open.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function loadReports() {
            clearAlert();
            try {
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error('Laden van schaderapporten mislukt.');
                }
                damageReports = await response.json();
                renderRows();
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function saveReport(event) {
            event.preventDefault();
            clearAlert();

            const id = elements.damageId.value;
            const payload = getFormData();
            const isUpdate = Boolean(id);

            try {
                const response = await fetch(isUpdate ? `${apiUrl}/${id}` : apiUrl, {
                    method: isUpdate ? 'PUT' : 'POST',
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
                await loadReports();
                showAlert(isUpdate ? 'Schaderapport bijgewerkt.' : 'Schaderapport toegevoegd.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function deleteReport(id) {
            const confirmed = window.confirm('Weet je zeker dat je dit schaderapport wilt verwijderen?');
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

                if (String(elements.damageId.value) === String(id)) {
                    resetForm();
                }

                await loadReports();
                showAlert('Schaderapport verwijderd.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        elements.form.addEventListener('submit', saveReport);
        elements.cancelEditBtn.addEventListener('click', resetForm);
        elements.reloadBtn.addEventListener('click', loadReports);
        elements.searchInput.addEventListener('input', renderRows);

        elements.damageBody.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }

            const id = button.dataset.id;
            const item = damageReports.find((report) => String(report.id) === String(id));
            if (!item) {
                return;
            }

            if (button.dataset.action === 'edit') {
                fillForm(item);
            }

            if (button.dataset.action === 'toggle-resolved') {
                toggleResolved(item);
            }

            if (button.dataset.action === 'delete') {
                deleteReport(id);
            }
        });

        loadLookupData().finally(loadReports);
    </script>
</body>
</html>
