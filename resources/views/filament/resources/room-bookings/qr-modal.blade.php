<div class="flex flex-col items-center justify-center p-6 text-center">
    {{-- Container QR --}}
    <div class="p-4 bg-white rounded-2xl shadow-xl border-2 border-gray-100 flex items-center justify-center transition-all hover:scale-105">
        @if(class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode'))
            {!! QrCode::size(250)->margin(1)->generate($record->qr_token) !!}
        @else
            <div class="w-[250px] h-[250px] flex items-center justify-center bg-gray-100 text-gray-400 text-xs italic p-4">
                Library SimpleQRCode belum terinstall.<br>
                Run: composer require simplesoftwareio/simple-qrcode
            </div>
        @endif
    </div>

    {{-- Info Token --}}
    <div class="mt-6 space-y-1">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">QR Token / Booking Code</p>
        <p class="text-3xl font-black text-gray-800 tracking-widest selection:bg-yellow-200">
            {{ $record->qr_token }}
        </p>
    </div>

    {{-- Info Expiry --}}
    @if($record->qr_expired_at)
        <div class="mt-8 flex items-center gap-3 px-5 py-2.5 bg-rose-50 rounded-full text-rose-700 border border-rose-100 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" />
            </svg>
            <span class="text-xs font-bold tracking-tight">
                Expired: {{ $record->qr_expired_at->format('d M, H:i') }} WIB
            </span>
        </div>
    @endif

    <p class="mt-4 text-[11px] text-gray-400 leading-relaxed italic">
        Tunjukkan QR ini ke petugas operasional<br>saat pengambilan atau pengembalian kunci.
    </p>
</div>
