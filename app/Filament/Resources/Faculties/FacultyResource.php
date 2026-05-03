<?php

namespace App\Filament\Resources\Faculties;

use App\Filament\Resources\Faculties\Pages\ManageFaculties;
use App\Models\Faculty;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string |UnitEnum| null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Data Fakultas';

    protected static ?string $pluralModelLabel = 'Data Fakultas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Faculty Details')
                    ->description('Masukan informasi dasar fakultas di sini.')
                    ->icon('heroicon-m-information-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2) // Membagi menjadi 2 kolom
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Fakultas')
                                    ->placeholder('e.g. Faculty of Engineering')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Kode')
                                    ->placeholder('e.g. FT / FE')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(10),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Fakultas')
                ->description('Detail data master fakultas.')
                ->icon('heroicon-m-academic-cap')
                    ->schema([
                        Group::make([
                            TextEntry::make('name')
                                ->label('Nama Fakultas')
                                ->weight('bold')
                                ->size('lg')
                                ->color('primary'),

                            TextEntry::make('code')
                                ->label('Kode Fakultas')
                                ->badge()
                                ->color('info'),
                        ]),
                    ]),

                Section::make('Informasi Sistem')
                ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('Dibuat Pada')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),

                            TextEntry::make('updated_at')
                                ->label('Terakhir Diubah')
                                ->dateTime('d M Y, H:i')
                                ->placeholder('-'),
                        ]),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge() // Membuat kode terlihat seperti label
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Fakultas')
                    ->description(fn (Faculty $record): string => "Kode: {$record->code}") // Subtitle di bawah nama
                    ->searchable()
                    ->sortable(),

                TextColumn::make('departments_count')
                    ->label('Total Prodi')
                    ->counts('departments') // Menampilkan jumlah prodi di fakultas tersebut
                    ->badge()
                    ->color('gray'),
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
                ViewAction::make()
                ->label('Lihat'),
                EditAction::make()
                ->label('Edit'),
                DeleteAction::make()
                ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->label('Hapus Pilihan'),
                ])
                ->label('Tindakan'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaculties::route('/'),
        ];
    }
}
