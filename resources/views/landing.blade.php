<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM Class & Zoom - Universitas Muhammadiyah Lampung</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; }

        /* Gradien Hero yang lebih soft dan elegan */
        .hero-bg {
            background: linear-gradient(to bottom right, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.3)), url('{{ asset("images/bgumlaclass.webp") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        i[data-lucide] { display: inline-block; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 bg-slate-50/50 dark:bg-slate-950">

    <div class="fixed w-full z-50 top-6 px-4 md:px-6" x-data="{ mobileMenuOpen: false }">
        <nav class="w-full lg:max-w-7xl mx-auto bg-white/70 dark:bg-slate-900/70 backdrop-blur-2xl border border-white/50 dark:border-slate-800/50 px-6 h-16 flex items-center justify-between rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all">

            <div class="flex items-center gap-3">
                <img src="{{ asset('images/umlabooking.png') }}" alt="UML Logo" class="h-8 w-auto">
                <span class="font-semibold text-sm tracking-wide text-slate-800 dark:text-white">SIM CLASS UMLA</span>
            </div>

            <div class="hidden md:flex items-center gap-8 text-[11px] font-semibold tracking-widest text-slate-500 uppercase">
                <a href="#prosedur" class="hover:text-indigo-600 transition-colors flex items-center gap-1">Prosedur</a>
                <a href="#peraturan" class="hover:text-indigo-600 transition-colors flex items-center gap-1">Aturan</a>
                <a href="{{ route('filament.admin.auth.login') }}"
                class="bg-gradient-to-r from-indigo-500 to-blue-500 text-white px-6 py-2.5 rounded-xl text-[11px] font-bold hover:shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition-all duration-300">
                    LOGIN
                </a>
            </div>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-500 outline-none hover:text-indigo-600 transition">
                <i data-lucide="menu" class="w-5 h-5" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-5 h-5" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </nav>

        <div id="mobile-menu"
            x-show="mobileMenuOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="mt-2 w-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-white/50 rounded-2xl shadow-xl p-4 md:hidden">
            <div class="flex flex-col gap-2 text-[11px] font-semibold tracking-widest text-center text-slate-500 uppercase">
                <a href="#prosedur" @click="mobileMenuOpen = false" class="py-3 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition">Prosedur</a>
                <a href="#peraturan" @click="mobileMenuOpen = false" class="py-3 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition">Aturan</a>
                <a href="{{ route('filament.admin.auth.login') }}" class="py-3 mt-2 bg-gradient-to-r from-indigo-500 to-blue-500 text-white rounded-xl shadow-md">LOGIN</a>
            </div>
        </div>
    </div>

    <section class="relative h-screen flex items-center justify-center hero-bg text-white px-6 text-center">
        <div class="max-w-4xl pt-12">
            <span class="px-5 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-[10px] font-semibold uppercase tracking-[0.3em] mb-8 inline-block shadow-sm">
                UMLA Global University
            </span>
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-6 leading-[1.1]">
                Classzoom <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-indigo-300">Booking</span><br>System
            </h1>
            <p class="text-slate-200 text-base md:text-lg max-w-2xl mx-auto mb-10 font-light leading-relaxed">
                Manajemen peminjaman ruang kelas dan lisensi Zoom Meeting Universitas Muhammadiyah Lamongan secara modern dan terintegrasi.
            </p>
            <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-slate-900 rounded-2xl font-semibold hover:bg-indigo-50 hover:scale-105 transition-all duration-300 shadow-[0_0_40px_rgba(255,255,255,0.2)]">
                <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                Booking Sekarang
            </a>
        </div>
    </section>

    <section id="prosedur" x-data="{ openOperasional: false, openUser: false }" class="py-24 px-6 relative">
        <div class="max-w-7xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-800 dark:text-white">Prosedur Peminjaman</h2>
            <div class="h-1 w-16 bg-gradient-to-r from-indigo-500 to-blue-400 mx-auto mt-6 rounded-full opacity-80"></div>
        </div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="p-8 text-center rounded-3xl border border-slate-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300 group">
                 <div class="w-14 h-14 bg-slate-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-indigo-50 transition-colors">
                    <i data-lucide="user-plus" class="w-6 h-6 stroke-[1.5]"></i>
                </div>
                <h4 class="font-semibold text-slate-800 text-lg mb-2">1. Autentikasi</h4>
                <p class="text-sm text-slate-500 font-light leading-relaxed">Login menggunakan NIM atau NIP resmi Anda.</p>
            </div>

            <div class="p-8 text-center rounded-3xl border border-slate-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300 group">
                <div class="w-14 h-14 bg-slate-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-indigo-50 transition-colors">
                    <i data-lucide="clipboard-list" class="w-6 h-6 stroke-[1.5]"></i>
                </div>
                <h4 class="font-semibold text-slate-800 text-lg mb-2">2. Input Data</h4>
                <p class="text-sm text-slate-500 font-light leading-relaxed">Pilih fasilitas dan tentukan jadwal kegiatan Anda.</p>
            </div>

            <div class="p-8 text-center rounded-3xl border border-slate-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:-translate-y-1 transition-transform duration-300 group">
                <div class="w-14 h-14 bg-slate-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-indigo-50 transition-colors">
                    <i data-lucide="shield-check" class="w-6 h-6 stroke-[1.5]"></i>
                </div>
                <h4 class="font-semibold text-slate-800 text-lg mb-2">3. Validasi</h4>
                <p class="text-sm text-slate-500 font-light leading-relaxed">Admin akan mereview dan memberikan persetujuan.</p>
            </div>

            <div class="p-8 text-center rounded-3xl border border-indigo-100 bg-gradient-to-b from-indigo-50/50 to-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex flex-col justify-between">
                <div>
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-blue-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-200">
                        <i data-lucide="book-open" class="w-6 h-6 stroke-[1.5]"></i>
                    </div>
                    <h4 class="font-semibold text-slate-800 text-lg mb-6 leading-tight">Buku Panduan</h4>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="button" @click="openOperasional = true" class="w-full inline-flex items-center justify-center gap-2 bg-white text-slate-600 border border-slate-200 py-2.5 px-4 rounded-xl text-xs font-semibold hover:border-indigo-200 hover:text-indigo-600 transition-all">
                        <i data-lucide="book-open" class="w-4 h-4 stroke-[1.5]"></i> Admin / Ops
                    </button>
                    <button type="button" @click="openUser = true" class="w-full inline-flex items-center justify-center gap-2 bg-slate-800 text-white py-2.5 px-4 rounded-xl text-xs font-semibold hover:bg-slate-700 transition-all shadow-md">
                        <i data-lucide="file-text" class="w-4 h-4 stroke-[1.5]"></i> Student / Dosen
                    </button>
                </div>
            </div>
        </div>

        @include('components.modal-operasional')
        @include('components.modal-studentdosen')
    </section>

    <section id="peraturan" class="py-24 px-6 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-white border border-slate-100 shadow-sm text-indigo-500 rounded-xl">
                        <i data-lucide="door-closed" class="w-6 h-6 stroke-[1.5]"></i>
                    </div>
                    <h3 class="text-xl font-bold tracking-tight text-slate-800">Aturan Kelas</h3>
                </div>
                <div class="space-y-4">
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex gap-4 text-sm hover:border-indigo-100 transition-colors">
                        <i data-lucide="check-circle-2" class="text-indigo-400 w-5 h-5 shrink-0"></i>
                        <span class="text-slate-600 font-light">Pengajuan maksimal 24 jam sebelum pemakaian.</span>
                    </div>
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex gap-4 text-sm hover:border-indigo-100 transition-colors">
                        <i data-lucide="check-circle-2" class="text-indigo-400 w-5 h-5 shrink-0"></i>
                        <span class="text-slate-600 font-light">Wajib menjaga fasilitas dan kebersihan ruangan.</span>
                    </div>
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex gap-4 text-sm hover:border-indigo-100 transition-colors">
                        <i data-lucide="check-circle-2" class="text-indigo-400 w-5 h-5 shrink-0"></i>
                        <span class="text-slate-600 font-light">Mematikan seluruh perangkat elektronik setelah selesai.</span>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-white border border-slate-100 shadow-sm text-blue-500 rounded-xl">
                        <i data-lucide="video" class="w-6 h-6 stroke-[1.5]"></i>
                    </div>
                    <h3 class="text-xl font-bold tracking-tight text-slate-800">Aturan Zoom</h3>
                </div>
                <div class="space-y-4">
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex gap-4 text-sm hover:border-blue-100 transition-colors">
                        <i data-lucide="check-circle-2" class="text-blue-400 w-5 h-5 shrink-0"></i>
                        <span class="text-slate-600 font-light">Dilarang mengubah profil akun dan password lisensi.</span>
                    </div>
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex gap-4 text-sm hover:border-blue-100 transition-colors">
                        <i data-lucide="check-circle-2" class="text-blue-400 w-5 h-5 shrink-0"></i>
                        <span class="text-slate-600 font-light">Hanya digunakan untuk kepentingan akademik kampus.</span>
                    </div>
                    <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex gap-4 text-sm hover:border-blue-100 transition-colors">
                        <i data-lucide="check-circle-2" class="text-blue-400 w-5 h-5 shrink-0"></i>
                        <span class="text-slate-600 font-light">Wajib logout setelah durasi peminjaman berakhir.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-950 text-slate-400 py-16 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <img src="{{ asset('images/umlabooking.png') }}" alt="Logo" class="h-8 w-auto brightness-200 opacity-90">
                    <div class="flex flex-col">
                        <span class="text-white font-semibold text-sm tracking-wide">UMLA SIM CLASS</span>
                    </div>
                </div>
                <p class="text-sm font-light leading-relaxed opacity-80 pr-4">
                    Inovasi layanan kampus digital untuk mempermudah civitas akademika dalam pengelolaan fasilitas secara mandiri.
                </p>
            </div>

            <div>
                <h4 class="font-semibold text-white text-xs uppercase tracking-widest mb-6">Bantuan</h4>
                <div class="space-y-4 text-sm font-light">
                    <p class="flex items-center gap-3"><i data-lucide="mail" class="text-slate-500 w-4 h-4"></i> it-support@uml.ac.id</p>
                    <p class="flex items-center gap-3"><i data-lucide="phone" class="text-slate-500 w-4 h-4"></i> 08xx-xxxx-xxxx</p>
                </div>
            </div>

            <div>
                <h4 class="font-semibold text-white text-xs uppercase tracking-widest mb-6">Tautan Utama</h4>
                <ul class="text-sm font-light space-y-4">
                    <li><a href="{{ route('filament.admin.auth.login') }}" class="hover:text-white transition-colors">Portal Login</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Website Resmi UMLA</a></li>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-8 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs font-light text-slate-500 text-center">
                &copy; {{ date('Y') }} Universitas Muhammadiyah Lamongan.
            </p>
            <div class="flex gap-5">
                <a href="#" class="text-slate-500 hover:text-white transition"><i data-lucide="facebook" class="w-4 h-4 stroke-[1.5]"></i></a>
                <a href="#" class="text-slate-500 hover:text-white transition"><i data-lucide="instagram" class="w-4 h-4 stroke-[1.5]"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // Inisialisasi ikon
        lucide.createIcons();
    </script>
</body>
</html>
