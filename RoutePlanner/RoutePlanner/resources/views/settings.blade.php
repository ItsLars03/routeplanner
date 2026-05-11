<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Instellingen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active {
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.18) 0%, rgba(245, 158, 11, 0) 100%);
            border-left: 4px solid #f59e0b;
            color: #fff;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100">
    <div class="flex min-h-screen">
        @include('partials.sidebar', ['active' => 'settings'])

        <main class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="max-w-4xl">
                <div class="mb-8">
                    <h1 class="text-3xl font-black text-white mb-2">Instellingen</h1>
                    <p class="text-slate-400">Stel hier het startadres van het filiaal in. Dit adres wordt gebruikt als begin- en eindpunt van elke rit.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-700 bg-red-900/30 p-4">
                        <p class="mb-2 text-sm font-bold text-red-300">Er is iets misgegaan:</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-200">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-700 bg-emerald-900/30 p-4">
                        <p class="text-sm font-bold text-emerald-300">✓ {{ session('success') }}</p>
                    </div>
                @endif

                <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                    <section class="rounded-3xl border border-slate-700 bg-slate-800 p-6 shadow-2xl">
                        <h2 class="mb-4 text-xl font-bold text-white">Filiaal startadres</h2>

                        <form action="{{ route('settings.update') }}" method="POST" class="space-y-5">
                            @csrf

                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-400">Startadres</label>
                                <input
                                    type="text"
                                    name="branch_address"
                                    value="{{ old('branch_address', $branchSetting->branch_address) }}"
                                    placeholder="Bijv. Kanaalweg 12, 8862 PG Harlingen"
                                    class="w-full rounded-2xl border border-slate-600 bg-slate-900 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-orange-500 focus:outline-none"
                                    required
                                >
                                <p class="mt-2 text-xs text-slate-400">De bezorger start hier altijd en komt hier ook weer terug.</p>
                            </div>

                            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                                <i class="fas fa-save"></i>
                                Opslaan
                            </button>
                        </form>
                    </section>

                    <aside class="rounded-3xl border border-slate-700 bg-slate-800 p-6 shadow-2xl">
                        <h2 class="mb-4 text-xl font-bold text-white">Huidige locatie</h2>

                        <div class="space-y-4 text-sm text-slate-300">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Adres</p>
                                <p class="mt-1 font-semibold text-white">{{ $branchSetting->branch_address ?: 'Nog niet ingesteld' }}</p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Coördinaten</p>
                                <p class="mt-1 font-semibold text-white">
                                    @if ($branchSetting->latitude && $branchSetting->longitude)
                                        {{ $branchSetting->latitude }}, {{ $branchSetting->longitude }}
                                    @else
                                        Nog niet bekend
                                    @endif
                                </p>
                            </div>

                            <div class="rounded-2xl border border-slate-700 bg-slate-900/60 p-4 text-slate-400">
                                Deze locatie wordt automatisch toegevoegd als begin- en eindpunt van alle geplande routes.
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </main>
    </div>
</body>
</html>