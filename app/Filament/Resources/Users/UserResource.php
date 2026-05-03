<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use UnitEnum;

use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static string | UnitEnum | null $navigationGroup = 'Users';

    protected static ?string $pluralModelLabel = 'Data Pengguna';

    protected static ?string $navigationLabel = 'Data Pengguna';

    public static function form(Schema $schema): Schema
{
    return $schema
        ->components([
            Section::make('Informasi Profil')
                ->description('Kelola data diri dasar dan identitas pengguna.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Ahmad Fauzi')
                            ->required()
                            ->maxLength(255),

                        Select::make('roles')
                            ->label('Role / Peran')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->native(false),

                        TextInput::make('nim')
                            ->label('NIM (Mahasiswa)')
                            ->placeholder('Masukkan NIM')
                            ->unique(ignoreRecord: true),

                        TextInput::make('nip')
                            ->label('NIP (Dosen/Staff)')
                            ->placeholder('Masukkan NIP')
                            ->unique(ignoreRecord: true),

                        Select::make('department_id')
                            ->label('Program Studi')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->columnSpanFull()
                            ->preload()
                            ->placeholder('Pilih Prodi'),
                    ]),
                ]),

            Section::make('Keamanan & Kontak')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('email@u mla.ac.id'),

                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->revealable()
                            ->placeholder('********'),

                        TextInput::make('phone')
                            ->label('Nomor Telepon/WA')
                            ->tel()
                            ->placeholder('08123456789'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Verifikasi Email')
                            ->native(false)
                            ->placeholder('Pilih tanggal jika sudah verifikasi'),
                    ]),

                    Grid::make(1)->schema([
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Nonaktifkan jika user tidak diperbolehkan login ke sistem.')
                            ->default(true)
                            ->inline(false),
                    ]),
                ])->collapsible(),
        ]);
}

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECTION 1: PROFIL UTAMA
                Section::make('Profil Pengguna')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make(3)->schema([ // Grid 3 untuk Nama, Role, dan Status
                            TextEntry::make('name')
                                ->label('Nama Lengkap')
                                ->weight('bold')
                                ->color('primary'),

                            TextEntry::make('roles')
                                ->label('Role / Peran')
                                ->badge()
                                ->getStateUsing(fn ($record) => $record->getRoleNames()) // Khusus Spatie Laravel Permission
                                ->color('warning'),

                            IconEntry::make('is_active')
                                ->label('Status Akun')
                                ->boolean(),
                        ]),

                        Grid::make(2)->schema([
                            TextEntry::make('nim')
                                ->label('NIM (Mahasiswa)')
                                ->placeholder('-')
                                ->icon('heroicon-m-academic-cap')
                                ->copyable(),

                            TextEntry::make('nip')
                                ->label('NIP (Dosen/Staff)')
                                ->placeholder('-')
                                ->icon('heroicon-m-briefcase')
                                ->copyable(),
                        ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100']),
                    ]),

                // SECTION 2: KONTAK & VERIFIKASI
                Section::make('Informasi Kontak & Akun')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('email')
                                ->label('Alamat Email')
                                ->icon('heroicon-m-envelope')
                                ->copyable(),

                            TextEntry::make('phone')
                                ->label('Nomor Telepon')
                                ->icon('heroicon-m-phone')
                                ->placeholder('Belum diatur')
                                ->copyable(),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('email_verified_at')
                                ->label('Verifikasi Email')
                                ->dateTime('d M Y H:i')
                                ->placeholder('Belum diverifikasi'),

                            TextEntry::make('created_at')
                                ->label('Terdaftar Sejak')
                                ->dateTime('d M Y'),

                            TextEntry::make('updated_at')
                                ->label('Update Terakhir')
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
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->copyable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'operasional' => 'warning',
                        'pimpinan' => 'info',
                        'dosen' => 'success',
                        'student' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),
                TextColumn::make('phone')
                ->icon('heroicon-m-phone')
                ->toggleable(),

                TextColumn::make('nim')
                    ->label('NIM')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department.name')
                    ->label('Prodi')
                    ->badge()
                    ->color('info')
                    ->placeholder('No Dept'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->defaultSort('created_at', 'desc')
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
            'index' => ManageUsers::route('/'),
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
