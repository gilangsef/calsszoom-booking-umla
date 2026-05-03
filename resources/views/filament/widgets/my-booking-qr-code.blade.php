<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold tracking-tight">Tiket QR Peminjaman</h2>
            <div class="flex gap-2">
                <span class="flex items-center gap-1 text-[10px] text-gray-500 font-medium uppercase">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Aktif
                </span>
            </div>
        </div>

        @if($bookings->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center border-2 border-dashed border-gray-100 rounded-xl">
                <div class="p-4 bg-gray-50 rounded-full mb-3">
                    <x-heroicon-o-ticket class="w-10 h-10 text-gray-300" />
                </div>
                <p class="text-sm text-gray-500 italic font-medium">Belum ada QR Code tersedia.</p>
                <p class="text-xs text-gray-400 mt-1">QR akan muncul otomatis setelah diverifikasi Operasional.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bookings as $booking)
                    <div class="flex flex-col items-center bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden transition-all hover:shadow-md">

                        <div class="w-full px-4 py-2 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                            <span class="text-[10px] font-black text-primary-600 tracking-tighter">{{ $booking->booking_code }}</span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $booking->status->getColor() === 'success' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $booking->status->getLabel() }}
                            </span>
                        </div>

                        <div class="p-5 flex flex-col items-center w-full">
                            <div class="p-2 bg-white border-2 border-gray-50 rounded-xl shadow-inner">
                                {!! QrCode::size(160)->margin(1)->generate($booking->qr_token) !!}
                            </div>

                            <div class="mt-4 text-center">
                                <h3 class="text-sm font-bold text-gray-800 line-clamp-1">{{ $booking->room->name }}</h3>
                                <div class="flex items-center justify-center gap-2 mt-1">
                                    <div class="flex items-center gap-1 text-[10px] text-gray-500">
                                        <x-heroicon-m-calendar class="w-3 h-3"/>
                                        {{ $booking->booking_date->format('d M Y') }}
                                    </div>
                                    <span class="text-gray-300">|</span>
                                    <div class="flex items-center gap-1 text-[10px] font-bold text-gray-700">
                                        <x-heroicon-m-clock class="w-3 h-3 text-primary-500"/>
                                        {{ substr($booking->start_time, 0, 5) }} WIB
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($booking->qr_expired_at)
                        <div class="w-full py-2 bg-rose-50 text-center border-t border-rose-100">
                            <p class="text-[9px] font-bold text-rose-600 uppercase tracking-tight">
                                Berlaku s/d {{ $booking->qr_expired_at->format('H:i') }} WIB
                            </p>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
