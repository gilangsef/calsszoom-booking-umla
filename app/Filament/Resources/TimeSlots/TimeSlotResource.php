<?php

namespace App\Filament\Resources\TimeSlots;

use App\Filament\Resources\TimeSlots\Pages\ManageTimeSlots;
use App\Models\TimeSlot;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TimeSlotResource extends Resource
{
    protected static ?string $model = TimeSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Data Jam';

    protected static ?string $pluralModelLabel = 'Data Jam';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sesi/SKS')
                    ->description('Atur durasi dan bobot SKS untuk peminjaman ruangan.')
                     ->icon('heroicon-m-information-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nama Sesi ')
                                ->placeholder('(Contoh: SKS 1)')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('total_sks')
                                ->label('Bobot SKS')
                                ->required()
                                ->numeric()
                                ->suffix('SKS'),

                            TextInput::make('duration')
                                ->label('Durasi Waktu')
                                ->required()
                                ->numeric()
                                ->suffix('Menit'),

                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->default(true)
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Sesi/SKS')
                ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Nama Sesi')
                                ->columnSpanFull()
                                ->weight('bold')
                                ->size('lg'),

                            TextEntry::make('total_sks')
                                ->label('Bobot SKS')
                                ->numeric()
                                ->suffix(' SKS')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('duration')
                                ->label('Durasi')
                                ->numeric()
                                ->suffix(' Menit'),

                            IconEntry::make('is_active')
                                ->label('Status')
                                ->boolean(),
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
                TextColumn::make('name')
                    ->label('Nama Sesi')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('total_sks')
                    ->label('SKS')
                    ->numeric()
                    ->suffix(' SKS')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('duration')
                    ->label('Durasi')
                    ->numeric()
                    ->suffix(' Menit')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah Pada')
                    ->dateTime('d M Y, H:i')
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
            'index' => ManageTimeSlots::route('/'),
        ];
    }
}
