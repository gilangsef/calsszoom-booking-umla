<div x-show="openUser"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[999] flex items-center justify-center p-4">

    <div @click="openUser = false"
         class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div x-show="openUser"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="relative bg-white dark:bg-gray-900 w-full max-w-2xl rounded-3xl shadow-xl overflow-hidden z-[1000] border border-gray-100 dark:border-gray-800">

        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-800 flex justify-between items-center bg-white dark:bg-gray-900">
            <h3 class="text-base font-bold flex items-center gap-2.5 text-gray-800 dark:text-white">
                <div class="p-1.5 bg-indigo-600 rounded-lg text-white">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                </div>
                Panduan Student & Dosen
            </h3>
            <button @click="openUser = false" type="button" class="cursor-pointer p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors text-gray-400">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="px-8 py-6 max-h-[55vh] overflow-y-auto">
            <div class="space-y-6">

                <div class="flex gap-4">
                    <div class="shrink-0 w-7 h-7 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold border border-indigo-100">1</div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Booking Fasilitas</h4>
                        <p class="text-[12px] text-gray-500 leading-relaxed">Pilih menu **Ruang Kelas** atau **Zoom Meeting**, tentukan jadwal, lalu tunggu verifikasi Admin di dashboard Anda.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="shrink-0 w-7 h-7 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold border border-indigo-100">2</div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Prosedur Kunci (Khusus Kelas)</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex items-start gap-2.5 text-[11px] bg-gray-50 dark:bg-gray-800/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                                <i data-lucide="scan-line" class="w-3.5 h-3.5 text-indigo-500 mt-0.5"></i>
                                <span>Tunjukkan **QR Code** di bukti booking kepada petugas untuk ambil/kembali kunci.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="shrink-0 w-7 h-7 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-xs font-bold border border-indigo-100">3</div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Akses Zoom Meeting</h4>
                        <p class="text-[12px] text-gray-500 leading-relaxed">Setelah disetujui, detail **Email & Password** akun Zoom akan muncul otomatis di halaman booking Anda (Tanpa QR Code).</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="px-8 py-4 border-t border-gray-50 dark:border-gray-800 flex justify-end bg-gray-50/30 dark:bg-gray-800/20">
            <button @click="openUser = false"
                    type="button"
                    class="cursor-pointer px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold text-[11px] uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-95 shadow-sm">
                Mengerti
            </button>
        </div>
    </div>
</div>
