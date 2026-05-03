<x-filament-panels::page>
    <div class="space-y-6">
        {{-- SEKSI FILTER --}}
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold tracking-tight text-primary-600 dark:text-primary-400">Filter Jadwal</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cari nama ruangan untuk melihat ketersediaan secara real-time.</p>
                </div>

                <div class="w-full max-w-sm">
                    {{-- Render Form (Dropdown Searchable) --}}
                    {{ $this->form }}
                </div>
            </div>
        </x-filament::section>

        {{-- SEKSI KALENDER --}}
        <x-filament::section>
            <div id="calendar-container" wire:ignore class="bg-white dark:bg-transparent">
                <div id="calendar"></div>
            </div>
        </x-filament::section>
    </div>

    {{-- Script FullCalendar --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
          },
          locale: 'id',
          firstDay: 1,
          dayMaxEvents: true,
          height: 'auto',
          events: @json($events),

          // Styling event agar adaptif dengan dark/light mode
          eventClassNames: 'rounded-md border-none px-2 py-1 shadow-sm font-medium text-[10px] leading-tight text-white bg-primary-600 dark:bg-primary-500',

          windowResize: function() {
            calendar.changeView(window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth');
          }
        });

        calendar.render();

        // Listener untuk update data dari Livewire (Filter)
        window.addEventListener('refreshCalendar', (event) => {
            calendar.removeAllEvents();
            calendar.addEventSource(event.detail.events);
        });
      });
    </script>

    <style>
        /* CSS Root Variables untuk Tema Biru */
        :root {
            --fc-button-bg-color: var(--primary-600);
            --fc-button-border-color: var(--primary-600);
            --fc-button-hover-bg-color: var(--primary-500);
            --fc-button-hover-border-color: var(--primary-500);
            --fc-button-active-bg-color: var(--primary-700);
            --fc-button-active-border-color: var(--primary-700);
            --fc-today-bg-color: rgba(var(--primary-500), 0.1);
        }

        /* 1. Mengecilkan tulisan tanggal (Header Kalender) */
        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            color: var(--primary-600);
            text-transform: capitalize;
            letter-spacing: -0.025em;
        }

        /* 2. Styling Tombol UI */
        .fc .fc-button {
            border-radius: 8px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.4rem 0.8rem !important;
            text-transform: capitalize !important;
        }

        .fc .fc-button-primary:focus {
            box-shadow: none !important;
        }

        /* 3. Perbaikan Dark Mode Komprehensif */
        .dark .fc {
            --fc-border-color: #374151; /* gray-700 */
            --fc-daygrid-dot-event-hover-bg-color: #1f2937;
            --fc-page-bg-color: transparent;
            --fc-list-event-hover-bg-color: #1f2937;
            --fc-neutral-bg-color: #111827; /* gray-900 */
        }

        .dark .fc .fc-toolbar-title {
            color: var(--primary-400);
        }

        /* Warna Teks Kalender di Dark Mode */
        .dark .fc-col-header-cell-cushion,
        .dark .fc-daygrid-day-number,
        .dark .fc-list-day-text,
        .dark .fc-list-day-side-text,
        .dark .fc-timegrid-slot-label-cushion,
        .dark .fc-list-event-title,
        .dark .fc-list-event-time {
            color: #e5e7eb !important; /* gray-200 */
        }

        /* Background Header Tabel di Dark Mode */
        .dark .fc-theme-standard .fc-list-day-cushion {
            background-color: #1f2937 !important;
        }

        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th,
        .dark .fc-list {
            border-color: #374151 !important;
        }

        /* Pointer & Event Reset */
        .fc-event {
            cursor: default !important;
            border: none !important;
        }

        /* Responsivitas Judul di Mobile */
        @media (max-width: 768px) {
            .fc .fc-toolbar-title {
                font-size: 0.95rem !important;
            }
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
    </style>
</x-filament-panels::page>
