<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Drop&Go | Leveringen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F9FAFB] min-h-screen text-slate-800">
    <div class="flex flex-col min-h-screen">
        <header class="h-16 bg-slate-900 text-white flex items-center px-4 md:px-8 shadow-sm">
            <div class="max-w-6xl mx-auto w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-md bg-orange-500 text-white">
                        <i class="fas fa-truck-ramp-box"></i>
                    </span>
                    <div class="ml-2">
                        <div>
                            <span class="text-xl font-black tracking-tighter text-white uppercase">Drop<span class="text-orange-500">&amp;Go</span></span>
                            @if ($driver)
                                <div class="text-xs text-slate-300">{{ $driver->firstname }} {{ $driver->lastname }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-sm font-semibold text-slate-200">{{ now()->format('d M Y') }}</div>
                    @if ($driver)
                        <form method="POST" action="{{ route('deliver.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-200 bg-white/5 hover:bg-white/10 px-3 py-1 rounded-md transition">Uitloggen</button>
                        </form>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 md:px-8">
            @if ($driver)
                <div class="max-w-6xl mx-auto">
                    <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                            <h2 class="text-xl font-black">Mijn ritten vandaag</h2>
                            <p class="text-xs text-slate-400 mt-1">Bekijk en beheer je toegewezen routes</p>
                        </div>

                        <div class="p-6">
                            <div id="cards" class="space-y-2">
                                <!-- route cards loaded here -->
                            </div>
                            <script>
                                const driverId = {{ $driver->id }};
                                const cardsEl = document.getElementById('cards');

                                async function loadAssignments() {
                                    cardsEl.innerHTML = '<div class="text-center py-6 text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Laden...</div>';
                                    try {
                                        const date = new Date().toISOString().slice(0,10);
                                        const res = await fetch(`/api/route-planner?date=${date}`);
                                        if (!res.ok) throw new Error('Laden mislukt');
                                        const data = await res.json();
                                        const slots = [];
                                        (data.vehicles || []).forEach((vehicle) => {
                                            (vehicle.time_slots || []).forEach((slot) => {
                                                if (slot.driver && Number(slot.driver.id) === Number(driverId)) {
                                                    slots.push({ vehicle, slot });
                                                }
                                            });
                                        });

                                        if (!slots.length) {
                                            cardsEl.innerHTML = '<div class="text-center py-8 text-slate-500"><i class="fas fa-inbox text-2xl mb-2 block"></i><p>Geen ritten vandaag</p></div>';
                                            return;
                                        }

                                        cardsEl.innerHTML = slots.map((s, idx) => `
                                            <a href="/deliver/slot/${s.slot.id}" class="group block p-3 rounded-2xl border border-slate-200 hover:border-orange-200 hover:bg-orange-50 transition-all duration-200 active:bg-orange-100${s.slot.finished_at ? ' opacity-60 pointer-events-none' : ''}">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600 font-bold text-sm">
                                                        ${idx + 1}
                                                    </div>
                                                                <div class="min-w-0 flex-1">
                                                                    <div class="flex items-baseline justify-between gap-2">
                                                                        <p class="text-sm font-semibold text-slate-900 truncate">${escapeHtml(s.vehicle.name || 'Voertuig')}</p>
                                                                        <span class="text-xs font-medium text-slate-500 whitespace-nowrap bg-slate-100 px-2 py-0.5 rounded-full">${s.slot.stops?.length || 0} stops</span>
                                                                    </div>
                                                                    <div class="flex items-center gap-2 mt-1 text-xs text-slate-600">
                                                                        <i class="fas fa-clock w-3 text-slate-400"></i>
                                                                        <span>${escapeHtml(s.slot.start_time || '-')} - ${escapeHtml(s.slot.end_time || '-')}</span>
                                                                        ${s.slot.finished_at ? `<span class="ml-2 inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-xs font-semibold"><i class="fas fa-check"></i>${new Date(s.slot.finished_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})}</span>` : ''}
                                                                    </div>
                                                                </div>
                                                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-orange-500 group-hover:translate-x-1 transition-all"></i>
                                                </div>
                                            </a>
                                        `).join('');

                                    } catch (err) {
                                        cardsEl.innerHTML = `<div class="text-red-600 text-sm p-3 bg-red-50 rounded-lg border border-red-200">Fout: ${escapeHtml(err.message || 'Onbekend')}</div>`;
                                    }
                                }

                                function escapeHtml(v){ if (v===null||v===undefined) return ''; return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

                                loadAssignments();
                            </script>
                        </div>
                    </section>
                </div>

            @else
                <div class="max-w-md mx-auto mt-12">
                    <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                            <h2 class="text-xl font-black">Inloggen</h2>
                            <p class="text-xs text-slate-400 mt-1">Chauffeur portal</p>
                        </div>

                        <div class="p-6">
                            @if ($errors->any())
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm font-semibold">{{ $errors->first() }}</div>
                            @endif
                            <form method="POST" action="{{ route('deliver.login') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">E-mailadres</label>
                                    <input name="email" type="email" required placeholder="naam@bedrijf.nl" class="mt-1 w-full bg-slate-100 border-2 border-transparent focus:border-orange-500/20 focus:bg-white rounded-xl px-4 py-2.5 text-sm outline-none transition-all" value="{{ old('email') }}">
                                </div>
                                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white rounded-xl px-4 py-2.5 text-sm font-bold shadow-lg shadow-orange-500/20 transition-all active:scale-95">Inloggen</button>
                            </form>
                        </div>
                    </section>
                </div>
            @endif
        </main>
    </div>
</body>
</html>
