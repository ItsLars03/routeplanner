<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Tijdsloten</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active { @apply bg-orange-500/20 text-orange-400; }
        .pagination a, .pagination span { @apply px-2 py-1 text-xs font-bold rounded text-slate-400 hover:text-orange-400; }
        .pagination .active span { @apply bg-orange-500 text-white; }
    </style>
</head>
<body class="bg-slate-900">
    <div class="flex h-screen">
        @include('partials.sidebar', ['active' => 'timeslots'])
        <div class="flex-1 overflow-y-auto p-6">
            <div class="max-w-6xl">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-black text-white mb-2">Tijdsloten Management</h1>
                    <p class="text-slate-400">Beheer hier alleen de globale toegestane tijdsblokken voor routes</p>
                </div>

                <!-- Add Timeslot Form -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 mb-8 shadow-lg">
                    <h2 class="text-xl font-bold text-white mb-6">Nieuw toegestaan tijdslot</h2>

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-red-900/30 border border-red-700">
                            <p class="text-sm font-bold text-red-300 mb-2">Fout bij het toevoegen:</p>
                            @foreach ($errors->all() as $error)
                                <p class="text-sm text-red-400">• {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-6 p-4 rounded-xl bg-emerald-900/30 border border-emerald-700">
                            <p class="text-sm font-bold text-emerald-300">✓ {{ session('success') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('timeslots.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-300 mb-2">Slot key</label>
                            <input type="text" name="slot_key" required class="w-full rounded-xl border border-slate-600 bg-slate-700 text-white px-3 py-2 text-sm" placeholder="06_08">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-300 mb-2">Starttijd</label>
                            <input type="time" name="start_time" required class="w-full rounded-xl border border-slate-600 bg-slate-700 text-white px-3 py-2 text-sm">
                        </div>

                        <div class="flex items-end">
                            <div class="w-full">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-300 mb-2">Eindtijd</label>
                                <input type="time" name="end_time" required class="w-full rounded-xl border border-slate-600 bg-slate-700 text-white px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-300 mb-2">Volgorde</label>
                            <input type="number" name="sort_order" min="0" value="0" class="w-full rounded-xl border border-slate-600 bg-slate-700 text-white px-3 py-2 text-sm">
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-bold transition shadow-sm">
                                Toevoegen
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Timeslots Table -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-900 border-b border-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Slot key</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Start</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Einde</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Actief</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-400">Volgorde</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-400">Acties</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                @forelse ($allTimeSlots as $slot)
                                    <tr class="hover:bg-slate-700/50 transition">
                                        <td class="px-6 py-4 text-sm font-semibold text-white">{{ $slot->slot_key }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-300">{{ \Illuminate\Support\Str::of($slot->start_time)->substr(0, 5) }}</td>
                                        <td class="px-6 py-4 text-sm text-slate-300">{{ \Illuminate\Support\Str::of($slot->end_time)->substr(0, 5) }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            @if ($slot->is_active)
                                                <span class="inline-block px-3 py-1 bg-emerald-900/40 text-emerald-300 rounded-full text-xs font-bold border border-emerald-700/50">Ja</span>
                                            @else
                                                <span class="inline-block px-3 py-1 bg-slate-700 text-slate-300 rounded-full text-xs font-bold">Nee</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-300">{{ $slot->sort_order }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('timeslots.destroy', $slot) }}" method="POST" onsubmit="return confirm('Zeker?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-400 hover:text-red-300 hover:underline transition">
                                                    Verwijder
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">
                                            Geen toegestane tijdsloten gevonden. Voeg een nieuw slot toe.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($allTimeSlots->hasPages())
                        <div class="px-6 py-4 border-t border-slate-700">
                            {{ $allTimeSlots->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
