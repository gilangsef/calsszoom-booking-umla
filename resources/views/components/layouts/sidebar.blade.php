@php
    $user = auth()->user();
    $roleName = strtolower($user->getRoleNames()->first() ?? 'user');
    $dashRoute = Route::has($roleName . '.dashboard') ? $roleName . '.dashboard' : 'dashboard';
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-muted border-r border-border transform transition-transform duration-300 lg:translate-x-0 flex flex-col shadow-xl lg:shadow-none">
    
    {{-- MODIFIKASI HEADER DISINI --}}
    <div class="px-6 h-24 flex items-center justify-between"> {{-- Tambah justify-between --}}
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center shadow-lg shadow-primary/20">
                <i data-lucide="building-2" class="w-6 h-6 text-white"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tighter text-foreground">Room<span class="text-primary">Ease</span></span>
        </div>

        {{-- TOMBOL CLOSE: Hanya muncul di mobile (lg:hidden) --}}
        <button @click="sidebarOpen = false" class="lg:hidden p-2 hover:bg-white rounded-xl transition-colors text-gray-400 hover:text-accent-red cursor-pointer border border-transparent hover:border-border">
            <i data-lucide="x" class="w-6 h-6"></i>
        </button>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
        <x-layouts.nav-link :href="route($dashRoute)" icon="layout-grid" :active="request()->routeIs('*.dashboard')">
            Dashboard
        </x-layouts.nav-link>

        {{-- Role Operasional --}}
        @if(str_contains($roleName, 'opera'))
            <div class="px-4 pt-6 pb-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Layanan</div>
            <x-layouts.nav-link :href="route('operasional.scan')" icon="qr-code" :active="request()->routeIs('operasional.scan*')">Scan QR</x-layouts.nav-link>
            <x-layouts.nav-link :href="route('operasional.bookings')" icon="calendar-check" :active="request()->routeIs('operasional.bookings*')">Peminjaman</x-layouts.nav-link>
            <x-layouts.nav-link :href="route('operasional.damage-reports')" icon="alert-triangle" :active="request()->routeIs('operasional.damage-reports*')">Kerusakan</x-layouts.nav-link>
        @endif

        {{-- Role Student --}}
        @if($roleName === 'student')
            <div class="px-4 pt-6 pb-2 text-[10px] font-black text-gray-400 uppercase tracking-widest">Mahasiswa</div>
            <x-layouts.nav-link :href="route('student.bookings')" icon="door-open" :active="request()->routeIs('student.bookings*')">Booking Kelas</x-layouts.nav-link>
            <x-layouts.nav-link :href="route('student.zoom-bookings')" icon="video" :active="request()->routeIs('student.zoom-bookings*')">Booking Zoom</x-layouts.nav-link>
        @endif
    </nav>

    <div class="p-4 bg-white/50 border-t border-border">
        <div class="flex items-center gap-3 p-3 rounded-2xl bg-white border border-border shadow-sm mb-3">
            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white font-black text-sm">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="overflow-hidden leading-tight">
                <p class="text-sm font-bold text-foreground truncate">{{ $user->name }}</p>
                <p class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $roleName }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm font-bold text-gray-500 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all group">
                <i data-lucide="log-out" class="w-5 h-5 mr-3 text-gray-400 group-hover:text-red-500"></i>
                Keluar
            </button>
        </form>
    </div>
</aside>