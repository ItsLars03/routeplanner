<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Orange Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Manrope', sans-serif; }
        #map { height: 100%; width: 100%; z-index: 1; filter: saturate(1.2); }
        .sidebar-item-active { 
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0) 100%); 
            border-left: 4px solid #f59e0b; 
            color: white; 
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #f59e0b; border-radius: 10px; }

        .stop-hover-tooltip {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
            padding: 14px 16px;
            color: #0f172a;
        }

        .stop-hover-tooltip.leaflet-tooltip-top:before {
            border-top-color: rgba(255, 255, 255, 0.98);
        }

        .stop-hover-tooltip strong {
            font-weight: 800;
            color: #0f172a;
        }

        #planner-panel.collapsed #toggle-planner-collapse {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-[#F9FAFB] flex min-h-screen overflow-y-auto text-slate-800 md:h-screen md:overflow-hidden">
    @include('partials.sidebar', ['active' => 'routes'])

    <div class="flex-1 flex flex-col min-w-0 relative md:min-h-0">
        
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4">
                <svg class="animate-spin h-12 w-12 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-bold text-slate-700">Data aan het opslaan...</p>
            </div>
        </div>
        
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 z-20 shadow-sm">
            <div class="flex items-center gap-6">
                <div class="bg-orange-50 px-4 py-2 rounded-2xl border border-orange-100 flex items-center gap-3">
                    <i class="fas fa-location-arrow text-orange-600 text-sm"></i>
                    <span class="text-sm font-bold text-slate-700">Harlingen</span>
                </div>
                <label for="planner-date" class="text-xs font-bold uppercase tracking-wide text-slate-500">Planningsdatum</label>
                <div class="relative">
                    <input id="planner-date" type="text" placeholder="dd-mm-yyyy" readonly class="rounded-xl border border-slate-200 px-3 py-2 pr-9 text-sm font-semibold text-slate-700 bg-white transition-all shadow-sm hover:shadow-md cursor-pointer">
                    <i class="fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-orange-500 pointer-events-none"></i>
                    
                    <div id="date-picker-popup" class="hidden absolute top-full left-0 mt-2 z-50 bg-slate-900 border border-slate-700 rounded-2xl p-4 shadow-2xl" style="width: 320px;">
                        <div class="flex items-center justify-between mb-4">
                            <button id="date-prev-month" class="w-10 h-10 text-slate-400 hover:text-orange-500 flex items-center justify-center transition text-xl">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="text-center flex-1">
                                <p id="date-month-year" class="text-sm font-bold text-white uppercase tracking-wide">mei 2026</p>
                            </div>
                            <button id="date-next-month" class="w-10 h-10 text-slate-400 hover:text-orange-500 flex items-center justify-center transition text-xl">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-7 gap-1 mb-4">
                            <div class="text-center text-[11px] font-bold text-slate-500 py-2">Ma</div>
                            <div class="text-center text-[11px] font-bold text-slate-500 py-2">Di</div>
                            <div class="text-center text-[11px] font-bold text-slate-500 py-2">Wo</div>
                            <div class="text-center text-[11px] font-bold text-slate-500 py-2">Do</div>
                            <div class="text-center text-[11px] font-bold text-slate-500 py-2">Vr</div>
                            <div class="text-center text-[11px] font-bold text-slate-600 py-2">Za</div>
                            <div class="text-center text-[11px] font-bold text-slate-600 py-2">Zo</div>
                        </div>
                        
                        <div id="date-days-grid" class="grid grid-cols-7 gap-1"></div>
                        
                        <div class="mt-4 pt-4 border-t border-slate-700 flex items-center justify-between">
                            <button id="date-clear" class="text-xs font-bold text-slate-400 hover:text-slate-200 transition uppercase tracking-wide">Wissen</button>
                            <div class="flex gap-2">
                                <button id="date-today" class="px-3 py-1 text-xs font-bold text-slate-400 hover:text-slate-200 transition">Vandaag</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button id="refresh-planner" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold transition-all">
                    Verversen
                </button>
                <button id="auto-refresh-toggle" class="ml-2 bg-white/80 border border-slate-200 text-slate-700 px-3 py-2 rounded-xl text-sm font-bold transition-all" title="Automatisch verversen">
                    Auto: <span id="auto-refresh-indicator" class="ml-2 text-xs font-mono text-slate-500">uit</span>
                </button>
                <button id="initialize-slots" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-orange-500/30 transition-all active:scale-95">
                    Maak tijdsloten
                </button>
            </div>
        </header>

        <div class="flex-1 flex relative min-h-0 flex-col md:flex-row">
            <div id="map" class="z-10 h-[46vh] md:h-auto md:flex-1"></div>

            <div id="map-assign-panel" class="hidden absolute top-4 right-4 z-30 w-80 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-2xl md:right-4">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Kaartselectie</p>
                <p id="map-assign-count" class="mt-1 text-sm font-black text-slate-800">0 stops geselecteerd</p>

                <div id="selected-stop-list" class="mt-3 flex flex-wrap gap-2"></div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <button id="map-assign-edit" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Bewerk</button>
                    <button id="map-assign-delete" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50">Verwijder</button>
                    <button id="map-assign-clear" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Leeg</button>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Verplaats naar route</p>

                    <div class="mt-3 space-y-2">
                        <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Voertuig</label>
                        <select id="map-assign-vehicle" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- kies voertuig --</option>
                        </select>
                    </div>

                    <div class="mt-3 space-y-2">
                        <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Tijdslot</label>
                        <select id="map-assign-slot" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- kies tijdslot --</option>
                        </select>
                    </div>

                    <button id="map-assign-submit" class="mt-4 w-full rounded-xl bg-orange-500 px-3 py-2 text-sm font-bold text-white transition hover:bg-orange-600">Verplaats</button>
                </div>
            </div>

            <!-- Edit Stop Modal -->
            <div id="stop-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white rounded-3xl p-8 shadow-2xl max-w-md w-full mx-4">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black text-slate-900">Stop bewerken</h3>
                        <button onclick="closeEditStopModal()" class="w-8 h-8 flex items-center justify-center hover:bg-slate-100 rounded-lg transition">
                            <i class="fas fa-times text-slate-400"></i>
                        </button>
                    </div>

                    <form id="stop-edit-form" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Naam</label>
                            <input id="edit-stop-name" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Adres</label>
                            <input id="edit-stop-address" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Latitude</label>
                                <input id="edit-stop-lat" type="number" step="0.00001" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Longitude</label>
                                <input id="edit-stop-lng" type="number" step="0.00001" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Datum</label>
                            <input id="edit-stop-date" type="date" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <div class="pt-4 border-t border-slate-200">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">Tijdslot</p>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" id="slot-type-fixed" name="slotType" value="fixed" class="w-4 h-4" checked>
                                    <span class="text-sm font-medium text-slate-700">Vast tijdslot</span>
                                </label>
                                <select id="edit-stop-slot" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">-- Selecteer slot --</option>
                                </select>
                            </div>

                            <div class="space-y-2 mt-3">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" id="slot-type-custom" name="slotType" value="custom" class="w-4 h-4">
                                    <span class="text-sm font-medium text-slate-700">Custom tijdslot</span>
                                </label>
                                <div id="custom-time-inputs" class="hidden grid grid-cols-2 gap-3 pl-7">
                                    <div>
                                        <label class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1 block">Van</label>
                                        <input id="edit-stop-custom-start" type="time" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1 block">Tot</label>
                                        <input id="edit-stop-custom-end" type="time" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3 pt-4">
                            <button id="stop-edit-cancel" type="button" onclick="closeEditStopModal()" class="flex-1 rounded-xl bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Annuleren</button>
                            <button id="stop-edit-save" type="button" class="flex-1 rounded-xl bg-orange-500 px-3 py-2 text-sm font-bold text-white transition hover:bg-orange-600">Opslaan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="planner-panel" class="w-full md:w-[32rem] bg-white/95 backdrop-blur-xl flex flex-col shadow-2xl z-20 m-3 md:m-6 rounded-[2.5rem] overflow-hidden border border-white/20 ring-1 ring-black/5 md:max-h-[calc(100vh-7rem)] min-h-0 transition-all duration-300">
                <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white relative overflow-hidden flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black mb-1">Planner</h2>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                            <p id="planner-status" class="text-slate-400 text-xs font-medium uppercase tracking-wider">Laden van data...</p>
                        </div>
                    </div>
                    <button id="toggle-planner-collapse" class="w-9 h-9 flex items-center justify-center hover:bg-white/20 rounded-lg transition text-white flex-shrink-0">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <div id="planner-container" class="p-5 flex flex-col h-full min-h-0 gap-4 overflow-y-auto transition-all duration-300">
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-200">
                            <p class="text-[11px] uppercase tracking-wide font-bold text-slate-500">Voertuigen</p>
                            <p id="count-vehicles" class="text-xl font-black text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-200">
                            <p class="text-[11px] uppercase tracking-wide font-bold text-slate-500">Drivers bezet</p>
                            <p id="count-assigned" class="text-xl font-black text-slate-900">0</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 border border-slate-200">
                            <p class="text-[11px] uppercase tracking-wide font-bold text-slate-500">Stops actief</p>
                            <p id="count-stops" class="text-xl font-black text-slate-900">0</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button id="route-filter-active" class="rounded-full bg-orange-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600">Met routes</button>
                        <button id="route-filter-all" class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200">Alle voertuigen</button>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Ingeladen stops</p>
                                <p id="unassigned-stops-count" class="text-sm font-bold text-slate-800">0 niet toegewezen</p>
                            </div>
                            <button id="show-unassigned-on-map" class="rounded-full bg-white px-3 py-2 text-[11px] font-bold text-slate-700 border border-slate-200 transition hover:bg-slate-100">
                                Toon op kaart
                            </button>
                            <button id="clear-slot-focus" class="hidden rounded-full bg-white px-3 py-2 text-[11px] font-bold text-slate-700 border border-slate-200 transition hover:bg-slate-100">
                                Toon alle tijdsloten
                            </button>
                        </div>
                        <div id="unassigned-stops-list" class="flex flex-wrap gap-2"></div>
                    </div>

                    <div id="planner-list" class="flex-1 overflow-y-auto space-y-2 pr-1">
                    </div>
                </div>
            </div>

            <div id="slot-stops-popup" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-950/55 backdrop-blur-sm px-4">
                <div class="w-full max-w-2xl rounded-[2rem] bg-white shadow-2xl border border-slate-200 overflow-hidden">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Tijdslot stops</p>
                            <h3 id="slot-stops-popup-title" class="mt-1 text-xl font-black text-slate-900">Stops</h3>
                            <p id="slot-stops-popup-meta" class="mt-1 text-sm text-slate-500"></p>
                        </div>
                        <button id="close-slot-stops-popup" class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-900" type="button" aria-label="Sluiten">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
                        <div id="slot-stops-popup-list" class="space-y-3"></div>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-10 left-10 z-20 hidden md:flex flex-col gap-2">
                 <button onclick="map.zoomIn()" class="w-12 h-12 bg-white text-slate-800 rounded-2xl shadow-2xl flex items-center justify-center hover:bg-orange-500 hover:text-white transition-all active:scale-90 border border-slate-100">
                    <i class="fas fa-plus"></i>
                 </button>
                 <button onclick="map.zoomOut()" class="w-12 h-12 bg-white text-slate-800 rounded-2xl shadow-2xl flex items-center justify-center hover:bg-orange-500 hover:text-white transition-all active:scale-90 border border-slate-100">
                    <i class="fas fa-minus"></i>
                 </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map', { zoomControl: false }).setView([53.1736, 5.4217], 11);
        var stopMarkers = [];
        var slotRouteLayer = null;
        var slotRouteRequestToken = 0;
        var markerByStopId = {};
        var branchMarker = null;
        const branchSetting = @json($branchSetting ?? null);
        var state = {
            date: null,
            data: null,
            selectedMapStopIds: new Set(),
            vehicleView: 'active',
            activeSlotId: null,
            mapFocusMode: 'all',
            mapFocusDriverId: null,
        };
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '©OpenStreetMap'
        }).addTo(map);

        const plannerDateInput = document.getElementById('planner-date');
        const plannerStatus = document.getElementById('planner-status');
        const plannerList = document.getElementById('planner-list');
        const countVehicles = document.getElementById('count-vehicles');
        const countAssigned = document.getElementById('count-assigned');
        const countStops = document.getElementById('count-stops');
        const initializeButton = document.getElementById('initialize-slots');
        const refreshButton = document.getElementById('refresh-planner');
        const autoRefreshToggle = document.getElementById('auto-refresh-toggle');
        const autoRefreshIndicator = document.getElementById('auto-refresh-indicator');
        const routeFilterActive = document.getElementById('route-filter-active');
        const routeFilterAll = document.getElementById('route-filter-all');
        const showUnassignedOnMap = document.getElementById('show-unassigned-on-map');
        const clearSlotFocus = document.getElementById('clear-slot-focus');
        const unassignedStopsCount = document.getElementById('unassigned-stops-count');
        const unassignedStopsList = document.getElementById('unassigned-stops-list');
        const slotStopsPopup = document.getElementById('slot-stops-popup');
        const slotStopsPopupTitle = document.getElementById('slot-stops-popup-title');
        const slotStopsPopupMeta = document.getElementById('slot-stops-popup-meta');
        const slotStopsPopupList = document.getElementById('slot-stops-popup-list');
        const closeSlotStopsPopupButton = document.getElementById('close-slot-stops-popup');
        const APK_WARNING_DAYS = 60;
        let apkStatusByVehicleId = {};
        let damageByVehicleId = {};
        const mapAssignPanel = document.getElementById('map-assign-panel');
        const mapAssignCount = document.getElementById('map-assign-count');
        const selectedStopList = document.getElementById('selected-stop-list');
        const mapAssignVehicle = document.getElementById('map-assign-vehicle');
        const mapAssignSlot = document.getElementById('map-assign-slot');
        const mapAssignSubmit = document.getElementById('map-assign-submit');
        const mapAssignEdit = document.getElementById('map-assign-edit');
        const mapAssignDelete = document.getElementById('map-assign-delete');
        const mapAssignClear = document.getElementById('map-assign-clear');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Set initial date from PHP or use today
        const initialDate = '{{ $initialDate ?? '' }}';
        plannerDateInput.value = initialDate || new Date().toISOString().slice(0, 10);
        state.date = plannerDateInput.value;

        function setLoading(isLoading) {
            state.isLoading = isLoading;
            loadingOverlay.classList.toggle('hidden', !isLoading);
            mapAssignSubmit.disabled = isLoading;
            mapAssignEdit.disabled = isLoading || state.selectedMapStopIds.size !== 1;
            mapAssignDelete.disabled = isLoading || state.selectedMapStopIds.size === 0;
            initializeButton.disabled = isLoading;
            refreshButton.disabled = isLoading;
        }

        function formatDateForUrl(dateString) {
            // Convert YYYY-MM-DD to DD-MM-YYYY
            const [year, month, day] = dateString.split('-');
            return `${day}-${month}-${year}`;
        }

        plannerDateInput.addEventListener('change', () => {
            state.date = plannerDateInput.value;
            const formattedDate = formatDateForUrl(state.date);
            window.history.pushState(null, '', `/route/${formattedDate}`);
            loadPlanner();
        });

        refreshButton.addEventListener('click', () => loadPlanner());
        
        // Auto-refresh / polling support
        let autoRefreshEnabled = localStorage.getItem('route_auto_refresh') === '1';
        const autoRefreshIntervalMs = 30000; // 30s
        let autoRefreshTimer = null;

        function updateAutoRefreshUi() {
            if (autoRefreshEnabled) {
                autoRefreshToggle.classList.add('bg-emerald-50');
                autoRefreshIndicator.textContent = 'aan';
                autoRefreshIndicator.classList.remove('text-slate-500');
                autoRefreshIndicator.classList.add('text-emerald-600');
            } else {
                autoRefreshToggle.classList.remove('bg-emerald-50');
                autoRefreshIndicator.textContent = 'uit';
                autoRefreshIndicator.classList.remove('text-emerald-600');
                autoRefreshIndicator.classList.add('text-slate-500');
            }
        }

        function startAutoRefresh() {
            if (autoRefreshTimer) return;
            autoRefreshTimer = setInterval(async () => {
                if (state.isLoading) return; // avoid overlapping loads
                try {
                    await loadPlanner();
                } catch (e) {
                    // ignore network errors and continue polling
                }
            }, autoRefreshIntervalMs);
        }

        function stopAutoRefresh() {
            if (!autoRefreshTimer) return;
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }

        autoRefreshToggle.addEventListener('click', () => {
            autoRefreshEnabled = !autoRefreshEnabled;
            localStorage.setItem('route_auto_refresh', autoRefreshEnabled ? '1' : '0');
            updateAutoRefreshUi();
            if (autoRefreshEnabled) startAutoRefresh(); else stopAutoRefresh();
        });

        // initialize auto-refresh on page load
        updateAutoRefreshUi();
        if (autoRefreshEnabled) startAutoRefresh();
        routeFilterActive.addEventListener('click', () => {
            state.vehicleView = 'active';
            updateRouteFilterButtons();
            renderPlanner();
        });

        routeFilterAll.addEventListener('click', () => {
            state.vehicleView = 'all';
            updateRouteFilterButtons();
            renderPlanner();
        });

        showUnassignedOnMap.addEventListener('click', () => {
            if (state.mapFocusMode === 'unassigned') {
                state.mapFocusMode = 'all';
                clearSlotRoute();
                updateMapFocusButton();
                renderMapStops();
                return;
            }

            clearSlotRoute();
            setMapFocusUnassigned();
        });

        clearSlotFocus.addEventListener('click', () => {
            state.activeSlotId = null;
            state.mapFocusMode = 'all';
            state.mapFocusDriverId = null;
            clearSlotRoute();
            updateSlotFocusButton();
            updateMapFocusButton();
            renderPlanner();
            renderMapStops();
        });

        initializeButton.addEventListener('click', async () => {
            await fetch('/api/route-planner/initialize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ date: state.date })
            });

            loadPlanner();
        });

        mapAssignVehicle.addEventListener('change', renderMapAssignSlotOptions);

        mapAssignSubmit.addEventListener('click', moveSelectedStopsToRoute);

        mapAssignEdit.addEventListener('click', editSelectedStop);

        mapAssignDelete.addEventListener('click', deleteSelectedStops);

        mapAssignClear.addEventListener('click', () => {
            clearSelectedMapStops();
            updateMapSelectionUI();
        });

        plannerList.addEventListener('change', async (event) => {
            const target = event.target;
            const slotId = target.dataset.slotId;

            if (!slotId) {
                return;
            }

            if (target.classList.contains('driver-select')) {
                await saveDriver(slotId, target.value || null);
                return;
            }

            if (target.classList.contains('stop-checkbox')) {
                const container = target.closest('[data-slot-container]');
                const selected = Array.from(container.querySelectorAll('.stop-checkbox:checked')).map((checkbox) => Number(checkbox.value));
                await saveStops(slotId, selected);
            }
        });

        plannerList.addEventListener('click', async (event) => {
            const unassignedButton = event.target.closest('button[data-unassigned-stop-id]');
            if (unassignedButton) {
                toggleMapStopSelection(Number(unassignedButton.dataset.unassignedStopId));
                return;
            }

            const viewButton = event.target.closest('button[data-slot-view]');
            if (viewButton) {
                const slotId = Number(viewButton.dataset.slotView);

                state.activeSlotId = slotId;
                state.mapFocusMode = 'slot';
                state.mapFocusDriverId = null;
                updateSlotFocusButton();
                updateMapFocusButton();
                renderPlanner();
                renderMapStops();
                await renderSlotRoute(slotId);

                const slot = getSlotById(slotId);
                const stops = (slot && slot.stops) ? slot.stops.filter(s => s.latitude && s.longitude) : [];
                if (stops.length > 0) {
                    const latLngs = stops.map(s => [Number(s.latitude), Number(s.longitude)]);
                    try {
                        const bounds = L.latLngBounds(latLngs);
                        map.fitBounds(bounds.pad ? bounds.pad(0.2) : bounds, { padding: [60, 60] });
                    } catch (e) {
                        map.setView([Number(stops[0].latitude), Number(stops[0].longitude)], 13);
                    }
                }

                openSlotStopsPopup(slotId);
                return;
            }

            // allow clicking the slot bar itself or the "Bekijk" button
            const focusEl = event.target.closest('[data-slot-focus]');
            if (!focusEl) {
                return;
            }

            const slotId = Number(focusEl.dataset.slotFocus);

            // Set map focus mode to the selected slot and update UI
            state.activeSlotId = slotId;
            state.mapFocusMode = 'slot';
            state.mapFocusDriverId = null;
            updateSlotFocusButton();
            updateMapFocusButton();
            renderPlanner();
            renderMapStops();
            await renderSlotRoute(slotId);
        });

        closeSlotStopsPopupButton.addEventListener('click', closeSlotStopsPopup);

        slotStopsPopup.addEventListener('click', (event) => {
            if (event.target === slotStopsPopup) {
                closeSlotStopsPopup();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSlotStopsPopup();
            }
        });

        function openSlotStopsPopup(slotId) {
            const slot = getSlotById(slotId);

            if (!slot) {
                alert('Tijdslot niet gevonden.');
                return;
            }

            const stops = slot.stops || [];
            const slotLabel = `${slot.slot_key} · ${String(slot.start_time).slice(0, 5)}-${String(slot.end_time).slice(0, 5)}`;
            const driverLabel = slot.driver ? `${slot.driver.firstname} ${slot.driver.lastname}` : 'Geen chauffeur';
            const vehicle = (state.data?.vehicles || []).find((item) => (item.time_slots || []).some((timeSlot) => Number(timeSlot.id) === Number(slot.id)));
            const vehicleLabel = vehicle
                ? `${vehicle.name}${vehicle.license_plate ? ` · ${vehicle.license_plate}` : ''}`
                : '';

            slotStopsPopupTitle.textContent = slotLabel;
            slotStopsPopupMeta.textContent = `${vehicleLabel ? `${vehicleLabel} · ` : ''}${driverLabel} · ${stops.length} stops`;
            slotStopsPopupList.innerHTML = stops.length > 0
                ? stops.map((stop, index) => {
                    const arrived = stop && stop.pivot && stop.pivot.arrived_at;
                    const arrivedTime = arrived ? new Date(stop.pivot.arrived_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                    const isLate = arrived && Number(stop.pivot.delivered_late) === 1;
                    const badgeColor = isLate ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700';
                    const badgeIcon = isLate ? '❌' : '✓';
                    const arrivedBadge = arrived ? `<span class="ml-2 inline-flex items-center justify-center rounded-full ${badgeColor} px-2 py-1 text-[11px] font-black" title="${isLate ? 'Te laat afgeleverd' : 'Afgeleverd'} om ${escapeHtml(arrivedTime)}">${badgeIcon} ${escapeHtml(arrivedTime)}</span>` : '';
                    return `
                    <div data-stop-id="${escapeHtml(stop.id)}" class="slot-stop-item rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 shadow-sm cursor-pointer">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">${index + 1}</span>
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-slate-900 truncate flex items-center gap-2">${escapeHtml(stop.name)} ${arrived ? arrivedBadge : ''}</div>
                                        <div class="mt-0.5 text-sm text-slate-600">${escapeHtml(stop.address || '')}</div>
                                    </div>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full bg-white px-3 py-1 text-[11px] font-black uppercase tracking-wide text-slate-600 border border-slate-200">${escapeHtml(formatStopWindowLabel(stop))}</span>
                        </div>
                    </div>
                `}).join('')
                : `<div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-sm text-slate-500">Geen stops in dit tijdslot.</div>`;

            slotStopsPopup.classList.remove('hidden');
            slotStopsPopup.classList.add('flex');
        }

        function closeSlotStopsPopup() {
            slotStopsPopup.classList.add('hidden');
            slotStopsPopup.classList.remove('flex');
        }

        // Click a stop in the slot popup to zoom to it on the map
        slotStopsPopupList.addEventListener('click', (e) => {
            const el = e.target.closest('[data-stop-id]');
            if (!el) return;
            const stopId = Number(el.dataset.stopId);
            const stop = getStopById(stopId);
            if (!stop || !stop.latitude || !stop.longitude) return;

            // Ensure slot focus so markers are visible
            state.mapFocusMode = 'slot';
            // keep current activeSlotId
            renderMapStops();

            const lat = Number(stop.latitude);
            const lng = Number(stop.longitude);
            try {
                map.setView([lat, lng], 16);
            } catch (err) {
                // ignore
            }

            const marker = markerByStopId[stopId];
            if (marker && marker.openTooltip) {
                marker.openTooltip();
            }
        });

        function updateSlotFocusButton() {
            clearSlotFocus.classList.add('hidden');
        }

        function updateMapFocusButton() {
            showUnassignedOnMap.textContent = 'Toon op kaart';
            renderMapStops();
        }

        function renderAllowedSlotOptions() {
            const slotSelect = document.getElementById('edit-stop-slot');
            const slotWindows = state.data?.slot_windows || [];

            if (!slotSelect) {
                return;
            }

            const previousValue = slotSelect.value;
            slotSelect.innerHTML = '<option value="">-- Selecteer slot --</option>' + slotWindows.map((slotWindow) => {
                const start = String(slotWindow.start_time).slice(0, 5);
                const end = String(slotWindow.end_time).slice(0, 5);
                return `<option value="${escapeHtml(slotWindow.slot_key)}">${escapeHtml(slotWindow.slot_key)} (${escapeHtml(start)}-${escapeHtml(end)})</option>`;
            }).join('');

            if (previousValue && slotWindows.some((slotWindow) => String(slotWindow.slot_key) === String(previousValue))) {
                slotSelect.value = previousValue;
            }
        }

        function clearSlotRoute() {
            if (slotRouteLayer) {
                map.removeLayer(slotRouteLayer);
                slotRouteLayer = null;
            }
        }

        async function renderSlotRoute(slotId) {
            clearSlotRoute();

            const requestToken = ++slotRouteRequestToken;

            if (!slotId) {
                return;
            }

            try {
                const response = await fetch(`/api/route-planner/time-slots/${slotId}/route`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const coordinates = Array.isArray(payload.coordinates) ? payload.coordinates : [];

                // Ignore stale responses when user already switched to another slot.
                if (requestToken !== slotRouteRequestToken || Number(state.activeSlotId) !== Number(slotId) || state.mapFocusMode !== 'slot') {
                    return;
                }

                if (coordinates.length < 2) {
                    return;
                }

                const latLngs = coordinates
                    .map((point) => Array.isArray(point) ? [Number(point[1]), Number(point[0])] : null)
                    .filter(Boolean);

                if (latLngs.length < 2) {
                    return;
                }

                slotRouteLayer = L.polyline(latLngs, {
                    color: '#0f766e',
                    weight: 5,
                    opacity: 0.9,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(map);
            } catch (error) {
                // If the routing service is unavailable, keep the pins visible and skip the lines.
            }
        }

        async function loadPlanner() {
            plannerStatus.textContent = 'Laden van data...';

            const response = await fetch(`/api/route-planner?date=${encodeURIComponent(state.date)}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                plannerStatus.textContent = 'Fout bij laden';
                setLoading(false);
                return;
            }

            state.data = await response.json();
            keepOnlyExistingSelectedStops();
            renderPlanner();
            // Load APK warnings and damage reports for visible vehicles
            apkStatusByVehicleId = {};
            damageByVehicleId = {};
            loadApkWarnings();
            loadDamageReports();
            renderMapAssignVehicleOptions();
            renderMapAssignSlotOptions();
            renderAllowedSlotOptions();
            renderMapStops();
            if (state.mapFocusMode === 'slot' && state.activeSlotId) {
                await renderSlotRoute(state.activeSlotId);
            } else {
                clearSlotRoute();
            }
            updateRouteFilterButtons();
            updateSlotFocusButton();
            updateMapFocusButton();
            plannerStatus.textContent = `Data geladen voor ${state.data.date}`;
            setTimeout(() => map.invalidateSize(), 0);
            setLoading(false);
        }

        function renderPlanner() {
            const vehicles = getVisibleVehicles();
            const drivers = state.data?.drivers || [];
            const stops = state.data?.stops || [];
            const unassignedStops = getUnassignedStops();

            countVehicles.textContent = String((state.data?.vehicles || []).length);

            let assignedCount = 0;
            (state.data?.vehicles || []).forEach((vehicle) => {
                vehicle.time_slots.forEach((slot) => {
                    if (slot.driver_id) {
                        assignedCount += 1;
                    }
                });
            });

            countAssigned.textContent = String(assignedCount);
            countStops.textContent = String(stops.length);

            renderUnassignedStops(unassignedStops);

            if (vehicles.length === 0) {
                plannerList.innerHTML = state.vehicleView === 'active'
                    ? `
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm text-slate-600 space-y-3">
                            <p class="font-semibold text-slate-700">Er staan nog geen voertuigen met routes in de lijst.</p>
                            <p>Voeg een chauffeur of stops toe aan een tijdslot, of toon alle voertuigen.</p>
                            <button id="show-all-vehicles" class="rounded-xl bg-orange-500 px-4 py-2 text-xs font-bold text-white transition hover:bg-orange-600">Toon alle voertuigen</button>
                        </div>
                    `
                    : `
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-sm text-slate-600">
                            Geen voertuigen gevonden. Voeg eerst voertuigen toe op de voertuigenpagina.
                        </div>
                    `;

                const showAllButton = document.getElementById('show-all-vehicles');
                if (showAllButton) {
                    showAllButton.addEventListener('click', () => {
                        state.vehicleView = 'all';
                        updateRouteFilterButtons();
                        renderPlanner();
                    });
                }
                return;
            }

            plannerList.innerHTML = vehicles.map((vehicle) => {
                const slotsByKey = Object.fromEntries((vehicle.time_slots || []).map((slot) => [slot.slot_key, slot]));
                const orderedSlots = (state.data.slot_windows || [])
                    .map((slotWindow) => slotsByKey[slotWindow.slot_key])
                    .filter((slot) => slot && slotHasRoute(slot));
                if (orderedSlots.length === 0) {
                    return '';
                }

                const routeSlotCount = orderedSlots.filter((slot) => slot.driver_id || (slot.stops || []).length > 0).length;
                const stopCount = orderedSlots.reduce((sum, slot) => sum + (slot.stops || []).length, 0);

                const hasIssues = Boolean(vehicle.has_issues) || Boolean(apkStatusByVehicleId[vehicle.id]?.isExpired) || Boolean(damageByVehicleId[vehicle.id]?.open);
                return `
                    <section class="p-2.5 rounded-lg border border-slate-200 bg-white space-y-1.5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-extrabold text-slate-900">${escapeHtml(vehicle.name)}</h3>
                                    ${hasIssues ? `<div class="inline-flex items-center gap-1.5 ml-2">
                                        ${apkStatusByVehicleId[vehicle.id]?.isExpired ? `<span title="APK verlopen" class="inline-flex items-center text-xs font-bold text-red-600">
                                            <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-4 w-4\" viewBox=\"0 0 20 20\" fill=\"currentColor\"><path fill-rule=\"evenodd\" d=\"M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l5.454 9.688A1.75 1.75 0 0 1 16.95 15H3.05a1.75 1.75 0 0 1-1.246-2.213L8.257 3.1zM11 13a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-1-8a.75.75 0 0 0-.75.75v4.5a.75.75 0 0 0 1.5 0v-4.5A.75.75 0 0 0 10 5z\" clip-rule=\"evenodd\"/></svg>
                                        </span>` : ''}
                                        ${damageByVehicleId[vehicle.id]?.open ? `<span title="${damageByVehicleId[vehicle.id].open} open${damageByVehicleId[vehicle.id].open > 1 ? 'e' : ''} schade" class="inline-flex items-center text-xs font-bold text-red-600">
                                            <i class="fas fa-car-crash"></i>
                                        </span>` : ''}
                                    </div>` : ''}
                                </div>
                                <p class="text-[11px] text-slate-500">${escapeHtml(vehicle.license_plate || '-')} · ${escapeHtml(vehicle.brand || '')} ${escapeHtml(vehicle.model || '')}</p>
                                <div class="mt-0.5 flex flex-wrap gap-1 text-[10px] font-bold text-slate-600">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5">${routeSlotCount} route-sloten</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5">${stopCount} stops</span>
                                </div>
                            </div>
                            <span class="rounded-full ${routeSlotCount > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'} px-2 py-0.5 text-[10px] font-black uppercase tracking-wide">
                                ${routeSlotCount > 0 ? 'Actief' : 'Leeg'}
                            </span>
                        </div>
                        <div class="space-y-1.5">
                            ${orderedSlots.map((slot) => renderSlot(slot, drivers, stops)).join('')}
                        </div>
                    </section>
                `;
            }).filter(Boolean).join('');
        }

        function getVisibleVehicles() {
            const vehicles = state.data?.vehicles || [];

            if (state.vehicleView === 'all') {
                return vehicles;
            }

            return vehicles.filter((vehicle) => vehicleHasRoutes(vehicle));
        }

        async function loadApkWarnings() {
            if (!state.data?.vehicles) return;

            const statusMap = {};
            const vehicles = state.data.vehicles;

            const requests = vehicles.map(async (vehicle) => {
                if (!vehicle.license_plate) return;

                try {
                    const response = await fetch(`/api/vehicles-info/${encodeURIComponent(vehicle.license_plate)}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) return;

                    const data = await response.json();
                    const raw = String(data.vervaldatum_apk ?? '').trim();
                    if (!/^\d{8}$/.test(raw)) return;

                    const year = Number(raw.slice(0,4));
                    const month = Number(raw.slice(4,6));
                    const day = Number(raw.slice(6,8));
                    const apkDate = new Date(year, month - 1, day);

                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const diffMs = apkDate.getTime() - today.getTime();
                    const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                    if (days < 0) {
                        statusMap[vehicle.id] = { isExpired: true, label: `${day}-${String(month).padStart(2,'0')}-${year}` };
                        return;
                    }

                    if (days <= APK_WARNING_DAYS) {
                        statusMap[vehicle.id] = { isExpired: false, label: `${days} dagen` };
                    }
                } catch (e) {
                    // ignore per-vehicle failures
                }
            });

            await Promise.allSettled(requests);
            apkStatusByVehicleId = statusMap;
            renderPlanner();
        }

        function buildDamageMap(reports) {
            const map = {};
            reports.forEach((item) => {
                if (!item.vehicle_id) return;
                if (!map[item.vehicle_id]) {
                    map[item.vehicle_id] = { total: 0, open: 0, items: [] };
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
                const response = await fetch('/api/damage-reports', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) return;
                const damageReports = await response.json();
                damageByVehicleId = buildDamageMap(damageReports);
                renderPlanner();
            } catch (e) {
                // ignore damage endpoint errors so planner remains usable
            }
        }

        function vehicleHasRoutes(vehicle) {
            return (vehicle.time_slots || []).some((slot) => slot.driver_id || (slot.stops || []).length > 0);
        }

        function slotHasRoute(slot) {
            return Boolean(slot.driver_id) || (slot.stops || []).length > 0;
        }

        function getUnassignedStops() {
            const assignedStopIds = new Set();

            (state.data?.vehicles || []).forEach((vehicle) => {
                (vehicle.time_slots || []).forEach((slot) => {
                    (slot.stops || []).forEach((stop) => {
                        assignedStopIds.add(Number(stop.id));
                    });
                });
            });

            return (state.data?.stops || []).filter((stop) => !assignedStopIds.has(Number(stop.id)));
        }

        function getStopsForDriver(driverId) {
            const stopIds = new Set();

            (state.data?.vehicles || []).forEach((vehicle) => {
                (vehicle.time_slots || []).forEach((slot) => {
                    if (Number(slot.driver_id) !== Number(driverId)) {
                        return;
                    }

                    (slot.stops || []).forEach((stop) => {
                        stopIds.add(Number(stop.id));
                    });
                });
            });

            return stopIds;
        }

        function setMapFocusUnassigned() {
            state.mapFocusMode = 'unassigned';
            state.mapFocusDriverId = null;
            state.activeSlotId = null;
            updateSlotFocusButton();
            updateMapFocusButton();
            renderPlanner();
            renderMapStops();
            clearSlotRoute();
        }

        function renderUnassignedStops(stops) {
            // Keep only a simple count display here; names were removed to keep the UI compact.
            unassignedStopsCount.textContent = `${stops.length} niet toegewezen`;
            // Clear the detailed list (we only show the count and the "Toon op kaart" button).
            unassignedStopsList.innerHTML = '';
        }

        function updateSlotFocusButton() {
            clearSlotFocus.classList.toggle('hidden', !state.activeSlotId);
        }

        function updateMapFocusButton() {
            if (state.mapFocusMode === 'unassigned') {
                showUnassignedOnMap.textContent = 'Toon alle stops';
            } else {
                showUnassignedOnMap.textContent = 'Toon op kaart';
            }
        }

        function updateRouteFilterButtons() {
            const active = state.vehicleView === 'active';

            routeFilterActive.className = active
                ? 'rounded-full bg-orange-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600'
                : 'rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200';

            routeFilterAll.className = !active
                ? 'rounded-full bg-orange-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-orange-600'
                : 'rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-200';
        }

        function renderSlot(slot, drivers, stops) {
            const hasDriver = Boolean(slot.driver_id);
            return `
                <div data-slot-focus="${slot.id}" class="cursor-pointer rounded-lg border ${hasDriver ? 'border-emerald-200 bg-emerald-50/20' : 'border-slate-200 bg-white/60'} p-2 text-xs flex items-center justify-between" data-slot-container>
                    <div class="flex flex-col">
                        <div class="font-extrabold text-xs text-slate-900">${escapeHtml(slot.slot_key)} <span class="ml-1 text-[10px] font-semibold text-slate-500">${escapeHtml(slot.start_time.slice(0,5))}-${escapeHtml(slot.end_time.slice(0,5))}</span></div>
                        <div class="mt-0.5 text-[10px] text-slate-500">Stops: <span class="font-bold text-slate-700">${(slot.stops || []).length}</span></div>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <select class="driver-select w-36 text-[10px] rounded px-2 py-0.5" data-slot-id="${slot.id}">
                            <option value="">-- geen --</option>
                            ${drivers.map((driver) => {
                                const label = `${driver.firstname} ${driver.lastname}`;
                                const selected = Number(slot.driver_id) === Number(driver.id) ? 'selected' : '';
                                return `<option value="${driver.id}" ${selected}>${escapeHtml(label)}</option>`;
                            }).join('')}
                        </select>
                        <div class="flex gap-1">
                            <button type="button" data-slot-view="${slot.id}" class="text-[10px] font-black text-orange-700 underline px-1.5 py-0.5">Bekijk</button>
                        </div>
                    </div>
                </div>
            `;
        }

        async function saveDriver(slotId, driverId) {
            setLoading(true);
            const response = await fetch(`/api/route-planner/time-slots/${slotId}/driver`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ driver_id: driverId ? Number(driverId) : null }),
            });

            if (!response.ok) {
                setLoading(false);
                const payload = await response.json().catch(() => ({}));
                alert(payload.message || 'Driver opslaan mislukt');
                await loadPlanner();
                return;
            }

            await loadPlanner();
            setLoading(false);
        }

        async function saveStops(slotId, stopIds) {
            setLoading(true);
            const response = await fetch(`/api/route-planner/time-slots/${slotId}/stops`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ stop_ids: stopIds }),
            });

            if (!response.ok) {
                setLoading(false);
                alert('Stops opslaan mislukt');
                await loadPlanner();
                return;
            }

            await loadPlanner();
            setLoading(false);
        }

        function getMarkerIconForStop(stop, isSelected) {
            let color = '#94a3b8'; // default gray
            let icon = 'circle';
            let textColor = '#ffffff';

            const id = Number(stop?.id);
            const unassignedIds = new Set((getUnassignedStops() || []).map((s) => Number(s.id)));

            if (isSelected) {
                color = '#f59e0b'; // orange
                icon = 'circle-check';
                textColor = '#ffffff';
            } else if (state.mapFocusMode === 'unassigned' && unassignedIds.has(id)) {
                color = '#10b981'; // green
                icon = 'circle-xmark';
                textColor = '#ffffff';
            } else if (state.mapFocusMode === 'driver' && state.mapFocusDriverId) {
                const driverStops = getStopsForDriver(state.mapFocusDriverId);
                if (driverStops.has(id)) {
                    color = '#38bdf8'; // cyan
                    icon = 'check-circle';
                    textColor = '#ffffff';
                }
            } else if (state.mapFocusMode === 'slot' && state.activeSlotId) {
                const slotStops = getStopsForActiveSlot();
                if (slotStops.has(id)) {
                    color = '#a78bfa'; // purple
                    icon = 'circle-check';
                    textColor = '#ffffff';
                }
            }

            // Create SVG marker icon
            // If the stop has an arrived timestamp, show a badge with checkmark or X
            const arrived = Boolean(stop && stop.pivot && stop.pivot.arrived_at);
            const isLate = arrived && Number(stop.pivot.delivered_late) === 1;
            const badgeColor = isLate ? '#dc2626' : '#10b981'; // red or green
            const innerFill = 'white';
            const arrivedBadge = arrived ? `
                <g transform="translate(28 8)">
                    <circle cx="0" cy="0" r="7" fill="${badgeColor}" />
                    <path d="M -3 0 L -1 2 L 4 -3" stroke="#ffffff" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                </g>
            ` : '';

            const svgContent = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 50" width="40" height="50">
                    <defs>
                        <filter id="shadow">
                            <feDropShadow dx="0" dy="2" stdDeviation="4" flood-opacity="0.3"/>
                        </filter>
                    </defs>
                    <!-- Pin shape -->
                    <path d="M 20 0 C 11.7 0 5 6.7 5 15 C 5 28 20 50 20 50 S 35 28 35 15 C 35 6.7 28.3 0 20 0 Z" 
                          fill="${color}" filter="url(#shadow)"/>
                    <!-- Inner circle for icon -->
                    <circle cx="20" cy="15" r="8" fill="${innerFill}" opacity="0.95"/>
                    ${arrivedBadge}
                </svg>
            `;

            const svgIcon = L.icon({
                iconUrl: 'data:image/svg+xml;base64,' + btoa(svgContent),
                iconSize: [40, 50],
                iconAnchor: [20, 50],
                popupAnchor: [0, -40],
                className: 'custom-marker'
            });

            return svgIcon;
        }


        function getStartEndIconForBranch() {
            const svgContent = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50" height="50">
                    <defs>
                        <filter id="shadow-target">
                            <feDropShadow dx="0" dy="3" stdDeviation="5" flood-opacity="0.4"/>
                        </filter>
                    </defs>
                    <!-- Pin body -->
                    <path d="M 25 1 C 15 1 7 9 7 19 C 7 31 19 46 25 49 C 31 46 43 31 43 19 C 43 9 35 1 25 1 Z"
                          fill="#0f172a" filter="url(#shadow-target)"/>
                    <!-- Target rings -->
                    <circle cx="25" cy="19" r="10" fill="#f8fafc"/>
                    <circle cx="25" cy="19" r="7" fill="#ef4444"/>
                    <circle cx="25" cy="19" r="4" fill="#f8fafc"/>
                    <circle cx="25" cy="19" r="2" fill="#ef4444"/>
                </svg>
            `;

            const svgIcon = L.icon({
                iconUrl: 'data:image/svg+xml;base64,' + btoa(svgContent),
                iconSize: [50, 50],
                iconAnchor: [25, 50],
                popupAnchor: [0, -45],
                className: 'custom-marker'
            });

            return svgIcon;
        }

        function getStopHoverHtml(stop) {
            const timeWindow = formatStopWindowLabel(stop);
            const dateLabel = stop.date ? new Date(stop.date).toLocaleDateString('nl-NL') : 'Algemeen';
            let arrivedHtml = '';
            try {
                if (stop && stop.pivot && stop.pivot.arrived_at) {
                    const at = new Date(stop.pivot.arrived_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const isLate = Number(stop.pivot.delivered_late) === 1;
                    const bgColor = isLate ? '#dc2626' : '#16a34a'; // red or green
                    const title = isLate ? 'Te laat afgeleverd' : 'Afgeleverd';
                    const icon = isLate ? '❌' : '✓';
                    arrivedHtml = `<div style="margin-top:6px;"><span style="display:inline-flex;align-items:center;justify-content:center;background:${bgColor};color:#fff;border-radius:9999px;font-weight:800;font-size:11px;padding:4px 8px" title="${title} om ${escapeHtml(at)}">${icon} ${escapeHtml(at)}</span></div>`;
                }
            } catch (e) {
                // ignore formatting errors
            }

            return `
                <div style="min-width: 220px; max-width: 280px;">
                    <div style="font-weight: 800; font-size: 14px; color: #0f172a; margin-bottom: 6px;">${escapeHtml(stop.name)}</div>
                    <div style="font-size: 12px; color: #475569; line-height: 1.4; margin-bottom: 8px;">${escapeHtml(stop.address)}</div>
                    <div style="display: grid; gap: 4px; font-size: 12px; color: #0f172a; margin-bottom: 10px;">
                        <div><strong>ID:</strong> ${escapeHtml(stop.id)}</div>
                        <div><strong>Datum:</strong> ${escapeHtml(dateLabel)}</div>
                        <div><strong>Tijdvenster:</strong> ${escapeHtml(timeWindow)}</div>
                    </div>
                    ${arrivedHtml}
                </div>
            `;
        }

        function renderMapStops() {
            stopMarkers.forEach((marker) => map.removeLayer(marker));
            stopMarkers = [];
            markerByStopId = {};

            const stops = state.data?.stops || [];
            // Helper: attach pivot data from vehicle time_slots (if present)
            function getStopWithPivot(baseStop) {
                // search vehicles -> time_slots -> stops for a matching stop id
                const sid = Number(baseStop.id);
                const vehicles = state.data?.vehicles || [];
                for (const vehicle of vehicles) {
                    for (const slot of (vehicle.time_slots || [])) {
                        for (const s of (slot.stops || [])) {
                            if (Number(s.id) === sid) {
                                // merge pivot into a shallow copy
                                return Object.assign({}, baseStop, { pivot: s.pivot || {} });
                            }
                        }
                    }
                }
                return baseStop;
            }
            const visibleStopIds = getFocusedStopIds();

            stops.forEach((stop) => {
                if (!stop.latitude || !stop.longitude) {
                    return;
                }

                if (visibleStopIds && !visibleStopIds.has(Number(stop.id))) {
                    return;
                }

                const stopWithPivot = getStopWithPivot(stop);
                const isSelected = state.selectedMapStopIds.has(Number(stop.id));
                const icon = getMarkerIconForStop(stopWithPivot, isSelected);

                const marker = L.marker([Number(stop.latitude), Number(stop.longitude)], { icon })
                    .addTo(map)
                    .bindTooltip(getStopHoverHtml(stopWithPivot), {
                        direction: 'top',
                        offset: [0, -42],
                        opacity: 1,
                        sticky: true,
                        className: 'stop-hover-tooltip',
                    })
                    .on('click', () => {
                        toggleMapStopSelection(Number(stop.id));
                    });

                stopMarkers.push(marker);
                markerByStopId[Number(stop.id)] = marker;
            });

            // Render branch/filiaal marker
            if (branchMarker) {
                map.removeLayer(branchMarker);
                branchMarker = null;
            }

            if (branchSetting?.latitude && branchSetting?.longitude) {
                const startEndIcon = getStartEndIconForBranch();
                branchMarker = L.marker([Number(branchSetting.latitude), Number(branchSetting.longitude)], { icon: startEndIcon })
                    .addTo(map)
                    .bindTooltip(`<strong>🎯 Start/Finish</strong><br>${escapeHtml(branchSetting.branch_address || 'Filiaal')}`, {
                        direction: 'top',
                        offset: [0, -45],
                        opacity: 1,
                        sticky: true,
                        className: 'stop-hover-tooltip',
                    });
            }

            updateMapSelectionUI();
        }

        function getFocusedStopIds() {
            if (state.mapFocusMode === 'unassigned') {
                return new Set(getUnassignedStops().map((stop) => Number(stop.id)));
            }

            if (state.mapFocusMode === 'driver' && state.mapFocusDriverId) {
                return getStopsForDriver(state.mapFocusDriverId);
            }

            if (state.mapFocusMode === 'slot' && state.activeSlotId) {
                return getStopsForActiveSlot();
            }

            return null;
        }

        function getStopsForActiveSlot() {
            const stopIds = new Set();

            (state.data?.vehicles || []).forEach((vehicle) => {
                (vehicle.time_slots || []).forEach((slot) => {
                    if (Number(slot.id) !== Number(state.activeSlotId)) {
                        return;
                    }

                    (slot.stops || []).forEach((stop) => {
                        stopIds.add(Number(stop.id));
                    });
                });
            });

            return stopIds;
        }

        function getStopById(stopId) {
            return (state.data?.stops || []).find((s) => Number(s.id) === Number(stopId)) || null;
        }

        function getSelectedStops() {
            return Array.from(state.selectedMapStopIds)
                .map((stopId) => getStopById(stopId))
                .filter(Boolean);
        }

        function getMarkerStyleForStop(stop, isSelected) {
            // selected override
            if (isSelected) {
                return {
                    radius: 10,
                    color: '#b45309',
                    fillColor: '#f59e0b',
                    fillOpacity: 0.95,
                    weight: 2,
                };
            }

            // Determine mode-specific styles
            const id = Number(stop?.id);
            const unassignedIds = new Set((getUnassignedStops() || []).map((s) => Number(s.id)));

            if (state.mapFocusMode === 'unassigned' && unassignedIds.has(id)) {
                return {
                    radius: 8,
                    color: '#065f46',
                    fillColor: '#10b981',
                    fillOpacity: 0.85,
                    weight: 2,
                };
            }

            if (state.mapFocusMode === 'driver' && state.mapFocusDriverId) {
                const driverStops = getStopsForDriver(state.mapFocusDriverId);
                if (driverStops.has(id)) {
                    return {
                        radius: 8,
                        color: '#075985',
                        fillColor: '#38bdf8',
                        fillOpacity: 0.85,
                        weight: 2,
                    };
                }
            }

            if (state.mapFocusMode === 'slot' && state.activeSlotId) {
                const slotStops = getStopsForActiveSlot();
                if (slotStops.has(id)) {
                    return {
                        radius: 8,
                        color: '#5b21b6',
                        fillColor: '#a78bfa',
                        fillOpacity: 0.8,
                        weight: 2,
                    };
                }
            }

            // default (muted)
            return {
                radius: 8,
                color: '#0f172a',
                fillColor: '#94a3b8',
                fillOpacity: 0.6,
                weight: 2,
            };
        }

        function getMarkerStyleForId(stopId, isSelected) {
            const stop = getStopById(stopId);
            return getMarkerStyleForStop(stop || {}, isSelected);
        }

        function getMarkerStyle(isSelected) {
            if (isSelected) {
                return {
                    radius: 10,
                    color: '#b45309',
                    fillColor: '#f59e0b',
                    fillOpacity: 0.95,
                    weight: 2,
                };
            }

            return {
                radius: 8,
                color: '#0f172a',
                fillColor: '#334155',
                fillOpacity: 0.75,
                weight: 2,
            };
        }

        function toggleMapStopSelection(stopId) {
            if (state.selectedMapStopIds.has(stopId)) {
                state.selectedMapStopIds.delete(stopId);
            } else {
                state.selectedMapStopIds.add(stopId);
            }

            const marker = markerByStopId[stopId];
            if (marker) {
                const stop = getStopById(stopId);
                const newIcon = getMarkerIconForStop(stop, state.selectedMapStopIds.has(stopId));
                marker.setIcon(newIcon);
            }

            updateMapSelectionUI();
        }

        function clearSelectedMapStops() {
            state.selectedMapStopIds.forEach((stopId) => {
                const marker = markerByStopId[stopId];
                if (marker) {
                    const stop = getStopById(stopId);
                    const newIcon = getMarkerIconForStop(stop, false);
                    marker.setIcon(newIcon);
                }
            });

            state.selectedMapStopIds.clear();
        }

        function keepOnlyExistingSelectedStops() {
            const allowed = new Set((state.data?.stops || []).map((stop) => Number(stop.id)));
            state.selectedMapStopIds = new Set(Array.from(state.selectedMapStopIds).filter((id) => allowed.has(id)));
        }

        function updateMapSelectionUI() {
            const selectedCount = state.selectedMapStopIds.size;
            const selectedStops = getSelectedStops();

            mapAssignCount.textContent = `${selectedCount} stops geselecteerd`;

            selectedStopList.innerHTML = selectedStops.length
                ? selectedStops.map((stop) => `
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-700">
                        #${escapeHtml(stop.id)} ${escapeHtml(stop.name)}
                    </span>
                `).join('')
                : '<span class="text-[11px] text-slate-400">Selecteer een of meer stops op de kaart</span>';

            if (selectedCount > 0) {
                mapAssignPanel.classList.remove('hidden');
            } else {
                mapAssignPanel.classList.add('hidden');
            }

            mapAssignEdit.classList.toggle('hidden', selectedCount !== 1);
            mapAssignEdit.disabled = selectedCount !== 1;
            mapAssignDelete.disabled = selectedCount === 0;
        }

        function renderMapAssignVehicleOptions() {
            const vehicles = state.data?.vehicles || [];
            const previousValue = mapAssignVehicle.value;

            mapAssignVehicle.innerHTML = '<option value="">-- kies voertuig --</option>' + vehicles.map((vehicle) => {
                const routeLabel = vehicleHasRoutes(vehicle) ? 'routes actief' : 'leeg';
                return `<option value="${vehicle.id}">${escapeHtml(vehicle.name)} (${escapeHtml(vehicle.license_plate || '-')}) · ${routeLabel}</option>`;
            }).join('');

            if (previousValue && vehicles.some((vehicle) => String(vehicle.id) === String(previousValue))) {
                mapAssignVehicle.value = previousValue;
            }
        }

        function renderMapAssignSlotOptions() {
            const vehicleId = Number(mapAssignVehicle.value);
            const vehicle = (state.data?.vehicles || []).find((item) => Number(item.id) === vehicleId);
            const previousValue = mapAssignSlot.value;

            if (!vehicle) {
                mapAssignSlot.innerHTML = '<option value="">-- kies tijdslot --</option>';
                return;
            }

            const slots = (vehicle.time_slots || []).slice().sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)));

            mapAssignSlot.innerHTML = '<option value="">-- kies tijdslot --</option>' + slots.map((slot) => {
                return `<option value="${slot.id}">${escapeHtml(slot.slot_key)} (${escapeHtml(slot.start_time.slice(0, 5))}-${escapeHtml(slot.end_time.slice(0, 5))})</option>`;
            }).join('');

            if (previousValue && slots.some((slot) => String(slot.id) === String(previousValue))) {
                mapAssignSlot.value = previousValue;
            }
        }

        function getSlotById(slotId) {
            const vehicles = state.data?.vehicles || [];

            for (const vehicle of vehicles) {
                const foundSlot = (vehicle.time_slots || []).find((slot) => Number(slot.id) === Number(slotId));
                if (foundSlot) {
                    return foundSlot;
                }
            }

            return null;
        }

        async function deleteSelectedStops() {
            const selectedStops = getSelectedStops();

            if (selectedStops.length === 0) {
                return;
            }

            const confirmed = confirm(selectedStops.length === 1
                ? 'Weet je zeker dat je deze stop wilt verwijderen?'
                : `Weet je zeker dat je ${selectedStops.length} stops wilt verwijderen?`);

            if (!confirmed) {
                return;
            }

            setLoading(true);

            try {
                for (const stop of selectedStops) {
                    const response = await fetch(`/api/stops/${stop.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });

                    if (!response.ok) {
                        const payload = await response.json().catch(() => ({}));
                        throw new Error(payload.message || 'Stop verwijderen mislukt');
                    }
                }

                clearSelectedMapStops();
                await loadPlanner();
            } catch (error) {
                alert(error.message || 'Stop verwijderen mislukt');
            } finally {
                setLoading(false);
            }
        }

        function editSelectedStop() {
            const selectedStops = getSelectedStops();

            if (selectedStops.length !== 1) {
                alert('Selecteer precies 1 stop om te bewerken.');
                return;
            }

            openEditStopModal(Number(selectedStops[0].id));
        }

        async function moveSelectedStopsToRoute() {
            const slotId = Number(mapAssignSlot.value);

            if (!slotId) {
                alert('Kies eerst een voertuig en tijdslot.');
                return;
            }

            if (state.selectedMapStopIds.size === 0) {
                alert('Selecteer eerst stops op de kaart.');
                return;
            }

            const slot = getSlotById(slotId);
            if (!slot) {
                alert('Tijdslot niet gevonden.');
                return;
            }

            const existingStopIds = (slot.stops || []).map((stop) => Number(stop.id));
            const mergedStopIds = Array.from(new Set([...existingStopIds, ...Array.from(state.selectedMapStopIds)]));

            await saveStops(slotId, mergedStopIds);
            clearSelectedMapStops();
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatStopTimeValue(value) {
            if (!value) {
                return '';
            }

            const text = String(value);

            if (/^\d{2}:\d{2}/.test(text)) {
                return text.slice(0, 5);
            }

            return text.length >= 16 ? text.slice(11, 16) : text;
        }

        function formatStopWindowLabel(stop) {
            if (!stop) {
                return 'Niet ingesteld';
            }

            const customStart = formatStopTimeValue(stop.custom_start_time);
            const customEnd = formatStopTimeValue(stop.custom_end_time);

            if (customStart && customEnd) {
                return `${customStart} - ${customEnd}`;
            }

            if (stop.slot_key) {
                return stop.slot_key.replace('_', ' - ');
            }

            return 'Niet ingesteld';
        }

        // Custom Date Picker
        const plannerDateInputField = document.getElementById('planner-date');
        const datePickerPopup = document.getElementById('date-picker-popup');
        const datePrevMonth = document.getElementById('date-prev-month');
        const dateNextMonth = document.getElementById('date-next-month');
        const dateMonthYear = document.getElementById('date-month-year');
        const dateDaysGrid = document.getElementById('date-days-grid');
        const dateClear = document.getElementById('date-clear');
        const dateToday = document.getElementById('date-today');

        let currentPickerDate = new Date();

        function formatDateDisplay(dateStr) {
            // YYYY-MM-DD to DD-MM-YYYY
            const [year, month, day] = dateStr.split('-');
            return `${day}-${month}-${year}`;
        }

        function parseDisplayDate(displayStr) {
            // DD-MM-YYYY to YYYY-MM-DD
            const [day, month, year] = displayStr.split('-');
            return `${year}-${month}-${day}`;
        }

        function renderCalendar() {
            const year = currentPickerDate.getFullYear();
            const month = currentPickerDate.getMonth();
            
            dateMonthYear.textContent = currentPickerDate.toLocaleDateString('nl-NL', { month: 'long', year: 'numeric' });
            
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const prevLastDay = new Date(year, month, 0);
            
            const firstDayOfWeek = (firstDay.getDay() + 6) % 7; // Monday = 0
            const daysInMonth = lastDay.getDate();
            const daysInPrevMonth = prevLastDay.getDate();
            
            dateDaysGrid.innerHTML = '';
            
            // Previous month days
            for (let i = firstDayOfWeek - 1; i >= 0; i--) {
                const day = daysInPrevMonth - i;
                const dayEl = document.createElement('button');
                dayEl.textContent = day;
                dayEl.className = 'text-xs font-semibold text-slate-600 py-2 rounded-lg hover:bg-slate-800 transition';
                dayEl.disabled = true;
                dateDaysGrid.appendChild(dayEl);
            }
            
            // Current month days
            const todayDate = new Date();
            const isCurrentMonth = year === todayDate.getFullYear() && month === todayDate.getMonth();
            
            for (let day = 1; day <= daysInMonth; day++) {
                const dayEl = document.createElement('button');
                dayEl.textContent = day;
                
                const dayDate = new Date(year, month, day);
                const dateString = dayDate.toISOString().split('T')[0];
                
                let isSelected = false;
                if (state.date) {
                    isSelected = state.date === dateString;
                }
                
                let isToday = isCurrentMonth && day === todayDate.getDate();
                
                if (isSelected) {
                    dayEl.className = 'text-xs font-bold text-white bg-orange-500 py-2 rounded-lg hover:bg-orange-600 transition';
                } else if (isToday) {
                    dayEl.className = 'text-xs font-bold text-orange-500 border-2 border-orange-500 py-2 rounded-lg hover:bg-orange-500/10 transition';
                } else {
                    dayEl.className = 'text-xs font-semibold text-slate-300 py-2 rounded-lg hover:bg-slate-800 transition';
                }
                
                dayEl.addEventListener('click', () => {
                    state.date = dateString;
                    state.activeSlotId = null;
                    state.mapFocusMode = 'all';
                    state.mapFocusDriverId = null;
                    
                    plannerDateInputField.value = formatDateDisplay(dateString);
                    const formattedDate = formatDateDisplay(dateString);
                    window.history.pushState(null, '', `/route/${formattedDate}`);
                    
                    datePickerPopup.classList.add('hidden');
                    renderCalendar();
                    loadPlanner();
                });
                
                dateDaysGrid.appendChild(dayEl);
            }
            
            // Next month days
            const totalCells = dateDaysGrid.children.length;
            const remainingCells = 42 - totalCells;
            for (let day = 1; day <= remainingCells; day++) {
                const dayEl = document.createElement('button');
                dayEl.textContent = day;
                dayEl.className = 'text-xs font-semibold text-slate-600 py-2 rounded-lg hover:bg-slate-800 transition';
                dayEl.disabled = true;
                dateDaysGrid.appendChild(dayEl);
            }
        }

        plannerDateInputField.addEventListener('click', () => {
            const currentValue = state.date || new Date().toISOString().split('T')[0];
            currentPickerDate = new Date(currentValue + 'T00:00:00');
            renderCalendar();
            datePickerPopup.classList.toggle('hidden');
        });

        datePrevMonth.addEventListener('click', () => {
            currentPickerDate.setMonth(currentPickerDate.getMonth() - 1);
            renderCalendar();
        });

        dateNextMonth.addEventListener('click', () => {
            currentPickerDate.setMonth(currentPickerDate.getMonth() + 1);
            renderCalendar();
        });

        dateClear.addEventListener('click', () => {
            state.date = null;
            plannerDateInputField.value = '';
            datePickerPopup.classList.add('hidden');
        });

        dateToday.addEventListener('click', () => {
            const today = new Date().toISOString().split('T')[0];
            state.date = today;
            state.activeSlotId = null;
            state.mapFocusMode = 'all';
            state.mapFocusDriverId = null;
            
            plannerDateInputField.value = formatDateDisplay(today);
            currentPickerDate = new Date(today + 'T00:00:00');
            renderCalendar();
            window.history.pushState(null, '', `/route/${formatDateDisplay(today)}`);
            
            datePickerPopup.classList.add('hidden');
            loadPlanner();
        });

        // Close picker when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#planner-date') && !e.target.closest('#date-picker-popup')) {
                datePickerPopup.classList.add('hidden');
            }
        });

        // Set initial date display
        if (state.date) {
            plannerDateInputField.value = formatDateDisplay(state.date);
        }

        // Toggle planner panel collapse/expand
        const plannerPanel = document.getElementById('planner-panel');
        const togglePlannerButton = document.getElementById('toggle-planner-collapse');

        togglePlannerButton.addEventListener('click', () => {
            plannerPanel.classList.toggle('collapsed');
            // Persist collapse state to localStorage
            const isCollapsed = plannerPanel.classList.contains('collapsed');
            localStorage.setItem('plannerPanelCollapsed', isCollapsed ? 'true' : 'false');
        });

        // Restore collapse state from localStorage
        const wasCollapsed = localStorage.getItem('plannerPanelCollapsed') === 'true';
        if (wasCollapsed) {
            plannerPanel.classList.add('collapsed');
        }

        // Edit Stop Modal Functions
        const stopEditModal = document.getElementById('stop-edit-modal');
        const slotTypeFixed = document.getElementById('slot-type-fixed');
        const slotTypeCustom = document.getElementById('slot-type-custom');
        const customTimeInputs = document.getElementById('custom-time-inputs');
        const stopEditForm = document.getElementById('stop-edit-form');
        let currentEditingStopId = null;

        // Toggle custom time inputs visibility
        slotTypeFixed.addEventListener('change', () => {
            customTimeInputs.classList.add('hidden');
            document.getElementById('edit-stop-slot').classList.remove('hidden');
        });

        slotTypeCustom.addEventListener('change', () => {
            customTimeInputs.classList.remove('hidden');
        });

        window.openEditStopModal = function(stopId) {
            currentEditingStopId = stopId;
            const stop = getStopById(stopId);
            
            if (!stop) {
                alert('Stop niet gevonden');
                return;
            }

            // Populate form with stop data
            document.getElementById('edit-stop-name').value = stop.name || '';
            document.getElementById('edit-stop-address').value = stop.address || '';
            document.getElementById('edit-stop-lat').value = stop.latitude || '';
            document.getElementById('edit-stop-lng').value = stop.longitude || '';
            document.getElementById('edit-stop-date').value = stop.date || '';

            // Set slot type
            if (stop.custom_start_time && stop.custom_end_time) {
                slotTypeCustom.checked = true;
                customTimeInputs.classList.remove('hidden');
                document.getElementById('edit-stop-custom-start').value = formatStopTimeValue(stop.custom_start_time);
                document.getElementById('edit-stop-custom-end').value = formatStopTimeValue(stop.custom_end_time);
                document.getElementById('edit-stop-slot').value = '';
            } else {
                slotTypeFixed.checked = true;
                customTimeInputs.classList.add('hidden');
                document.getElementById('edit-stop-slot').value = stop.slot_key || '';
                document.getElementById('edit-stop-custom-start').value = '';
                document.getElementById('edit-stop-custom-end').value = '';
            }

            stopEditModal.classList.remove('hidden');
        };

        window.closeEditStopModal = function() {
            stopEditModal.classList.add('hidden');
            currentEditingStopId = null;
            stopEditForm.reset();
        };

        // Save stop changes
        document.getElementById('stop-edit-save').addEventListener('click', async () => {
            if (!currentEditingStopId) return;

            const isCustom = slotTypeCustom.checked;
            const payload = {
                name: document.getElementById('edit-stop-name').value,
                address: document.getElementById('edit-stop-address').value,
                latitude: Number(document.getElementById('edit-stop-lat').value),
                longitude: Number(document.getElementById('edit-stop-lng').value),
                date: document.getElementById('edit-stop-date').value || null,
                is_active: true,
            };

            if (isCustom) {
                payload.custom_start_time = document.getElementById('edit-stop-custom-start').value;
                payload.custom_end_time = document.getElementById('edit-stop-custom-end').value;
                payload.slot_key = null;
            } else {
                payload.slot_key = document.getElementById('edit-stop-slot').value || null;
                payload.custom_start_time = null;
                payload.custom_end_time = null;
            }

            console.log('Sending stop update payload:', payload);

            try {
                setLoading(true);
                const response = await fetch(`/api/stops/${currentEditingStopId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    const payload = await response.json();
                    const message = payload.message || payload.errors?.name?.[0] || 'Fout bij opslaan';
                    alert(message);
                    setLoading(false);
                    return;
                }

                closeEditStopModal();
                await loadPlanner();
            } catch (error) {
                console.error('Error saving stop:', error);
                alert('Fout bij opslaan: ' + error.message);
            } finally {
                setLoading(false);
            }
        });

        // Close modal on backdrop click
        stopEditModal.addEventListener('click', (e) => {
            if (e.target === stopEditModal) {
                closeEditStopModal();
            }
        });

        loadPlanner();
    </script>
</body>
</html>