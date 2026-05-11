<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Drop&Go | Levering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Manrope', sans-serif; }
        #map { height: 100%; width: 100%; z-index: 1; filter: saturate(1.2); }
    </style>
</head>
<body class="bg-[#F9FAFB] text-slate-800">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="h-16 bg-slate-900 text-white flex items-center px-4 md:px-8 z-20 shadow-sm">
            <div class="max-w-7xl w-full mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('deliver.page') }}" class="flex items-center justify-center h-9 w-9 rounded-lg hover:bg-slate-800 active:bg-slate-700 transition">
                        <i class="fas fa-arrow-left text-white"></i>
                    </a>
                    <div class="min-w-0 ml-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-black tracking-tighter text-white uppercase">Drop<span class="text-orange-500">&amp;Go</span></span>
                            <span id="slotTitle" class="text-sm text-slate-300 truncate">Laden...</span>
                        </div>
                        <p id="progressText" class="text-xs text-slate-400 mt-0.5"></p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-sm font-semibold text-slate-200">{{ now()->format('d M Y') }}</div>
                    @if (session()->has('driver_id'))
                        <form method="POST" action="{{ route('deliver.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-200 bg-white/5 hover:bg-white/10 px-3 py-1 rounded-md transition">Uitloggen</button>
                        </form>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main content -->
        <div class="flex-1 flex flex-col md:flex-row max-w-7xl w-full mx-auto gap-4 px-4 md:px-8 py-4 pb-24 md:pb-4">
            <!-- Map section -->
            <div class="md:w-2/3 rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div id="map" class="h-80 md:h-full md:min-h-[600px] bg-slate-100"></div>
            </div>

            <!-- Stop info card -->
            <div class="md:w-1/3 flex flex-col gap-3">
                <div class="rounded-3xl border border-slate-100 bg-white shadow-sm md:sticky md:top-20">
                    <div class="p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                        <h2 id="stopName" class="text-lg font-black leading-tight">Laden...</h2>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div id="currentStopNum" class="px-3 py-1 rounded-lg bg-orange-50 text-orange-700 text-xs font-bold whitespace-nowrap"></div>
                            <div id="deliveryStatusBadge" class="hidden px-3 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold whitespace-nowrap">
                                ✓ Afgeleverd
                            </div>
                        </div>

                        <p id="stopAddress" class="text-sm leading-6 text-slate-600 mb-4"></p>

                        <a 
                            id="routeLink" 
                            target="_blank" 
                            href="#" 
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800 active:scale-[0.98]"
                        >
                            <i class="fas fa-map text-xs"></i> MAPS OPENEN
                        </a>
                    </div>
                </div>

                <div class="space-y-2">
                    <button 
                        id="arrivedBtn" 
                        class="w-full rounded-xl bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 text-xs font-bold shadow-lg shadow-orange-500/20 transition-all active:scale-95"
                    >
                        AANGEKOMEN
                    </button>

                    <button 
                        id="nextStopBtn" 
                        class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 text-xs font-bold shadow-lg shadow-blue-600/20 transition-all active:scale-95 hidden"
                    >
                        VOLGENDE STOP
                    </button>

                    <button 
                        id="finishRouteBtn" 
                        class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-3 text-xs font-bold shadow-lg shadow-slate-900/20 transition-all active:scale-95 hidden"
                    >
                        ROUTE AFRONDEN
                    </button>

                    <button 
                        id="backBtn" 
                        class="w-full rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 text-xs font-bold transition-all hidden"
                    >
                        ← VORIGE STOP
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const slotId = {{ request()->route('timeSlot') ?? 'null' }};
        let map = null;
        let currentSlot = null;
        let allStops = [];
        let currentStopIndex = 0;
        const branchSetting = @json($branchSetting ?? null);
        let branchReturned = false;
        let branchMarker = null;

        async function loadSlot() {
            try {
                console.log('Loading slot:', slotId);
                const url = `/api/route-planner/time-slots/${slotId}`;
                
                const res = await fetch(url);
                if (!res.ok) throw new Error('Laden mislukt: ' + res.statusText);
                const found = await res.json();
                
                console.log('Slot loaded:', found);
                currentSlot = found;
                branchReturned = Boolean(found.returned_to_branch_at);

                // Sort stops by sequence
                allStops = (found.stops || [])
                    .slice()
                    .sort((a, b) => (a.pivot?.sequence || 999) - (b.pivot?.sequence || 999));

                const allStopsDelivered = allStops.length > 0 && allStops.every((stop) => Boolean(stop.pivot?.arrived_at));

                // Update title
                document.getElementById('slotTitle').textContent = `${escapeHtml(found.vehicle?.name || 'Voertuig')}`;

                if (!allStops.length || allStopsDelivered) {
                    currentStopIndex = 0;
                    showBranchLocation();
                    return;
                }

                // Show first stop
                currentStopIndex = 0;
                showStop(currentStopIndex);

            } catch (err) {
                console.error('Error loading slot:', err);
                document.getElementById('stopName').textContent = '❌ Fout: ' + err.message;
            }
        }

        function showStop(idx) {
            if (idx < 0 || idx >= allStops.length) {
                showBranchLocation();
                return;
            }

            currentStopIndex = idx;
            const stop = allStops[idx];
            const arrived = stop.pivot?.arrived_at;
            const isLate = arrived && Number(stop.pivot?.delivered_late) === 1;

            branchMarker = null;

            // Update UI
            document.getElementById('stopName').textContent = escapeHtml(stop.name || 'Stop');
            document.getElementById('stopAddress').textContent = escapeHtml(stop.address || '-');
            document.getElementById('currentStopNum').textContent = `${idx + 1} van ${allStops.length}`;
            document.getElementById('progressText').textContent = `${idx + 1}/${allStops.length}`;

            // Update button + delivered status badge
            const btn = document.getElementById('arrivedBtn');
            const statusBadge = document.getElementById('deliveryStatusBadge');
            if (arrived) {
                const arrivedTime = new Date(arrived).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                const icon = isLate ? '❌' : '✓';
                const badgeBgColor = isLate ? 'bg-red-100' : 'bg-emerald-100';
                const badgeTextColor = isLate ? 'text-red-700' : 'text-emerald-700';
                const badgeText = isLate ? `❌ Te laat afgeleverd ${arrivedTime}` : `✓ Afgeleverd ${arrivedTime}`;
                
                btn.textContent = `${icon} ${arrivedTime}`;
                btn.disabled = true;
                btn.classList.remove('bg-orange-500', 'hover:bg-orange-600', 'shadow-lg', 'shadow-orange-500/20');
                btn.classList.add('bg-orange-100', 'text-orange-800', 'shadow-none');
                statusBadge.textContent = badgeText;
                statusBadge.classList.remove('hidden', 'bg-emerald-100', 'text-emerald-700', 'bg-red-100', 'text-red-700');
                statusBadge.classList.add(badgeBgColor, badgeTextColor);
                // Show the next route as soon as this stop is finished.
                // Even if the next stop is already marked complete, the driver can still open it.
                document.getElementById('nextStopBtn').classList.toggle('hidden', idx === allStops.length - 1);
                document.getElementById('finishRouteBtn').classList.toggle('hidden', idx !== allStops.length - 1);
            } else {
                btn.textContent = 'AANGEKOMEN';
                btn.disabled = false;
                btn.classList.remove('bg-orange-100', 'text-orange-800', 'shadow-none');
                btn.classList.add('bg-orange-500', 'hover:bg-orange-600', 'shadow-lg', 'shadow-orange-500/20');
                statusBadge.classList.add('hidden');
                // Hide next button
                document.getElementById('nextStopBtn').classList.add('hidden');
                document.getElementById('finishRouteBtn').classList.add('hidden');
            }

            // Back button visibility
            document.getElementById('backBtn').classList.toggle('hidden', idx === 0);

            // Map
            if (!map) {
                map = L.map('map').setView([52.2, 5.77], 7);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '©OpenStreetMap'
                }).addTo(map);
            }

            // Clear markers
            map.eachLayer((layer) => {
                if (layer && layer.options && layer.options.pane !== 'tilePane') {
                    map.removeLayer(layer);
                }
            });

            // Add marker for current stop
            if (stop.latitude && stop.longitude) {
                const marker = L.marker([stop.latitude, stop.longitude])
                    .addTo(map)
                    .bindPopup(`<strong>${escapeHtml(stop.name)}</strong><br>${escapeHtml(stop.address || '-')}`)
                    .openPopup();

                if (arrived) {
                    const tooltipIcon = isLate ? '❌ Te laat' : '✓ Afgeleverd';
                    marker.bindTooltip(tooltipIcon, {
                        permanent: true,
                        direction: 'top',
                        offset: [0, -30],
                        className: 'delivered-tooltip'
                    });
                }

                map.setView([stop.latitude, stop.longitude], 15);
            }

            // Update route link
            document.getElementById('routeLink').href = googleMapsLink(stop);
        }

        function showBranchLocation() {
            const stopName = document.getElementById('stopName');
            const stopAddress = document.getElementById('stopAddress');
            const currentStopNum = document.getElementById('currentStopNum');
            const btn = document.getElementById('arrivedBtn');
            const statusBadge = document.getElementById('deliveryStatusBadge');
            const nextBtn = document.getElementById('nextStopBtn');
            const backBtn = document.getElementById('backBtn');
            const finishBtn = document.getElementById('finishRouteBtn');

            stopName.textContent = branchReturned ? '✓ Rit op het filiaal' : 'Eindlocatie';
            stopAddress.textContent = branchSetting?.branch_address || 'Filiaal';
            currentStopNum.textContent = 'Eindlocatie';
            document.getElementById('progressText').textContent = `${allStops.length}/${allStops.length} · Terug naar filiaal`;

            btn.disabled = branchReturned;
            btn.textContent = branchReturned ? '✓ Terug bij filiaal' : 'AANGEKOMEN BIJ FILIAAL';
            btn.classList.toggle('bg-orange-500', !branchReturned);
            btn.classList.toggle('hover:bg-orange-600', !branchReturned);
            btn.classList.toggle('shadow-lg', !branchReturned);
            btn.classList.toggle('shadow-orange-500/20', !branchReturned);
            btn.classList.toggle('bg-orange-100', branchReturned);
            btn.classList.toggle('text-orange-800', branchReturned);
            btn.classList.toggle('shadow-none', branchReturned);

            statusBadge.classList.toggle('hidden', !branchReturned);
            statusBadge.textContent = branchReturned ? '✓ Terug bij filiaal' : '';
            nextBtn.classList.add('hidden');
            backBtn.classList.add('hidden');
            finishBtn.classList.toggle('hidden', !branchReturned);

            if (!map) {
                map = L.map('map').setView([52.2, 5.77], 7);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '©OpenStreetMap'
                }).addTo(map);
            }

            map.eachLayer((layer) => {
                if (layer && layer.options && layer.options.pane !== 'tilePane') {
                    map.removeLayer(layer);
                }
            });

            if (branchSetting?.latitude && branchSetting?.longitude) {
                const houseIcon = getHouseIconForBranch();
                branchMarker = L.marker([branchSetting.latitude, branchSetting.longitude], { icon: houseIcon })
                    .addTo(map)
                    .bindPopup(`<strong>🏠 Begin/Eindpunt</strong><br>${escapeHtml(branchSetting.branch_address || 'Filiaal')}`)
                    .openPopup();

                map.setView([branchSetting.latitude, branchSetting.longitude], 15);
            }

            document.getElementById('routeLink').href = branchMapsLink();
            btn.onclick = markBranchArrival;
        }

        function getHouseIconForBranch() {
            const svgContent = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50" height="50">
                    <defs>
                        <filter id="shadow-house">
                            <feDropShadow dx="0" dy="3" stdDeviation="5" flood-opacity="0.4"/>
                        </filter>
                    </defs>
                    <!-- House shape -->
                    <path d="M 25 5 L 40 20 L 40 40 Q 40 45 35 45 L 15 45 Q 10 45 10 40 L 10 20 Z" 
                          fill="#fbbf24" filter="url(#shadow-house)" stroke="#92400e" stroke-width="1.5"/>
                    <!-- Roof -->
                    <path d="M 25 5 L 10 20 L 40 20 Z" fill="#f59e0b" stroke="#92400e" stroke-width="1.5"/>
                    <!-- Door -->
                    <rect x="21" y="30" width="8" height="15" fill="#78350f" stroke="#92400e" stroke-width="1"/>
                    <!-- Door handle -->
                    <circle cx="28" cy="37" r="1.5" fill="#fbbf24"/>
                    <!-- Windows -->
                    <rect x="14" y="24" width="5" height="5" fill="#93c5fd" stroke="#92400e" stroke-width="1"/>
                    <rect x="31" y="24" width="5" height="5" fill="#93c5fd" stroke="#92400e" stroke-width="1"/>
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

        async function markBranchArrival() {
            if (branchReturned || !currentSlot?.id) {
                return;
            }

            const btn = document.getElementById('arrivedBtn');
            
            try {
                btn.disabled = true;
                btn.textContent = 'Even geduld...';
                
                const url = `/api/route-planner/time-slots/${currentSlot.id}/return`;
                console.log('Marking branch arrival:', url);
                
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json'}
                });
                
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                const json = await r.json();
                
                console.log('Branch arrival recorded:', json);

                branchReturned = true;
                showBranchLocation();
                
            } catch (err) {
                console.error('Error:', err);
                btn.textContent = '❌ Fout';
                btn.disabled = false;
                setTimeout(() => {
                    btn.textContent = 'AANKOMEN BIJ FILIAAL';
                }, 2000);
            }
        }

        document.getElementById('arrivedBtn').addEventListener('click', async () => {
            if (allStops.length === 0 || currentStopIndex >= allStops.length) {
                await markBranchArrival();
                return;
            }

            const stop = allStops[currentStopIndex];
            const btn = document.getElementById('arrivedBtn');
            
            try {
                btn.disabled = true;
                btn.textContent = 'Even geduld...';
                
                const url = `/api/route-planner/time-slots/${currentSlot.id}/stops/${stop.id}/arrive`;
                console.log('Marking arrival:', url);
                
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json'}
                });
                
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                const json = await r.json();
                
                console.log('Arrival recorded:', json);
                
                // Update stop object with arrival time
                stop.pivot.arrived_at = json.arrived_at;
                
                // Update UI to show "Volgende adres" button
                if (currentStopIndex === allStops.length - 1) {
                    showBranchLocation();
                } else {
                    showStop(currentStopIndex);
                }
                
            } catch (err) {
                console.error('Error:', err);
                btn.textContent = '❌ Fout';
                btn.disabled = false;
                setTimeout(() => {
                    btn.textContent = 'Aangekomen op bestemming';
                }, 2000);
            }
        });

        document.getElementById('nextStopBtn').addEventListener('click', () => {
            showStop(currentStopIndex + 1);
        });

        document.getElementById('finishRouteBtn').addEventListener('click', async () => {
            const btn = document.getElementById('finishRouteBtn');
            try {
                btn.disabled = true;
                btn.textContent = 'Even geduld...';

                const url = `/api/route-planner/time-slots/${currentSlot.id}/finish`;
                const r = await fetch(url, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' } });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const json = await r.json();
                console.log('Slot finished:', json);

                // Redirect back to list after marking finished
                window.location.href = '{{ route('deliver.page') }}';
            } catch (err) {
                console.error('Error finishing slot:', err);
                btn.disabled = false;
                btn.textContent = 'ROUTE AFRONDEN';
                alert('Kon rit niet afronden: ' + (err.message || 'Onbekend'));
            }
        });

        function googleMapsLink(stop) {
            const lat = stop.latitude || '';
            const lng = stop.longitude || '';
            const q = stop.address || stop.name || '';
            if (lat && lng) {
                return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(lat + ',' + lng)}&travelmode=driving`;
            }
            return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(q)}`;
        }

        function branchMapsLink() {
            if (branchSetting?.latitude && branchSetting?.longitude) {
                return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(branchSetting.latitude + ',' + branchSetting.longitude)}&travelmode=driving`;
            }

            return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(branchSetting?.branch_address || 'Filiaal')}`;
        }

        function escapeHtml(v) {
            if (v === null || v === undefined) return '';
            return String(v)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }


        document.getElementById('backBtn').addEventListener('click', () => {
            showStop(currentStopIndex - 1);
        });

        window.addEventListener('load', loadSlot);
    </script>
</body>
</html>
