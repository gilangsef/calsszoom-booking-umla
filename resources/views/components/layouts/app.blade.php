<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Jelz UI RoomEase</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .shadow-jelz { shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-white min-h-screen overflow-x-hidden antialiased text-foreground">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">
        
        <x-layouts.sidebar />

        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
            <x-layouts.header :title="$title ?? 'Dashboard'" />

            <main class="flex-1 p-4 md:p-8">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{-- Mobile Overlay --}}
        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 bg-foreground/60 backdrop-blur-sm z-40 lg:hidden">
        </div>
    </div>

    @livewireScripts
    {{-- Script Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        // Fungsi stand-alone untuk inisialisasi icon
        function initIcons() {
            lucide.createIcons();
        }

        // Jalankan saat halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', initIcons);

        // PENTING: Jalankan setiap kali Livewire melakukan navigasi (SPA mode)
        document.addEventListener('livewire:navigated', initIcons);

        // Menangani refresh manual dari event Livewire jika dibutuhkan
        document.addEventListener('livewire:init', () => {
           Livewire.on('refresh-icons', () => {
               setTimeout(initIcons, 50);
           });

           // Opsional: Re-init otomatis setiap kali ada komponen yang berubah (Morph)
           Livewire.hook('morph.updated', (el, component) => {
               initIcons();
           });
        });
    </script>
</body>
</html>