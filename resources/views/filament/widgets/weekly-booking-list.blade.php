<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold tracking-tight">Jadwal Peminjaman Minggu Ini</h2>
            <span class="text-xs text-gray-500 font-medium bg-gray-100 px-2 py-1 rounded-md">
                {{ now()->startOfWeek()->format('d M') }} - {{ now()->endOfWeek()->format('d M Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="py-3 px-4 text-xs font-bold uppercase text-gray-400">Tanggal & Jam</th>
                        <th class="py-3 px-4 text-xs font-bold uppercase text-gray-400">Ruangan</th>
                        <th class="py-3 px-4 text-xs font-bold uppercase text-gray-400 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                    @forelse($weeklyBookings as $booking)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                            <td class="py-4 px-4">
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                    {{ $booking->booking_date->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-500 font-mono">
                                    {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }} WIB
                                </p>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-sm font-semibold text-primary-600">{{ $booking->room->name }}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-medium">{{ $booking->booking_code }}</p>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($booking->status->value === 'in_use')
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-bold animate-pulse border border-blue-200">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                        </span>
                                        IN PROGRESS
                                    </div>
                                @else
                                    {{-- Menggunakan badge default Filament --}}
                                    <x-filament::badge :color="$booking->status->getColor()">
                                        {{ $booking->status->getLabel() }}
                                    </x-filament::badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-heroicon-o-calendar class="w-10 h-10 text-gray-200 mb-2"/>
                                    <p class="text-sm text-gray-400 italic">Tidak ada jadwal minggu ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
