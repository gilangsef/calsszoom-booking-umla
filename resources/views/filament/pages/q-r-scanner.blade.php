<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        {{-- Area Scanner (Kiri) --}}
        <div class="md:col-span-7">
            <x-filament::section>
                <x-slot name="heading">Kamera Scanner</x-slot>
                <x-slot name="description">Arahkan QR Code ke kamera untuk memproses kunci secara otomatis.</x-slot>

                <div class="flex flex-col items-center">
                    <div wire:ignore class="w-full max-w-sm overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shadow-inner">
                        <div id="qr-reader" class="w-full"></div>
                    </div>

                    <div class="mt-4 flex items-center gap-2 text-sm text-gray-500 italic">
                        <x-filament::loading-indicator wire:loading class="h-4 w-4" />
                        <span wire:loading>Memproses data...</span>
                        <span wire:loading.remove>Status: Kamera Aktif (Scanner Siap)</span>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Area Info & Manual (Kanan) --}}
        <div class="md:col-span-5 space-y-6">

            {{-- Hasil Scan Terakhir --}}
            @if($lastBooking)
                <x-filament::section icon="heroicon-o-check-circle" icon-color="success">
                    <x-slot name="heading">Data Berhasil Diproses</x-slot>

                    <div class="space-y-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Jenis Aksi</span>
                            <span class="text-xl font-black text-primary-600 tracking-tight">{{ $lastBooking['action'] }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t border-gray-100 dark:border-gray-800 pt-4">
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">Ruangan</span>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $lastBooking['room'] }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase font-bold">Peminjam</span>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $lastBooking['user'] }}</p>
                            </div>
                        </div>

                        <div class="bg-primary-50 dark:bg-primary-950/30 p-3 rounded-xl text-center border border-primary-100 dark:border-primary-900">
                            <span class="text-[10px] text-primary-600 dark:text-primary-400 block mb-1 font-bold uppercase">Status Terbaru</span>
                            <x-filament::badge color="success" size="lg">
                                {{ $lastBooking['status'] }}
                            </x-filament::badge>
                        </div>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                    <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
                        <div class="relative mb-3">
                            <x-heroicon-o-qr-code class="h-16 w-16 opacity-10" />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-8 h-0.5 bg-primary-500/20 animate-bounce"></div>
                            </div>
                        </div>
                        <p class="text-sm italic">Menunggu scan pertama...</p>
                    </div>
                </x-filament::section>
            @endif

            {{-- Input Manual --}}
            <x-filament::section collapsible collapsed>
                <x-slot name="heading">Input Kode Manual</x-slot>
                <div class="space-y-3">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            id="manualInput"
                            placeholder="Contoh: BK-XXXXXXX"
                            class="font-mono"
                        />
                    </x-filament::input.wrapper>

                    <x-filament::button color="gray" onclick="sendManual()" class="w-full shadow-sm">
                        Proses Kode Manual
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let html5QrCode = null;
        let isProcessing = false;

        // --- KONFIGURASI AUDIO BEEP ---
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        const audioCtx = new AudioCtx();

        function playBeep(frequency, duration, volume) {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(volume, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration/1000);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + duration/1000);
        }

        // Listener Suara dari Livewire
        window.addEventListener('play-beep-success', () => {
            playBeep(880, 200, 0.1); // Suara Tinggi (High-pitch Tut)
            if (navigator.vibrate) navigator.vibrate(200);
        });

        window.addEventListener('play-beep-error', () => {
            playBeep(220, 150, 0.2); // Suara Rendah (Low-pitch)
            setTimeout(() => playBeep(220, 150, 0.2), 200); // Tut-Tut rendah
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
        });

        document.addEventListener('DOMContentLoaded', () => {
            initScanner();
        });

        async function initScanner() {
            html5QrCode = new Html5Qrcode("qr-reader");
            const config = {
                fps: 20,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            try {
                await html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
            } catch (err) {
                html5QrCode.start({ facingMode: "user" }, config, onScanSuccess).catch(e => console.error(e));
            }
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;
            isProcessing = true;

            // Suara Feedback Instan saat terdeteksi kamera
            playBeep(440, 100, 0.05);

            @this.processScan(decodedText).then(() => {
                // Beri jeda 3 detik sebelum boleh scan kode berikutnya
                setTimeout(() => { isProcessing = false; }, 3000);
            });
        }

        window.sendManual = () => {
            const val = document.getElementById('manualInput').value;
            if(val) {
                @this.processScan(val);
                document.getElementById('manualInput').value = '';
            }
        };
    </script>
    @endpush
</x-filament-panels::page>
