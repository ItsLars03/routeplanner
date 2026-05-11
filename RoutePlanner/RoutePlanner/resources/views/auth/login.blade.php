<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop&Go | Inloggen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 border border-slate-100">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-orange-500 p-2 rounded-xl shadow-lg shadow-orange-500/30">
                <i class="fas fa-truck-fast text-white text-lg"></i>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">Routi<span class="text-orange-500">Go</span></h1>
        </div>

        <h2 class="text-lg font-bold text-slate-700 mb-1">Inloggen planners</h2>
        <p class="text-sm text-slate-500 mb-6">Log in om gebruikers en planningsdata te beheren.</p>

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-medium px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-medium px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">E-mail</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="mt-1 w-full rounded-xl border-2 border-transparent bg-slate-100 px-4 py-2.5 text-sm outline-none focus:border-orange-500/20 focus:bg-white" />
            </div>

            <div>
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Wachtwoord</label>
                <input type="password" name="password" required class="mt-1 w-full rounded-xl border-2 border-transparent bg-slate-100 px-4 py-2.5 text-sm outline-none focus:border-orange-500/20 focus:bg-white" />
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500" />
                Onthoud mij
            </label>

            <button type="submit" class="w-full rounded-xl bg-orange-500 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/30 transition hover:bg-orange-600 active:scale-[0.99]">
                Inloggen
            </button>
        </form>
    </div>
</body>
</html>
