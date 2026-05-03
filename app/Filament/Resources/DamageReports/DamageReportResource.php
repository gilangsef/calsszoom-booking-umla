<?php

namespace App\Filament\Resources\DamageReports;

use App\Enums\DamageReportStatus;
use App\Filament\Resources\DamageReports\Pages\ManageDamageReports;
use App\Models\DamageReport;
use App\Models\Room;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DamageReportResource extends Resource
{
    protected static ?string $model = DamageReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Lapor Kerusakan';

    protected static ?string $modelLabel = 'Lapor Kerusakann';

    protected static ?string $pluralModelLabel = 'Lapor Kerusakan';

    protected static string | UnitEnum | null $navigationGroup = 'Lapor';

    /**
     * Scope: Admin melihat semua.
     * Dosen & Operasional hanya melihat laporannya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Auth::check() && !Auth::user()->isAdmin()) {
            $query->where('reported_by', Auth::id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Laporan')
                    ->description('Detail kerusakan yang ditemukan di ruangan.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('report_code')
                                ->label('Kode Laporan')
                                ->default(fn () => 'REP-' . strtoupper(uniqid()))
                                ->disabled()
                                ->dehydrated()
                                ->required(),

                            Select::make('room_id')
                                ->label('Ruangan')
                                ->relationship('room', 'name')
                                ->getOptionLabelFromRecordUsing(fn (Room $record) => "{$record->code} - {$record->name}")
                                ->searchable(['code', 'name'])
                                ->preload()
                                ->required(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('damage_type')
                                ->label('Jenis Kerusakan')
                                ->placeholder('Contoh: Lampu Mati, AC Rusak')
                                ->required(),

                            Select::make('severity')
                                ->label('Tingkat Keparahan')
                                ->options([
                                    'low' => 'Ringan',
                                    'medium' => 'Sedang',
                                    'high' => 'Berat',
                                    'critical' => 'Kritis',
                                ])
                                ->default('medium')
                                ->required()
                                ->native(false),
                        ]),

                        Textarea::make('description')
                            ->label('Deskripsi Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('photo')
                            ->label('Foto Bukti')
                            ->image()
                            ->directory('damage-reports')
                            ->columnSpanFull(),

                        DateTimePicker::make('reported_at')
                            ->label('Waktu Kejadian/Lapor')
                            ->default(now())
                            ->disabled()
                            ->required(),
                    ]),

                Section::make('Tindakan & Penyelesaian')
                    ->description('Hanya diisi oleh Admin saat proses perbaikan.')
                    ->columnSpanFull()
                    ->visible(fn ($record) => filled($record) && Auth::user()->isAdmin())
                    ->schema([
                        Select::make('status')
                            ->label('Status Laporan')
                            ->options(DamageReportStatus::class)
                            ->default(DamageReportStatus::PENDING)
                            ->required()
                            ->native(false),

                        Grid::make(2)->schema([
                            DateTimePicker::make('resolved_at')
                                ->label('Waktu Selesai'),

                            Select::make('resolved_by')
                                ->label('Petugas Eksekutor')
                                ->relationship('resolver', 'name')
                                ->searchable()
                                ->preload(),
                        ]),

                        Textarea::make('resolution_notes')
                            ->label('Catatan Perbaikan')
                            ->placeholder('Jelaskan apa saja yang sudah diperbaiki...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Hidden::make('reported_by')
                    ->default(fn () => Auth::id())
                    ->dehydrated()
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                    // SECTION 1: INFORMASI UTAMA
            Section::make('Detail Laporan Kerusakan')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('report_code')
                            ->label('Kode Laporan')
                            ->weight('bold')
                            ->color('primary')
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge()
                            ->label('Status Laporan')
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->color(fn ($state) => $state->getColor()),

                        TextEntry::make('room.name')
                            ->label('Lokasi Ruangan')
                            ->icon('heroicon-m-home-modern'),

                        TextEntry::make('damage_type')
                            ->label('Jenis Kerusakan'),

                        TextEntry::make('severity')
                            ->label('Tingkat Keparahan')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'success',
                                'medium' => 'warning',
                                'high', 'critical' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('reporter.name')
                            ->label('Dilaporkan Oleh')
                            ->icon('heroicon-m-user'),
                    ]),

                    TextEntry::make('description')
                        ->label('Deskripsi Kejadian')
                        ->placeholder('Tidak ada deskripsi tambahan.')
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'pt-4']),
                ]),

            // SECTION 2: BUKTI FOTO (Hanya muncul jika ada foto)
            Section::make('Bukti Foto')
                ->icon('heroicon-o-camera')
                ->schema([
                    ImageEntry::make('photo')
                        ->label('')
                        ->extraImgAttributes([
                            'class' => 'rounded-xl shadow-md w-full object-contain bg-gray-50',
                            'style' => 'max-height: 400px;',
                        ]),
                ])
                ->visible(fn ($record) => filled($record->photo))
                ->collapsible(),

            // SECTION 3: PENYELESAIAN
            Section::make('Informasi Penyelesaian')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('resolver.name')
                            ->label('Petugas Eksekutor')
                            ->placeholder('Menunggu penanganan'),

                        TextEntry::make('resolved_at')
                            ->label('Waktu Selesai')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('resolution_notes')
                            ->label('Catatan Perbaikan')
                            ->placeholder('Belum ada catatan perbaikan.')
                            ->columnSpanFull(),
                    ]),
                ])
                ->visible(fn ($record) => filled($record->resolved_at) || $record->status->value === 'resolved')
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_code')
                    ->label('Kode')
                    ->searchable()
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),

                TextColumn::make('room.name')
                    ->label('Ruangan')
                    ->sortable(),

                TextColumn::make('damage_type')
                    ->label('Jenis')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->icon(fn ($state) => $state->getIcon())
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->getColor())
                    ->sortable(),

                TextColumn::make('reported_at')
                    ->label('Tgl Lapor')
                    ->dateTime('d M Y')
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
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                    ->label('Lihat'),
                    EditAction::make()
                       ->label('Edit'),

                    // 1. ACTION: SELESAIKAN (RESOLVE)
                    Action::make('resolve')
                        ->label('Selesaikan')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn ($record) => Auth::user()->isAdmin() && !in_array($record->status, [DamageReportStatus::RESOLVED, DamageReportStatus::CANCELLED]))
                        ->form([
                            Textarea::make('resolution_notes')
                                ->label('Catatan Penyelesaian')
                                ->placeholder('Contoh: Kabel proyektor sudah diganti dengan yang baru...')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(fn ($record, array $data) => $record->update([
                            'status' => DamageReportStatus::RESOLVED,
                            'resolved_at' => now(),
                            'resolved_by' => Auth::id(),
                            'resolution_notes' => $data['resolution_notes'],
                        ])),

                    // 2. ACTION: BATALKAN (CANCEL)
                    Action::make('cancel')
                        ->label('Batalkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => Auth::user()->isAdmin() && !in_array($record->status, [DamageReportStatus::RESOLVED, DamageReportStatus::CANCELLED]))
                        ->form([
                            Textarea::make('resolution_notes')
                                ->label('Alasan Pembatalan')
                                ->placeholder('Contoh: Laporan ganda, atau kerusakan tidak ditemukan...')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(fn ($record, array $data) => $record->update([
                            'status' => DamageReportStatus::CANCELLED,
                            // resolved_at dan resolved_by bisa diisi juga sebagai jejak audit siapa yang membatalkan
                            'resolved_at' => now(),
                            'resolved_by' => Auth::id(),
                            'resolution_notes' => $data['resolution_notes'],
                        ])),

                    DeleteAction::make()
                    ->label('Hapus'),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->label('Kapus Pilihan'),
                ])
                ->label('Tindakan'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDamageReports::route('/'),
        ];
    }
}
