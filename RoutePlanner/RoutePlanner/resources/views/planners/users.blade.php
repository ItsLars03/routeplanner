<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Planner Gebruikers</title>
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
    </style>
</head>
<body class="bg-[#F9FAFB] flex h-screen overflow-hidden text-slate-800">
    @include('partials.sidebar', ['active' => 'planners'])

    <div class="flex-1 flex flex-col min-w-0 relative overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 z-20 shadow-sm">
            <div class="flex items-center gap-4 md:gap-6">
                <div class="bg-orange-50 px-4 py-2 rounded-2xl border border-orange-100 flex items-center gap-3">
                    <i class="fas fa-users text-orange-600 text-sm"></i>
                    <span class="text-sm font-bold text-slate-700">Planner Gebruikersbeheer</span>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-medium px-4 py-3">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-medium px-4 py-3">{{ $errors->first() }}</div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <section class="xl:col-span-1 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                        <h2 class="text-xl font-black">{{ $editUser ? 'Account bewerken' : 'Nieuwe planner' }}</h2>
                        <p class="text-xs text-slate-400 mt-1">Maak planner-accounts aan of wijzig bestaande accounts.</p>
                    </div>

                    <form method="POST" action="{{ $editUser ? route('planners.users.update', $editUser) : route('planners.users.store') }}" class="p-6 space-y-4">
                        @csrf
                        @if($editUser)
                            @method('PUT')
                        @endif

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Naam</label>
                            <input type="text" name="name" required value="{{ old('name', $editUser->name ?? '') }}" class="mt-1 w-full rounded-xl border-2 border-transparent bg-slate-100 px-4 py-2.5 text-sm outline-none focus:border-orange-500/20 focus:bg-white" />
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">E-mail</label>
                            <input type="email" name="email" required value="{{ old('email', $editUser->email ?? '') }}" class="mt-1 w-full rounded-xl border-2 border-transparent bg-slate-100 px-4 py-2.5 text-sm outline-none focus:border-orange-500/20 focus:bg-white" />
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Wachtwoord {{ $editUser ? '(leeg laten = ongewijzigd)' : '' }}</label>
                            <input type="password" name="password" {{ $editUser ? '' : 'required' }} class="mt-1 w-full rounded-xl border-2 border-transparent bg-slate-100 px-4 py-2.5 text-sm outline-none focus:border-orange-500/20 focus:bg-white" />
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Bevestig wachtwoord</label>
                            <input type="password" name="password_confirmation" {{ $editUser ? '' : 'required' }} class="mt-1 w-full rounded-xl border-2 border-transparent bg-slate-100 px-4 py-2.5 text-sm outline-none focus:border-orange-500/20 focus:bg-white" />
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="submit" class="flex-1 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600">
                                {{ $editUser ? 'Opslaan' : 'Account aanmaken' }}
                            </button>

                            @if($editUser)
                                <a href="{{ route('planners.users.index') }}" class="flex-1 rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                                    Annuleren
                                </a>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                        <h2 class="text-xl font-black">Planner accounts</h2>
                        <p class="text-xs text-slate-400 mt-1">{{ $users->count() }} account(s)</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wide text-xs">
                                <tr>
                                    <th class="text-left px-4 py-3">Naam</th>
                                    <th class="text-left px-4 py-3">E-mail</th>
                                    <th class="text-left px-4 py-3">Aangemaakt</th>
                                    <th class="text-left px-4 py-3">Acties</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($users as $user)
                                    <tr class="hover:bg-orange-50/40 transition">
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $user->name }}</td>
                                        <td class="px-4 py-3">{{ $user->email }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $user->created_at?->format('d-m-Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('planners.users.index', ['edit' => $user->id]) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Bewerk</a>
                                                <form method="POST" action="{{ route('planners.users.destroy', $user) }}" onsubmit="return confirm('Weet je zeker dat je dit account wilt verwijderen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition">Verwijder</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-400 font-medium">Nog geen planner accounts gevonden.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
