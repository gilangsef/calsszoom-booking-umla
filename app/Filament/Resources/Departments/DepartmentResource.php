<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ManageDepartments;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string |UnitEnum| null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Data Prodi';

    protected static ?string $pluralModelLabel = 'Data Prodi';

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
                                Select::make('faculty_id')
                                    ->label('Fakultas')
                                    ->relationship('faculty', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Fakultas')
                                    ->required(),

                                TextInput::make('name')
                                    ->label('Nama Prodi')
                                    ->placeholder('Contoh : Teknik Komputer')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Kode')
                                    ->placeholder('TKOM')
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
                 Section::make('Informasi Prodi')
                ->description('Detail data master Prodi.')
                ->icon('heroicon-m-academic-cap')
                    ->schema([
                        Group::make([
                            TextEntry::make('faculty.name')
                                ->label('Nama Fakultas')
                                ->badge()
                                ->color('primary'),

                            TextEntry::make('name')
                                ->label('Nama Prodi')
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
                TextColumn::make('faculty.name')
                    ->label('Fakultas')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Kode')
                    ->copyable()
                    ->fontFamily('mono')
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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDepartments::route('/'),
        ];
    }
}
