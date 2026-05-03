<?php
namespace App\Filament\Resources\RoomBookings\Schemas;

use App\Enums\BookingStatus;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class RoomBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SEKSI 1: IDENTITAS & RUANGAN
                Section::make('Informasi Utama Peminjaman')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('booking_code')
                                ->label('Kode Booking')
                                ->default(fn () => 'BK-' . strtoupper(uniqid()))
                                ->disabled()
                                ->dehydrated()
                                ->required(),

                            Select::make('room_id')
                                ->label('Pilih Ruangan')
                                ->relationship(
                                    'room',
                                    titleAttribute: 'name',
                                    // FILTER PRODI: Hanya munculkan ruangan prodi user login
                                    modifyQueryUsing: function (Builder $query) {
                                        $user = Auth::user();
                                        $query->where('is_active', true);

                                        // Admin bisa melihat semua, selain itu difilter prodi
                                        if (!$user->hasRole('admin')) {
                                            $query->where('department_id', $user->department_id);
                                        }
                                        return $query;
                                    }
                                )
                                ->getOptionLabelFromRecordUsing(fn (Room $record) =>
                                    "{$record->name} - {$record->building} (Lantai: {$record->floor}) (Kapasitas: {$record->capacity})"
                                )
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(fn (Get $get) => self::checkAvailability($get)),

                            TextInput::make('purpose')
                                ->label('Tujuan Peminjaman')
                                ->required()
                                ->placeholder('Contoh: Mata Kuliah Pemrograman Web')
                                ->columnSpan(1),

                            TextInput::make('participant_count')
                                    ->label('Jumlah Peserta')
                                    ->numeric()
                                    ->minValue(1) // Minimal harus ada 1 orang
                                    ->required()
                                    ->suffix('Orang')
                                    ->columnSpan(1)
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $roomId = $get('room_id');

                                            if ($roomId) {
                                                // Cari ruangan di database
                                                $room = Room::find($roomId);

                                                // Jika jumlah peserta melebihi kapasitas ruangan, lempar error
                                                if ($room && $value > $room->capacity) {
                                                    $fail("Jumlah peserta melebihi kapasitas maksimal ruangan ini ({$room->capacity} orang).");
                                                }
                                            }
                                        },
                                    ]),

                            Textarea::make('description')
                                ->label('Keterangan Tambahan')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                // SEKSI 2: JADWAL & LOGIC SKS
                Section::make('Jadwal & Durasi SKS')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('booking_date')
                                ->label('Tanggal Peminjaman')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->required()
                                ->minDate(today())
                                ->live()
                                ->afterStateUpdated(fn (Get $get) => self::checkAvailability($get)),

                            Select::make('time_slot_id')
                                ->label('Durasi Peminjaman')
                                ->relationship('timeSlot', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                // Panggil fungsi hitung jam selesai saat SKS diubah
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateEndTime($get, $set)),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('start_time') // KITA UBAH JADI SELECT
                                ->label('Jam Mulai')
                                ->prefixIcon('heroicon-m-clock')
                                // ->prefix('Pilih')
                                ->options(function () {
                                    // Generate otomatis pilihan jam dari 07:00 sampai 16:00 (Kelipatan 5 menit)
                                    $options = [];
                                    $time = Carbon::parse('07:00');
                                    $endTime = Carbon::parse('16:00');

                                    while ($time <= $endTime) {
                                        $timeString = $time->format('H:i');
                                        // Key dan Value sama (contoh: "07:05" => "07:05")
                                        $options[$timeString] = $timeString;
                                        $time->addMinutes(5);
                                    }

                                    return $options;
                                })
                                ->placeholder('Pilih Jam')
                                ->searchable() // User bisa ketik "08" untuk langsung lompat ke jam 8
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateEndTime($get, $set))
                                ->rules([
                                    // Rule bentrok tetap jalan seperti biasa
                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        self::validateCollision($get, $fail);
                                    },
                                ]),

                            TimePicker::make('end_time')
                                ->label('Jam Selesai')
                                ->icon('heroicon-m-clock') // Menambahkan icon jam di dalam box input
                                ->native(false)
                                ->seconds(false) // Menghilangkan input detik
                                ->displayFormat('H:i') // Menampilkan format Jam:Menit di UI
                                ->required()
                                ->readOnly() // Dikunci agar user tidak ubah manual
                                ->dehydrated()
                                ->live()
                                ->afterStateUpdated(fn (Get $get) => self::checkAvailability($get)),
                        ]),

                        Placeholder::make('availability_map')
                                ->label('Status Ketersediaan Ruangan')
                                ->content(function (Get $get, $record) {
                                    $roomId = $get('room_id');
                                    $date = $get('booking_date');

                                    // 1. Jika ruangan atau tanggal belum dipilih
                                    if (!$roomId || !$date) {
                                        return new HtmlString('
                                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-slate-500 text-sm">
                                                <i class="heroicon-m-calendar mr-1 inline-block w-4 h-4"></i>
                                                Pilih <strong>Ruangan</strong> dan <strong>Tanggal</strong> terlebih dahulu untuk melihat jadwal.
                                            </div>
                                        ');
                                    }

                                    // 2. Ambil data booking yang sudah ada (Approved/Pending) di hari & ruangan tersebut
                                    $bookings = RoomBooking::with('user')
                                        ->where('room_id', $roomId)
                                        ->whereDate('booking_date', Carbon::parse($date)->format('Y-m-d'))
                                        ->whereNotIn('status', [BookingStatus::REJECTED, BookingStatus::CANCELLED])
                                        ->orderBy('start_time')
                                        ->get();

                                    // 3. Bangun Tampilan HTML
                                    $html = '<div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm mt-2">';
                                    $html .= '<h4 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Jadwal Terpakai pada ' . Carbon::parse($date)->format('d M Y') . ':
                                              </h4>';

                                    // JIKA KOSONG SEHARIAN
                                    if ($bookings->isEmpty()) {
                                        $html .= '
                                            <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 flex items-center gap-3">
                                                <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div>
                                                    <strong class="block text-emerald-800">✨ Ruangan Kosong!</strong>
                                                    <span class="text-sm">Tersedia full selama jam operasional (07:00 - 16:00 WIB).</span>
                                                </div>
                                            </div>';
                                    }
                                    // JIKA ADA YANG BOOKING
                                    else {
                                        $html .= '<div class="space-y-3">';
                                        foreach ($bookings as $b) {
                                            // Jangan tampilkan data ini sendiri jika sedang mode "Edit"
                                            if ($record && $record->id === $b->id) continue;

                                            $start = substr($b->start_time, 0, 5);
                                            $end = substr($b->end_time, 0, 5);
                                            $purpose = $b->purpose ?? 'Kegiatan';
                                            $userName = $b->user ? $b->user->name : 'Seseorang';
                                            $statusLabel = $b->status === BookingStatus::VERIFIED ? 'Disetujui' : 'Menunggu Approval';
                                            $statusColor = $b->status === BookingStatus::VERIFIED ? 'bg-red-100 text-red-700 border-red-200' : 'bg-orange-100 text-orange-700 border-orange-200';

                                            $html .= '
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-slate-50 border border-slate-100 rounded-xl text-sm gap-3 transition hover:shadow-md">
                                                    <div class="flex items-center gap-4">
                                                        <span class="font-bold ' . $statusColor . ' border px-3 py-1.5 rounded-lg shadow-sm whitespace-nowrap">
                                                            ' . $start . ' - ' . $end . '
                                                        </span>
                                                        <div class="flex flex-col">
                                                            <span class="text-slate-800 font-bold">' . $purpose . '</span>
                                                            <span class="text-xs text-slate-500">Oleh: ' . $userName . ' (' . $statusLabel . ')</span>
                                                        </div>
                                                    </div>
                                                </div>';
                                        }
                                        $html .= '</div>';

                                        $html .= '
                                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100 text-xs text-blue-700 flex gap-2">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>Selain jam merah/oranye di atas, ruangan <strong>TERSEDIA</strong> untuk di-booking (Jam Operasional: 07:00 - 16:00 WIB).</span>
                                            </div>';
                                    }

                                    $html .= '</div>';

                                    return new HtmlString($html);
                                })
                                ->columnSpanFull(),
                    ]),
                        // TextInput::make('user_id')->default(Auth::id())->hidden()->dehydrated(),
                        // TextInput::make('status')->default(BookingStatus::PENDING->value)->hidden()->dehydrated(),
                        Hidden::make('user_id')
                        ->default(fn () => Auth::id()),
                        Hidden::make('status')
                        ->default(BookingStatus::PENDING->value),
            ]);
    }


    /**
     * LOGIC UTAMA: Menghitung Jam Selesai Berdasarkan SKS
     */
    public static function calculateEndTime(Get $get, Set $set): void
    {
        $startTime = $get('start_time');
        $timeSlotId = $get('time_slot_id');

        if ($startTime && $timeSlotId) {
            $timeSlot = TimeSlot::find($timeSlotId);

            if ($timeSlot) {
                // Tambahkan durasi menit dari Master TimeSlot ke Jam Mulai
                $endTime = Carbon::parse($startTime)
                    ->addMinutes($timeSlot->duration)
                    ->format('H:i:s');

                $set('end_time', $endTime);
            }
        }
    }

    protected static function validateCollision(Get $get, \Closure $fail, $record = null)
    {
        $roomId = $get('room_id');
        $date = $get('booking_date');
        $start = $get('start_time');
        $end = $get('end_time');

        if ($roomId && $date && $start && $end) {
            $isOccupied = RoomBooking::where('room_id', $roomId)
                ->whereDate('booking_date', Carbon::parse($date)->format('Y-m-d'))
                ->whereNotIn('status', [BookingStatus::REJECTED, BookingStatus::CANCELLED])
                ->where(function ($query) use ($start, $end) {
                    $query->whereTime('start_time', '<', $end)
                          ->whereTime('end_time', '>', $start);
                })
                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                ->exists();

            if ($isOccupied) {
                $fail('Jadwal bentrok! Silakan pilih jam lain.');
            }
        }
    }

    protected static function checkAvailability(Get $get)
    {
        self::validateCollision($get, function ($message) {
            throw ValidationException::withMessages(['start_time' => $message]);
        });
    }
}
