<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-m-calendar-days" icon-color="primary">
        <x-slot name="heading">Agenda Fasilitas Mingguan</x-slot>

        {{-- Grid Utama 7 Hari --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-3 mt-2 items-start">
            @foreach($calendar as $day)
                <div @class([
                    'flex flex-col gap-3 p-3 rounded-xl border min-h-[150px] transition-all duration-300',
                    'bg-white dark:bg-gray-900 border-gray-200 dark:border-white/10 shadow-sm' => ! $day['is_today'],
                    'bg-primary-50/30 dark:bg-primary-500/10 border-primary-500 ring-1 ring-primary-500 shadow-md scale-[1.02] z-10' => $day['is_today'],
                ])>

                    {{-- Header Tanggal --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-2">
                        <div class="flex flex-col">
                            <span @class([
                                'text-[9px] font-black uppercase tracking-widest leading-none',
                                'text-gray-400' => ! $day['is_today'],
                                'text-primary-600 dark:text-primary-400' => $day['is_today'],
                            ])>{{ $day['date_label'] }}</span>
                            <span class="text-xs font-bold text-gray-800 dark:text-white mt-0.5">{{ $day['date_number'] }}</span>
                        </div>
                    </div>

                    {{-- Container Events: Diubah jadi Flex-Row agar geser ke kanan --}}
                    <div class="flex flex-row flex-wrap gap-2">
                        @forelse($day['events'] as $event)
                            <div @class([
                                'flex flex-col gap-1 p-2 rounded-lg border-s-2 text-[10px] leading-tight shadow-xs min-w-[100px] flex-1',
                                'bg-blue-50/50 border-blue-500 text-blue-700 dark:bg-blue-900/30' => $event['color'] === 'blue',
                                'bg-emerald-50/50 border-emerald-500 text-emerald-700 dark:bg-emerald-900/30' => $event['color'] === 'emerald',
                            ])>
                                <div class="flex items-start gap-1">
                                    <x-dynamic-component :component="$event['icon']" class="shrink-0" style="width: 12px; height: 12px;" />
                                    <span class="font-bold truncate leading-none">{{ $event['title'] }}</span>
                                </div>

                                <p class="text-[9px] opacity-70 line-clamp-1 italic">
                                    {{ $event['subtitle'] ?: 'No desc' }}
                                </p>

                                <div class="flex items-center gap-1 text-[9px] font-mono font-medium opacity-80 mt-0.5">
                                    <x-heroicon-m-clock style="width: 10px; height: 10px;" />
                                    {{ date('H:i', strtotime($event['start'])) }}-{{ date('H:i', strtotime($event['end'])) }}
                                </div>
                            </div>
                        @empty
                            <div class="w-full flex flex-col items-center justify-center py-10 opacity-10 grayscale">
                                <x-heroicon-o-calendar class="w-5 h-5" />
                                <span class="text-[8px] font-bold mt-1 tracking-tighter uppercase">Free</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
