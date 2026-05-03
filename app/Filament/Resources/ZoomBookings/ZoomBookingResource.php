<?php

namespace App\Filament\Resources\ZoomBookings;

use App\Enums\ZoomBookingLinkStatus;
use App\Filament\Resources\ZoomBookings\Pages\ManageZoomBookings;
use App\Models\ZoomBooking;
use App\Services\ZoomService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Filament\Infolists\Components\TextEntry;

use UnitEnum ;

class ZoomBookingResource extends Resource
{
    protected static ?string $model = ZoomBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string | UnitEnum | null $navigationGroup = 'Booking';

    protected static ?string $navigationLabel = 'Booking Zoom';

    protected static ?string $modelLabel = 'Booking Zoom';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (Auth::check() && !Auth::user()->isAdmin() && !Auth::user()->isOperasional()) {
            $query->where('user_id', Auth::id());
        }
        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Section::make('Informasi Utama Booking')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('booking_code')
                                ->label('Kode Booking')
                                ->default(fn () => 'ZM-' . strtoupper(uniqid()))
                                ->disabled()
                                ->dehydrated(),

                            Select::make('zoom_category')
                                ->label('Kategori Zoom')
                                ->options([
                                    'perkuliahan' => 'Perkuliahan (Kelas Online)',
                                    'acara_kampus' => 'Acara Kampus (Seminar/Webinar)',
                                ])
                                ->required()
                                ->native(false),

                            DatePicker::make('booking_date')
                                ->label('Tanggal Peminjaman')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->required()
                                ->minDate(today())
                                ->live() // Trigger untuk refresh ketersediaan
                                ->afterStateUpdated(fn (Get $get) => self::checkAvailability($get)),

                            TextInput::make('participant_count')
                                ->label('Estimasi Peserta')
                                ->numeric()
                                ->default(1)
                                ->suffix('Orang')
                                // Validasi opsional: Zoom pro mentok 300 orang biasanya
                                ->maxValue(300),
                            TextInput::make('purpose')
                                ->label('Tujuan Acara/Kegiatan')
                                ->required()
                                ->placeholder('Contoh: Rapat Koordinasi HIMA')
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Keterangan Tambahan')
                                ->rows(3)
                                ->placeholder('Tambahkan detail seperti ID Meeting khusus jika diperlukan...')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Waktu & Ketersediaan')
                    ->schema([
                        Grid::make(2)->schema([
                            TimePicker::make('start_time')
                                ->label('Jam Mulai')
                                ->native(true) // Native true agar UX di mobile lebih enak
                                ->required()
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->live()
                                ->afterStateUpdated(fn (Get $get) => self::checkAvailability($get))
                                ->rules([
                                    // Panggil fungsi validasi bentrok
                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        self::validateCollision($get, $fail);
                                    },
                                ]),

                            TimePicker::make('end_time')
                                ->label('Jam Selesai')
                                ->native(true)
                                ->required()
                                ->seconds(false)
                                ->displayFormat('H:i')
                                ->after('start_time')
                                ->live()
                                ->afterStateUpdated(fn (Get $get) => self::checkAvailability($get))
                                ->rules([
                                    // Validasi bentrok juga di end_time untuk keamanan ganda
                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        self::validateCollision($get, $fail);
                                    },
                                ]),
                        ]),

