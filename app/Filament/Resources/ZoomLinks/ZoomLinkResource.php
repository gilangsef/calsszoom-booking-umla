<?php

namespace App\Filament\Resources\ZoomLinks;

use App\Filament\Resources\ZoomLinks\Pages\ManageZoomLinks;
use App\Models\ZoomLink;
use BackedEnum;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;

class ZoomLinkResource extends Resource
{
    protected static ?string $model = ZoomLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedVideoCamera;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Data Zoom';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Zoom Meeting')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Nama / ID')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Zoom Room 1'),

                    Grid::make(2)->schema([
                        TextInput::make('meeting_id')
                            ->label('Meeting ID')
                            ->required(),

                        TextInput::make('passcode')
                            ->label('Passcode'),
                    ]),

                    TextInput::make('meeting_url')
                        ->label('Meeting URL')
                        ->required()
                        ->url()
                        ->columnSpanFull(),

                    TextInput::make('host_email')
                        ->label('Host Email')
                        ->email(),

                    Select::make('status')
                        ->label('Status')
                        ->required()
                        ->options([
                            'available' => 'Tersedia',
                            'occupied' => 'Sedang Digunakan',
                            'inactive' => 'Nonaktif',
                        ])
                        ->default('available')
                        ->native(false),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
{
    return $schema
        ->components([
            // SECTION 1: AKSES UTAMA MEETING
            Section::make('Akses Link Zoom')
                ->icon('heroicon-o-video-camera')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->label('Nama Lisensi/Akun')
                            ->weight('bold')
                            ->color('primary'),

                        TextEntry::make('status')
                            ->label('Status Ketersediaan')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'available' => 'success',
                                'occupied' => 'warning',
                                'inactive' => 'gray',
                                default => 'gray',
                            }),
                    ]),

                    TextEntry::make('meeting_url')
                        ->label('Meeting URL (Klik untuk Join)')
                        ->url(fn ($record) => $record->meeting_url, true)
                        ->color('info')
                        ->icon('heroicon-m-link')
                        ->weight('bold')
                        ->copyable()
                        ->extraAttributes(['class' => 'mt-4 py-3 px-4 bg-gray-50 rounded-xl border border-dashed border-gray-300']),
                ]),

            // SECTION 2: DETAIL KREDENSIAL
            Section::make('Kredensial & Teknis')
                ->icon('heroicon-o-key')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('meeting_id')
                            ->label('Meeting ID')
                            ->icon('heroicon-m-identification')
                            ->copyable(),

                        TextEntry::make('passcode')
                            ->label('Passcode')
                            ->icon('heroicon-m-lock-closed')
                            ->copyable(),

                        TextEntry::make('host_email')
                            ->label('Host Email')
                            ->icon('heroicon-m-envelope'),
                    ]),

                    Grid::make(3)->schema([
                        IconEntry::make('is_active')
                            ->label('Sistem Aktif')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->label('Daftar Sejak')
                            ->dateTime('d M Y'),

                        TextEntry::make('updated_at')
                            ->label('Pembaruan Terakhir')
                            ->dateTime('d M Y H:i'),
                    ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),
                ])
                ->collapsible(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('meeting_id')
                    ->label('Meeting ID')
                    ->copyable(),

                TextColumn::make('meeting_url')
                    ->label('URL')
                    ->limit(40)
                    ->copyable()
                    ->url(fn (ZoomLink $record) => $record->meeting_url, true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'available' => 'success',
                        'occupied' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(), // Hapus duplikat boolean() di sini

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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageZoomLinks::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Opsional: Hanya tampilkan badge untuk Super Admin jika perlu
        if (!Auth::user()?->isAdmin()) {
            return null;
        }

        // Menghitung total user
        $count = static::getModel()::count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // Menggunakan warna primer (biru) atau abu-abu untuk data informasi umum
        return 'info';
    }

}
