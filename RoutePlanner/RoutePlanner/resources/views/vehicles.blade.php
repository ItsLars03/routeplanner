<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Vehicles</title>
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
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-icon {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-[#F9FAFB] flex h-screen overflow-hidden text-slate-800">
    @include('partials.sidebar', ['active' => 'vehicles'])

    <div class="flex-1 flex flex-col min-w-0 relative overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 z-20 shadow-sm">
            <div class="flex items-center gap-4 md:gap-6">
                <div class="bg-orange-50 px-4 py-2 rounded-2xl border border-orange-100 flex items-center gap-3">
                    <i class="fas fa-truck text-orange-600 text-sm"></i>
                    <span class="text-sm font-bold text-slate-700">Vehicle Management</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button id="reloadBtn" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-orange-500/30 transition-all active:scale-95">
                    Ververs lijst
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            <div id="alert" class="hidden mb-4 rounded-xl px-4 py-3 text-sm font-semibold"></div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="xl:col-span-1 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                        <h2 id="formTitle" class="text-xl font-black">Nieuwe vehicle</h2>
                        <p class="text-xs text-slate-400 mt-1">Voeg een voertuig toe of bewerk een bestaande.</p>
                    </div>

                    <form id="vehicleForm" class="p-6 space-y-4">
                        <input type="hidden" id="vehicleId">

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Naam</label>
                            <input id="name" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Brand</label>
                                <input id="brand" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Model</label>
                                <input id="model" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Fuel type</label>
                                <input id="fuel_type" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Kenteken</label>
                                <input id="license_plate" type="text" required class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all">
                            </div>
                        </div>

                        <div id="apiTokenSection" class="hidden">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Arduino API Token</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input id="apiToken" type="text" readonly class="flex-1 bg-slate-50 border-2 border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono outline-none text-slate-600 cursor-not-allowed">
                                <button type="button" id="copyApiTokenBtn" class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95" title="Kopieer token">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button type="button" id="refreshApiTokenBtn" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2.5 rounded-xl text-xs font-bold transition-all active:scale-95" title="Genereer nieuw token">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Plak dit token in je Arduino-code. Klik op vernieuwen voor een nieuw token.</p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="submit" id="submitBtn" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white rounded-xl px-4 py-2.5 text-sm font-bold shadow-lg shadow-orange-500/20 transition-all active:scale-95">
                                Vehicle opslaan
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
                            <h2 class="text-xl font-black">Vehicles overzicht</h2>
                            <p id="countLabel" class="text-xs text-slate-400 mt-1">0 voertuigen geladen</p>
                        </div>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input id="searchInput" type="text" placeholder="Zoek op naam, brand of kenteken" class="bg-slate-100 text-slate-800 rounded-xl pl-9 pr-3 py-2 text-sm outline-none border-2 border-transparent focus:border-orange-500/20 w-64 max-w-full">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-xs">
                                <tr>
                                    <th class="text-left px-4 py-3">ID</th>
                                    <th class="text-left px-4 py-3">Naam</th>
                                    <th class="text-left px-4 py-3">Brand</th>
                                    <th class="text-left px-4 py-3">Model</th>
                                    <th class="text-left px-4 py-3">Fuel</th>
                                    <th class="text-left px-4 py-3">Kenteken</th>
                                    <th class="text-left px-4 py-3">Acties</th>
                                </tr>
                            </thead>
                            <tbody id="vehiclesBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        const apiUrl = '/api/vehicles';
        const damageApiUrl = '/api/damage-reports';
        const APK_WARNING_DAYS = 60;

        const elements = {
            alert: document.getElementById('alert'),
            form: document.getElementById('vehicleForm'),
            formTitle: document.getElementById('formTitle'),
            submitBtn: document.getElementById('submitBtn'),
            cancelEditBtn: document.getElementById('cancelEditBtn'),
            reloadBtn: document.getElementById('reloadBtn'),
            searchInput: document.getElementById('searchInput'),
            vehiclesBody: document.getElementById('vehiclesBody'),
            countLabel: document.getElementById('countLabel'),
            vehicleId: document.getElementById('vehicleId'),
            name: document.getElementById('name'),
            brand: document.getElementById('brand'),
            model: document.getElementById('model'),
            fuel_type: document.getElementById('fuel_type'),
            license_plate: document.getElementById('license_plate'),
        };

        let vehicles = [];
        let apkStatusByVehicleId = {};
        let damageReports = [];
        let damageByVehicleId = {};
        let rdwDataByVehicleId = {};
        let rdwLoadingByVehicleId = {};
        let rdwErrorByVehicleId = {};
        let selectedDamageVehicleId = null;
        let selectedRdwVehicleId = null;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDate(value) {
            const raw = String(value ?? '').trim();
            if (!raw) {
                return '-';
            }

            if (/^\d{8}$/.test(raw)) {
                const year = raw.slice(0, 4);
                const month = raw.slice(4, 6);
                const day = raw.slice(6, 8);
                return `${day}-${month}-${year}`;
            }

            const date = new Date(raw);
            if (!Number.isNaN(date.getTime())) {
                return date.toLocaleDateString('nl-NL');
            }

            return raw;
        }

        function parseRdwDate(value) {
            const raw = String(value ?? '').trim();
            if (!/^\d{8}$/.test(raw)) {
                return null;
            }

            const year = Number(raw.slice(0, 4));
            const month = Number(raw.slice(4, 6));
            const day = Number(raw.slice(6, 8));
            const date = new Date(year, month - 1, day);

            if (Number.isNaN(date.getTime())) {
                return null;
            }

            return date;
        }

        function daysUntil(date) {
            if (!date) {
                return null;
            }

            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const target = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const diffMs = target.getTime() - today.getTime();

            return Math.floor(diffMs / (1000 * 60 * 60 * 24));
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
                name: elements.name.value.trim(),
                brand: elements.brand.value.trim(),
                model: elements.model.value.trim(),
                fuel_type: elements.fuel_type.value.trim(),
                license_plate: elements.license_plate.value.trim(),
            };
        }

        function resetForm() {
            elements.form.reset();
            elements.vehicleId.value = '';
            elements.formTitle.textContent = 'Nieuwe vehicle';
            elements.submitBtn.textContent = 'Vehicle opslaan';
            elements.cancelEditBtn.classList.add('hidden');
            
            // Hide API token section for new vehicles
            const apiTokenSection = document.getElementById('apiTokenSection');
            apiTokenSection.classList.add('hidden');
        }

        function fillForm(vehicle) {
            elements.vehicleId.value = vehicle.id;
            elements.name.value = vehicle.name || '';
            elements.brand.value = vehicle.brand || '';
            elements.model.value = vehicle.model || '';
            elements.fuel_type.value = vehicle.fuel_type || '';
            elements.license_plate.value = vehicle.license_plate || '';
            
            // Show API token section if vehicle has a token
            const apiTokenSection = document.getElementById('apiTokenSection');
            const apiTokenInput = document.getElementById('apiToken');
            if (vehicle.api_token) {
                apiTokenInput.value = vehicle.api_token;
                apiTokenSection.classList.remove('hidden');
            } else {
                apiTokenSection.classList.add('hidden');
            }
            
            elements.formTitle.textContent = 'Vehicle bewerken';
            elements.submitBtn.textContent = 'Vehicle updaten';
            elements.cancelEditBtn.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function renderRows() {
            const search = elements.searchInput.value.trim().toLowerCase();
            const filtered = vehicles.filter((vehicle) => {
                return (
                    String(vehicle.name || '').toLowerCase().includes(search) ||
                    String(vehicle.brand || '').toLowerCase().includes(search) ||
                    String(vehicle.model || '').toLowerCase().includes(search) ||
                    String(vehicle.license_plate || '').toLowerCase().includes(search)
                );
            });

            elements.countLabel.textContent = filtered.length + ' voertuigen geladen';

            if (!filtered.length) {
                elements.vehiclesBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 font-medium">Nog geen voertuigen gevonden.</td>
                    </tr>
                `;
                return;
            }

            elements.vehiclesBody.innerHTML = filtered.map((vehicle) => {
                const damageEntry = damageByVehicleId[vehicle.id];
                const showDamageDetails = selectedDamageVehicleId === vehicle.id;
                const showRdwDetails = selectedRdwVehicleId === vehicle.id;
                const mainRow = `
                <tr class="hover:bg-orange-50/40 transition ${showDamageDetails || showRdwDetails ? 'bg-orange-50/30' : ''}">
                    <td class="px-4 py-3 font-bold text-slate-500">${escapeHtml(vehicle.id)}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">${escapeHtml(vehicle.name || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(vehicle.brand || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(vehicle.model || '-')}</td>
                    <td class="px-4 py-3">${escapeHtml(vehicle.fuel_type || '-')}</td>
                    <td class="px-4 py-3 font-black text-slate-700">
                        ${escapeHtml(vehicle.license_plate || '-')}
                        ${damageEntry
                            ? `<div class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-bold ${damageEntry.open > 0 ? 'text-amber-700 bg-amber-50 border-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-100'} border rounded-md px-2 py-1">`
                                + '<i class="fa-solid fa-car-burst"></i>'
                                + `${escapeHtml(String(damageEntry.total))} schade${damageEntry.total === 1 ? '' : 's'} (${escapeHtml(String(damageEntry.open))} open)`
                                + '</div>'
                            : ''}
                        ${apkStatusByVehicleId[vehicle.id]
                            ? `<div class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-bold text-red-700 bg-red-50 border border-red-100 rounded-md px-2 py-1">`
                                + '<i class="fa-solid fa-triangle-exclamation"></i>'
                                + `${apkStatusByVehicleId[vehicle.id].isExpired ? 'APK verlopen' : 'APK verloopt bijna'} (${escapeHtml(apkStatusByVehicleId[vehicle.id].label)})`
                                + '</div>'
                            : ''}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button data-action="rdw" data-id="${escapeHtml(vehicle.id)}" class="px-3 py-1.5 rounded-lg ${showRdwDetails ? 'bg-blue-600 text-white' : 'bg-blue-50 hover:bg-blue-100 text-blue-700'} text-xs font-bold transition">RDW</button>
                            <button data-action="damages" data-id="${escapeHtml(vehicle.id)}" class="px-3 py-1.5 rounded-lg ${selectedDamageVehicleId === vehicle.id ? 'bg-orange-500 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'} text-xs font-bold transition">Schades</button>
                            <button data-action="edit" data-id="${vehicle.id}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Bewerk</button>
                            <button data-action="delete" data-id="${vehicle.id}" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition">Verwijder</button>
                        </div>
                    </td>
                </tr>
                `;

                if (!showDamageDetails && !showRdwDetails) {
                    return mainRow;
                }

                const detailBlocks = [];
                if (showRdwDetails) {
                    detailBlocks.push(getInlineRdwHtml(vehicle));
                }
                if (showDamageDetails) {
                    detailBlocks.push(getInlineDamageHtml(vehicle.id));
                }

                return mainRow + `
                <tr class="bg-orange-50/30">
                    <td colspan="7" class="px-4 py-3">
                        <div class="space-y-3">${detailBlocks.join('')}</div>
                    </td>
                </tr>
                `;
            }).join('');

            const warningCount = Object.values(apkStatusByVehicleId).filter((item) => item && !item.isExpired).length;
            const expiredCount = Object.values(apkStatusByVehicleId).filter((item) => item && item.isExpired).length;

            if (warningCount > 0 || expiredCount > 0) {
                const parts = [];
                if (expiredCount > 0) {
                    parts.push(`${expiredCount} verlopen`);
                }
                if (warningCount > 0) {
                    parts.push(`${warningCount} bijna verlopen`);
                }
                elements.countLabel.textContent += ` • APK: ${parts.join(', ')}`;
            }

            const vehiclesWithDamage = Object.keys(damageByVehicleId).length;
            if (vehiclesWithDamage > 0) {
                elements.countLabel.textContent += ` • Schade: ${vehiclesWithDamage} voertuigen`;
            }
        }

        function getInlineDamageHtml(vehicleId) {
            const entry = damageByVehicleId[vehicleId];

            if (!entry) {
                return '<div class="rounded-xl bg-white border border-slate-200 px-4 py-3 text-sm text-slate-600 font-medium">Geen schades geregistreerd voor dit voertuig.</div>';
            }

            const items = entry.items.slice(0, 10).map((item) => `
                <div class="rounded-xl bg-white border border-slate-200 px-3 py-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-bold text-slate-800">${escapeHtml(item.damage_type || 'Onbekend')}</span>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full ${item.status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">${escapeHtml(item.status || 'open')}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Datum: ${escapeHtml(item.reported_date || '-')} • Ernst: ${escapeHtml(item.severity || '-')}</p>
                    <p class="text-xs text-slate-600 mt-1">${escapeHtml(item.description || '-')}</p>
                </div>
            `).join('');

            return `
                <div class="space-y-2">
                    <div class="text-xs font-black uppercase tracking-wide text-slate-700">Schades voor dit voertuig</div>
                    <div class="text-xs text-slate-600 font-semibold">${entry.total} totaal, ${entry.open} open.</div>
                    <div class="space-y-2">${items}</div>
                </div>
            `;
        }

        function getInlineRdwHtml(vehicle) {
            const vehicleId = vehicle.id;

            if (rdwLoadingByVehicleId[vehicleId]) {
                return '<div class="rounded-xl bg-white border border-blue-200 px-4 py-3 text-sm text-blue-700 font-semibold">RDW gegevens laden...</div>';
            }

            if (rdwErrorByVehicleId[vehicleId]) {
                return `<div class="rounded-xl bg-white border border-red-200 px-4 py-3 text-sm text-red-700 font-semibold">${escapeHtml(rdwErrorByVehicleId[vehicleId])}</div>`;
            }

            const data = rdwDataByVehicleId[vehicleId];
            if (!data) {
                return '<div class="rounded-xl bg-white border border-slate-200 px-4 py-3 text-sm text-slate-600 font-medium">Nog geen RDW data geladen.</div>';
            }

            return `
                <div class="space-y-2">
                    <div class="text-xs font-black uppercase tracking-wide text-slate-700">RDW gegevens</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-2">
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Kenteken:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.kenteken || vehicle.license_plate || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Merk:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.merk || vehicle.brand || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Model:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.handelsbenaming || vehicle.model || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Voertuigsoort:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.voertuigsoort || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Kleur:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.eerste_kleur || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">APK vervalt:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(formatDate(data.vervaldatum_apk || '-'))}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">1e toelating:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(formatDate(data.datum_eerste_toelating || '-'))}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Tenaamstelling:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(formatDate(data.datum_tenaamstelling || '-'))}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Brandstof:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.brandstof_omschrijving || data.brandstof || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Massa ledig:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.massa_ledig_voertuig || '-')} kg</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Massa rijklaar:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.massa_rijklaar || '-')} kg</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Catalogusprijs:</span> <span class="font-bold text-slate-800 text-sm">EUR ${escapeHtml(data.catalogusprijs || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">WAM verzekerd:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.wam_verzekerd || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Aantal deuren:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.aantal_deuren || '-')}</span></div>
                        <div class="rounded-xl bg-white border border-slate-200 px-3 py-2"><span class="text-slate-500 text-xs">Aantal zitplaatsen:</span> <span class="font-bold text-slate-800 text-sm">${escapeHtml(data.aantal_zitplaatsen || '-')}</span></div>
                    </div>

                    <details class="bg-slate-900 rounded-xl overflow-hidden">
                        <summary class="cursor-pointer select-none px-3 py-2 text-xs font-bold text-slate-200 hover:bg-slate-800">Ruwe gegevens (JSON) tonen</summary>
                        <pre class="text-slate-100 text-xs p-3 overflow-auto max-h-72 border-t border-slate-700">${escapeHtml(JSON.stringify(data, null, 2))}</pre>
                    </details>
                </div>
            `;
        }

        async function loadRdwForVehicle(vehicle) {
            if (!vehicle || !vehicle.license_plate) {
                return;
            }

            const vehicleId = vehicle.id;
            rdwLoadingByVehicleId[vehicleId] = true;
            rdwErrorByVehicleId[vehicleId] = null;
            renderRows();

            try {
                const response = await fetch(`/api/vehicles-info/${encodeURIComponent(vehicle.license_plate)}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'RDW gegevens ophalen is mislukt.');
                }

                rdwDataByVehicleId[vehicleId] = data;
                rdwErrorByVehicleId[vehicleId] = null;
            } catch (error) {
                rdwErrorByVehicleId[vehicleId] = error.message;
            } finally {
                rdwLoadingByVehicleId[vehicleId] = false;
                renderRows();
            }
        }

        function buildDamageMap(reports) {
            const map = {};

            reports.forEach((item) => {
                if (!item.vehicle_id) {
                    return;
                }

                if (!map[item.vehicle_id]) {
                    map[item.vehicle_id] = {
                        total: 0,
                        open: 0,
                        items: [],
                    };
                }

                map[item.vehicle_id].total += 1;
                if (item.status !== 'resolved') {
                    map[item.vehicle_id].open += 1;
                }
                map[item.vehicle_id].items.push(item);
            });

            Object.values(map).forEach((entry) => {
                entry.items.sort((a, b) => String(b.reported_date || '').localeCompare(String(a.reported_date || '')));
            });

            return map;
        }

        async function loadDamageReports() {
            try {
                const response = await fetch(damageApiUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    return;
                }

                damageReports = await response.json();
                damageByVehicleId = buildDamageMap(damageReports);
                renderRows();
            } catch (error) {
                // Keep vehicles usable if damage endpoint fails.
            }
        }

        async function loadApkWarnings() {
            const statusMap = {};

            const requests = vehicles.map(async (vehicle) => {
                if (!vehicle.license_plate) {
                    return;
                }

                try {
                    const response = await fetch(`/api/vehicles-info/${encodeURIComponent(vehicle.license_plate)}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    const apkDate = parseRdwDate(data.vervaldatum_apk);
                    const days = daysUntil(apkDate);

                    if (days === null) {
                        return;
                    }

                    if (days < 0) {
                        statusMap[vehicle.id] = {
                            isExpired: true,
                            label: formatDate(data.vervaldatum_apk),
                        };
                        return;
                    }

                    if (days <= APK_WARNING_DAYS) {
                        statusMap[vehicle.id] = {
                            isExpired: false,
                            label: `${days} dagen`,
                        };
                    }
                } catch (error) {
                    // Ignore per-vehicle lookup errors so the list remains usable.
                }
            });

            await Promise.allSettled(requests);
            apkStatusByVehicleId = statusMap;
            renderRows();
        }

        async function loadVehicles() {
            clearAlert();
            try {
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error('Laden van vehicles mislukt.');
                }
                vehicles = await response.json();
                apkStatusByVehicleId = {};
                damageByVehicleId = {};
                rdwDataByVehicleId = {};
                rdwLoadingByVehicleId = {};
                rdwErrorByVehicleId = {};
                selectedDamageVehicleId = null;
                selectedRdwVehicleId = null;
                renderRows();
                loadApkWarnings();
                loadDamageReports();
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function saveVehicle(event) {
            event.preventDefault();
            clearAlert();

            const id = elements.vehicleId.value;
            const payload = getFormData();
            const isUpdate = Boolean(id);
            const url = isUpdate ? `${apiUrl}/${id}` : apiUrl;
            const method = isUpdate ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    const message = errorData.message || 'Opslaan is mislukt.';
                    throw new Error(message);
                }

                resetForm();
                await loadVehicles();
                showAlert(isUpdate ? 'Vehicle succesvol bijgewerkt.' : 'Vehicle succesvol toegevoegd.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        async function deleteVehicle(id) {
            const confirmed = window.confirm('Weet je zeker dat je dit voertuig wilt verwijderen?');
            if (!confirmed) {
                return;
            }

            clearAlert();
            try {
                const response = await fetch(`${apiUrl}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Verwijderen is mislukt.');
                }

                if (String(elements.vehicleId.value) === String(id)) {
                    resetForm();
                }

                await loadVehicles();
                showAlert('Vehicle verwijderd.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        }

        elements.form.addEventListener('submit', saveVehicle);
        elements.cancelEditBtn.addEventListener('click', resetForm);
        elements.reloadBtn.addEventListener('click', loadVehicles);
        elements.searchInput.addEventListener('input', renderRows);

        // Copy API token to clipboard
        const copyApiTokenBtn = document.getElementById('copyApiTokenBtn');
        if (copyApiTokenBtn) {
            copyApiTokenBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const apiTokenInput = document.getElementById('apiToken');
                console.log('Copy button clicked', { apiTokenInput, value: apiTokenInput?.value });
                
                if (!apiTokenInput || !apiTokenInput.value) {
                    showAlert('Geen token beschikbaar om te kopiëren', 'error');
                    return;
                }

                const tokenValue = apiTokenInput.value.trim();
                
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    // Modern Clipboard API
                    navigator.clipboard.writeText(tokenValue)
                        .then(() => {
                            console.log('Token copied successfully');
                            const originalHtml = copyApiTokenBtn.innerHTML;
                            copyApiTokenBtn.innerHTML = '<i class="fas fa-check"></i>';
                            copyApiTokenBtn.classList.remove('bg-orange-500', 'hover:bg-orange-600');
                            copyApiTokenBtn.classList.add('bg-emerald-500', 'hover:bg-emerald-600', 'text-white');
                            
                            showAlert('✓ Token gekopieerd!');
                            
                            setTimeout(() => {
                                copyApiTokenBtn.innerHTML = originalHtml;
                                copyApiTokenBtn.classList.add('bg-orange-500', 'hover:bg-orange-600');
                                copyApiTokenBtn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                            }, 2000);
                        })
                        .catch((err) => {
                            console.error('Clipboard copy failed:', err);
                            showAlert('Kopiëren mislukt. Probeer handmatig te kopiëren.', 'error');
                        });
                } else {
                    // Fallback for older browsers
                    try {
                        const textarea = document.createElement('textarea');
                        textarea.value = tokenValue;
                        textarea.style.position = 'fixed';
                        textarea.style.left = '-999999px';
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        
                        console.log('Token copied via fallback method');
                        showAlert('✓ Token gekopieerd!');
                    } catch (err) {
                        console.error('Fallback copy failed:', err);
                        showAlert('Kopiëren mislukt', 'error');
                    }
                }
            });
        }

        // Refresh API token
        const refreshApiTokenBtn = document.getElementById('refreshApiTokenBtn');
        if (refreshApiTokenBtn) {
            refreshApiTokenBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const vehicleId = elements.vehicleId.value;
                console.log('Refresh button clicked', { vehicleId });
                
                if (!vehicleId) {
                    showAlert('Selecteer eerst een voertuig om te bewerken', 'error');
                    return;
                }

                try {
                    refreshApiTokenBtn.disabled = true;
                    const syncIcon = refreshApiTokenBtn.querySelector('i');
                    syncIcon.classList.add('animate-spin-icon');
                    refreshApiTokenBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    
                    console.log(`Requesting new token for vehicle ${vehicleId}`);
                    
                    const response = await fetch(`${apiUrl}/${vehicleId}/refresh-token`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();
                    console.log('Token refresh response:', data);
                    
                    const apiTokenInput = document.getElementById('apiToken');
                    if (apiTokenInput) {
                        apiTokenInput.value = data.api_token;
                        showAlert('✓ Nieuw token aangemaakt!');
                    }
                } catch (error) {
                    console.error('Token refresh error:', error);
                    showAlert(`Fout bij vernieuwen: ${error.message}`, 'error');
                } finally {
                    refreshApiTokenBtn.disabled = false;
                    const syncIcon = refreshApiTokenBtn.querySelector('i');
                    syncIcon.classList.remove('animate-spin-icon');
                    refreshApiTokenBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        }

        elements.vehiclesBody.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }

            const id = button.dataset.id;
            const vehicle = vehicles.find((item) => String(item.id) === String(id));
            if (!vehicle) {
                return;
            }

            if (button.dataset.action === 'edit') {
                fillForm(vehicle);
            }

            if (button.dataset.action === 'damages') {
                selectedDamageVehicleId = selectedDamageVehicleId === vehicle.id ? null : vehicle.id;
                renderRows();
            }

            if (button.dataset.action === 'rdw') {
                const willOpen = selectedRdwVehicleId !== vehicle.id;
                selectedRdwVehicleId = willOpen ? vehicle.id : null;

                if (willOpen && !rdwDataByVehicleId[vehicle.id] && !rdwLoadingByVehicleId[vehicle.id]) {
                    loadRdwForVehicle(vehicle);
                }

                renderRows();
            }

            if (button.dataset.action === 'delete') {
                deleteVehicle(id);
            }
        });

        loadVehicles();
    </script>
</body>
</html>