                        // PETA KETERSEDIAAN ZOOM
                        Placeholder::make('availability_map')
                            ->label('Status Ketersediaan')
                            ->content(function (Get $get, $record) {
                                $date = $get('booking_date');

                                if (!$date) {
                                    return new HtmlString('
                                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-slate-500 text-sm">
                                            <i class="heroicon-m-clock mr-1 inline-block w-4 h-4"></i>
                                            Pilih <strong>Tanggal</strong> dulu untuk melihat jam yang sudah terpakai.
                                        </div>
                                    ');
                                }

                                // Asumsi nama modelnya ZoomBooking
                                $bookings = ZoomBooking::with('user')
                                    ->whereDate('booking_date', Carbon::parse($date)->format('Y-m-d'))
                                    ->whereNotIn('status', [ZoomBookingLinkStatus::REJECTED, ZoomBookingLinkStatus::CANCELLED])
                                    ->orderBy('start_time')
                                    ->get();

                                $html = '<div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm mt-2">';

                                // REVISI: Judul diubah hanya fokus ke Jam, tanpa format tahun/bulan
                                $html .= '<h4 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Daftar Jam Terpakai:
                                          </h4>';

                                if ($bookings->isEmpty()) {
                                    $html .= '
                                        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 flex items-center gap-3">
                                            <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div>
                                                <strong class="block text-emerald-800">✨ Lisensi Kosong!</strong>
                                                <span class="text-sm">Belum ada jam yang terpakai. Bebas pilih jam berapapun.</span>
                                            </div>
                                        </div>';
                                } else {
                                    $html .= '<div class="space-y-3">';
                                    foreach ($bookings as $b) {
                                        if ($record && $record->id === $b->id) continue;

                                        $start = Carbon::parse($b->start_time)->format('H:i');
                                        $end = Carbon::parse($b->end_time)->format('H:i');
                                        $purpose = $b->purpose ?? 'Kegiatan';

                                        // Pakai ZoomBookingStatus atau BookingStatus sesuai Enum kamu
                                        $statusLabel = $b->status === ZoomBookingLinkStatus::APPROVED ? 'Disetujui' : 'Menunggu';
                                        $statusColor = $b->status === ZoomBookingLinkStatus::APPROVED ? 'bg-red-100 text-red-700 border-red-200' : 'bg-orange-100 text-orange-700 border-orange-200';

                                        $html .= '
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-slate-50 border border-slate-100 rounded-xl text-sm gap-3 transition hover:shadow-md">
                                                <div class="flex items-center gap-4">
                                                    <span class="font-bold ' . $statusColor . ' border px-3 py-1.5 rounded-lg shadow-sm whitespace-nowrap">
                                                        ' . $start . ' - ' . $end . '
                                                    </span>
                                                    <div class="flex flex-col">
                                                        <span class="text-slate-800 font-bold">' . $purpose . '</span>
                                                        <span class="text-xs text-slate-500">Status: ' . $statusLabel . '</span>
                                                    </div>
                                                </div>
                                            </div>';
                                    }
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akses Zoom Meeting')
                    ->description('Detail link Zoom yang digenerate otomatis oleh sistem.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('zoom_link')
                                ->label('URL Join Zoom')
                                ->formatStateUsing(fn ($state) => new HtmlString("<a href='{$state}' target='_blank' class='text-primary-600 underline font-bold'>Klik untuk Join Meeting</a>"))
                                ->placeholder('Belum di-generate')
                                ->columnSpan(3),

                            TextEntry::make('zoom_meeting_id')
                                ->label('Meeting ID')
                                ->copyable()
                                ->copyMessage('Meeting ID berhasil disalin!')
                                ->placeholder('-'),

                            TextEntry::make('zoom_password')
                                ->label('Passcode')
                                ->copyable()
                                ->copyMessage('Passcode berhasil disalin!')
                                ->placeholder('-'),
                        ])
                    ])
                    // Hanya tampil kalau sudah di-approve
                    ->visible(fn ($record) => $record->status === ZoomBookingLinkStatus::APPROVED),

                Section::make('Detail Peminjaman')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('booking_code')->label('Kode Booking'),

                            TextEntry::make('zoom_category')
                                ->label('Kategori')
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'perkuliahan' => 'Perkuliahan',
                                    'acara_kampus' => 'Acara Kampus',
                                    default => $state,
                                }),

                            TextEntry::make('booking_date')
                                ->label('Tanggal')
                                ->date('d F Y'),

                            TextEntry::make('Waktu')
                                ->getStateUsing(fn ($record) => Carbon::parse($record->start_time)->format('H:i') . ' - ' . Carbon::parse($record->end_time)->format('H:i')),

                            TextEntry::make('purpose')->label('Tujuan'),

                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->icon(fn ($state) => $state->getIcon())
                                ->formatStateUsing(fn ($state) => $state->getLabel())
                                ->color(fn ($state) => $state->getColor()),
                        ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->weight('bold')
                    ->color('primary')
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->visible(fn() => Auth::user()?->isAdmin())
                    ->description(fn (ZoomBooking $record) => $record->purpose),
                TextColumn::make('zoom_category')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'perkuliahan' => 'Perkuliahan',
                        'acara_kampus' => 'Acara Kampus',
                        default => $state,
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('booking_date')
                    ->label('Jadwal')
                    ->date('d M Y')
                    ->description(fn (ZoomBooking $record): string =>
                        ($record->start_time && $record->end_time)
                            ? Carbon::parse($record->start_time)->format('H:i') . ' - ' . Carbon::parse($record->end_time)->format('H:i')
                            : '-'
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(fn ($state) => $state->getIcon())
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->getColor())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // Menggunakan import langsung sesuai permintaan
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('approve')
                        ->label('Setujui & Buat Zoom')
                        ->icon('heroicon-o-video-camera')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === ZoomBookingLinkStatus::PENDING && Auth::user()?->can('VerifyZoom:ZoomBooking'))
                        ->requiresConfirmation()
                        ->modalHeading('Buat Link Zoom Otomatis?')
                        ->modalDescription('Sistem akan menembak API Zoom untuk membuat jadwal meeting baru.')
                        ->action(function ($record) {
                            try {
                                $zoomService = new ZoomService();
                                $zoomData = $zoomService->createMeeting($record);

                                $record->update([
                                    'status' => ZoomBookingLinkStatus::APPROVED,
                                    'zoom_link' => $zoomData['join_url'] ?? null,
                                    'zoom_meeting_id' => $zoomData['id'] ?? null,
                                    'zoom_password' => $zoomData['password'] ?? null,
                                    'verified_by' => Auth::id(),
                                    'verified_at' => now(),
                                ]);

                                Notification::make()
                                    ->title('Zoom Meeting Berhasil Dibuat!')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal membuat Zoom')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === ZoomBookingLinkStatus::PENDING && Auth::user()?->can('VerifyZoom:ZoomBooking'))
                        ->form([
                            Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('Alasan...')
                            ->required()
                            ->rows(3)
                            ->required(),
                        ])
                        ->action(fn ($record, array $data) => $record->update([
                            'status' => ZoomBookingLinkStatus::REJECTED,
                            'rejection_reason' => $data['rejection_reason'],
                            'verified_by' => Auth::id(),
                            'verified_at' => now(),
                        ])),

                    DeleteAction::make(),
                ])
            ]);
    }

    /**
     * LOGIC VALIDASI BENTROK ZOOM
     */
    protected static function validateCollision(Get $get, \Closure $fail, $record = null)
    {
        $date = $get('booking_date');
        $start = $get('start_time');
        $end = $get('end_time');

        // Pastikan semua field waktu sudah diisi
        if ($date && $start && $end) {

            // Asumsi nama model adalah ZoomBooking. Ganti jika berbeda!
            $isOccupied = ZoomBooking::whereDate('booking_date', Carbon::parse($date)->format('Y-m-d'))
                ->whereNotIn('status', [\App\Enums\BookingStatus::REJECTED, \App\Enums\BookingStatus::CANCELLED])
                ->where(function ($query) use ($start, $end) {
                    // Cek apakah jam saling tumpang tindih
                    $query->whereTime('start_time', '<', $end)
                          ->whereTime('end_time', '>', $start);
                })
                ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                ->exists();

            if ($isOccupied) {
                $fail('Jadwal bentrok! Lisensi Zoom sedang dipakai di jam tersebut. Silakan cek Peta Jadwal di bawah.');
            }
        }
    }

    protected static function checkAvailability(Get $get)
    {
        self::validateCollision($get, function ($message) {
            throw ValidationException::withMessages([
                'start_time' => $message,
                'end_time' => $message,
            ]);
        });
    }

    public static function getPages(): array
    {
        return ['index' => ManageZoomBookings::route('/')];
    }
}
