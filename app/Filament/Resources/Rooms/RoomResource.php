<?php

namespace App\Filament\Resources\Rooms;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Filament\Resources\Rooms\Pages\ManageRooms;
use App\Models\Room;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Data Ruangan';

    protected static ?string $modelLabel = 'Data Ruangan';

    protected static ?string $pluralModelLabel = 'Data Ruangan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Ruangan')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('code')
                            ->label('Kode Ruangan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('A101'),

                        TextInput::make('name')
                            ->label('Nama Ruangan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ruang Kuliah A101'),

                        TextInput::make('building')
                            ->label('Gedung')
                            ->maxLength(100)
                            ->placeholder('Gedung A'),

                        TextInput::make('floor')
                            ->label('Lantai')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->default(1),

                        TextInput::make('capacity')
                            ->label('Kapasitas')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->suffix('orang')
                            ->default(30),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'available' => 'Tersedia',
                                'occupied' => 'Sedang Digunakan',
                                'under_maintenance' => 'Dalam Perbaikan',
                                'locked' => 'Terkunci',
                            ])
                            ->default('available')
                            ->native(false),

                        TagsInput::make('facilities')
                            ->label('Fasilitas')
                            ->placeholder('Tambahkan fasilitas')
                            ->suggestions([
                                'Proyektor',
                                'AC',
                                'Whiteboard',
                                'Sound System',
                                'Komputer',
                                'WiFi',
                                'TV LED',
                            ])
                            ->columnSpanFull(),
                        // Tambahkan Select Prodi di sini
                    Select::make('department_id')
                        ->label('Pemilik Ruangan (Prodi)')
                        ->relationship('department', 'name') // Relasi ke model Department
                        ->placeholder('Pilih Program Studi')
                        ->searchable()
                        ->preload()
                        ->required() // Wajib diisi sesuai revisi pembimbing
                        ->columnSpanFull(), // Agar terlihat jelas di paling atas
                    ]),
                ]),

            Section::make('Detail Tambahan')
                ->schema([
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Keterangan'),

                    FileUpload::make('image')
                        ->label('Foto Ruangan')
                        ->image()
                        ->directory('rooms')
                        ->maxSize(2048)
                        ->imageEditor()
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->inline(false),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
{
    return $schema
        ->components([
            // SECTION 1: VISUAL RUANGAN
            Section::make('Visual Ruangan')
                ->schema([
                    ImageEntry::make('image')
                        ->label('')
                        ->extraImgAttributes([
                            'class' => 'rounded-xl shadow-md w-full object-cover',
                            'style' => 'max-height: 350px;',
                        ])
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => filled($record->image)),

            // SECTION 2: DETAIL TEKNIS
            Section::make('Informasi Detail Ruangan')
                ->icon('heroicon-o-home-modern')
                ->schema([
                    Grid::make(3)->schema([ // Grid 3 agar pas untuk Kode, Nama, dan Status
                        TextEntry::make('code')
                            ->label('Kode Ruangan')
                            ->weight('bold')
                            ->color('primary')
                            ->copyable(),

                        TextEntry::make('name')
                            ->label('Nama Ruangan')
                            ->weight('bold'),

                        TextEntry::make('status')
                            ->label('Status Ketersediaan')
                            ->badge(),
                    ]),

                    Grid::make(3)->schema([
                        TextEntry::make('building')
                            ->label('Gedung')
                            ->icon('heroicon-m-building-office'),

                        TextEntry::make('floor')
                            ->label('Lantai')
                            ->formatStateUsing(fn ($state) => "Lantai {$state}"),

                        TextEntry::make('capacity')
                            ->label('Kapasitas Max')
                            ->suffix(' Orang')
                            ->icon('heroicon-m-users'),
                    ]),
                    Grid::make(3)->schema([
                        TextEntry::make('department.name')
                            ->label('Prodi Pemilik')
                            ->badge()
                            ->color('info'),
                    ])
                    ->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),
                ]),

            // SECTION 3: FASILITAS & SISTEM
            Section::make('Fasilitas & Status Sistem')
                ->icon('heroicon-o-wrench-screwdriver')
                ->schema([
                    TextEntry::make('facilities')
                        ->label('Fasilitas Tersedia')
                        ->badge()
                        ->color('success')
                        ->separator(',')
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        IconEntry::make('is_active')
                            ->label('Status Aktif Sistem')
                            ->boolean(),

                        TextEntry::make('created_at')
                            ->label('Tanggal Terdaftar')
                            ->dateTime('d M Y'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Update')
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
                ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-room.png')),

                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama Ruangan')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Room $record) =>
                        "{$record->building} - Lantai {$record->floor}"
                    ),

                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->suffix(' orang')
                    ->sortable()
                    ->alignCenter(),

                // Tambahkan kolom Prodi di sini
                TextColumn::make('department.name')
                    ->label('Prodi Pemilik')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('facilities')
                    ->label('Fasilitas')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state instanceof BookingStatus ? $state->getColor() : 'gray')
                    ->icon(fn ($state) => $state instanceof BookingStatus ? $state->getIcon() : null)
                    ->formatStateUsing(fn ($state) => $state instanceof BookingStatus ? $state->getLabel() : $state)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
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
                ViewAction::make()
                 ->label('Lihat'),
                EditAction::make()
                 ->label('Edit'),
                DeleteAction::make()
                 ->label('Hapus'),
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
            'index' => ManageRooms::route('/'),
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
