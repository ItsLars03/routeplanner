@php
    $active = $active ?? '';
@endphp

<aside class="w-64 bg-[#111827] text-slate-400 flex-col flex-shrink-0 z-30 shadow-2xl hidden md:flex">
    <div class="p-6 flex items-center gap-3">
        <div class="bg-orange-500 p-2 rounded-xl shadow-lg shadow-orange-900/40">
            <i class="fas fa-truck-fast text-white text-lg"></i>
        </div>
        <span class="text-xl font-black tracking-tighter text-white uppercase">Drop<span class="text-orange-500">&Go</span></span>
    </div>

    <nav class="flex-1 overflow-y-auto pt-2 space-y-1 px-3">
        <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-3">Dashboard</p>

        <a href="/routes" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'routes' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-route w-5 text-center {{ $active === 'routes' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'routes' ? 'font-semibold' : 'font-medium' }}">Routes tonen</span>
        </a>

        <a href="{{ route('deliver.page') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'deliver' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fa-solid fa-truck-ramp-box w-5 text-center {{ $active === 'deliver' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'deliver' ? 'font-semibold' : 'font-medium' }}">Drop&Go Deliver</span>
        </a>

        <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-8 mb-3">Beheer</p>

        <a href="/vehicles" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'vehicles' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-truck w-5 text-center {{ $active === 'vehicles' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'vehicles' ? 'font-semibold' : 'font-medium' }}">Vehicles beheren</span>
        </a>

        <a href="/drivers" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'drivers' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-id-card w-5 text-center {{ $active === 'drivers' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'drivers' ? 'font-semibold' : 'font-medium' }}">Drivers beheren</span>
        </a>

        <a href="/damages" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'damages' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-car-crash w-5 text-center {{ $active === 'damages' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'damages' ? 'font-semibold' : 'font-medium' }}">Schade rapporten</span>
        </a>

        <a href="{{ route('planners.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'planners' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-users w-5 text-center {{ $active === 'planners' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'planners' ? 'font-semibold' : 'font-medium' }}">Planner gebruikers</span>
        </a>

        <a href="{{ route('timeslots.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'timeslots' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-calendar-days w-5 text-center {{ $active === 'timeslots' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'timeslots' ? 'font-semibold' : 'font-medium' }}">Tijdsloten</span>
        </a>

        <a href="{{ route('settings.page') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all {{ $active === 'settings' ? 'sidebar-item-active' : 'hover:bg-white/5 hover:text-orange-400 group' }}">
            <i class="fas fa-gear w-5 text-center {{ $active === 'settings' ? 'text-orange-500' : 'group-hover:scale-110 transition' }}"></i>
            <span class="text-[13px] {{ $active === 'settings' ? 'font-semibold' : 'font-medium' }}">Instellingen</span>
        </a>
    </nav>

    <div class="p-4 bg-black/20">
        <div class="bg-slate-800/40 rounded-2xl p-4 flex items-center gap-3 border border-white/5">
            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-orange-600 to-amber-400 flex items-center justify-center text-white font-bold text-sm">R</div>
            <div class="flex-1 min-w-0">
                <p class="text-[12px] text-white font-bold truncate">{{ auth()->user()->email ?? 'planner@dropandgo.nl' }}</p>
                <p class="text-[10px] text-orange-500/80 font-medium">Planner User</p>
            </div>
        </div>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 text-xs font-bold transition">
                    Uitloggen
                </button>
            </form>
        @endauth
    </div>
</aside>
