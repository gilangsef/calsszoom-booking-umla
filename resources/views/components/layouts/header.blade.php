@props(['title'])

<header class="h-24 bg-white/80 backdrop-blur-xl border-b border-border z-30 sticky top-0 px-4 md:px-8">
    <div class="flex items-center justify-between h-full">
        <div class="flex items-center">
            <button @click="sidebarOpen = true" class="p-2.5 mr-4 rounded-xl bg-muted border border-border lg:hidden text-gray-600 active:scale-95 transition-all">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <h1 class="text-2xl font-extrabold text-foreground tracking-tight">{{ $title }}</h1>
        </div>

        <div class="flex items-center gap-4 md:gap-6">
            <div class="hidden md:flex flex-col items-end border-r border-border pr-6">
                <span class="text-xs font-bold text-foreground">{{ now()->translatedFormat('d F Y') }}</span>
                <span class="text-[11px] font-black text-primary uppercase tracking-widest" id="live-clock">--:--:--</span>
            </div>

            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" 
                    class="w-12 h-12 rounded-2xl bg-muted border border-border flex items-center justify-center text-gray-500 hover:text-primary hover:border-primary/50 transition-all cursor-pointer overflow-hidden group">
                    @if(auth()->user()->profile_photo_path)
                        <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        {{-- Icon User Default (Lucide) --}}
                        <i data-lucide="user-circle" class="w-6 h-6 group-hover:scale-110 transition-transform"></i>
                    @endif
                </button>

                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    class="absolute right-0 mt-3 w-56 bg-white border border-border rounded-2xl shadow-xl py-2 z-50 overflow-hidden"
                    style="display: none;">
                    
                    <div class="px-4 py-3 border-b border-border mb-1">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">User Profile</p>
                        <p class="text-sm font-bold text-foreground truncate">{{ auth()->user()->name }}</p>
                    </div>

                    <a href="
                    {{-- {{ route('profile.edit') }} --}}
                    " class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-muted hover:text-primary transition-colors">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Settings
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 transition-colors cursor-pointer text-left">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>