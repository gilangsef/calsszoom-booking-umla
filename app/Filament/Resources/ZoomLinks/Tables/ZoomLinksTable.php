<?php

namespace App\Filament\Resources\ZoomLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\ZoomLink;

class ZoomLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Hapus baris ganda ->columns([ yang tadi ada di sini
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
            ->actions([ // Gunakan actions() bukan recordActions() untuk standar Filament v3
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([ // Gunakan bulkActions() bukan toolbarActions() untuk DeleteBulkAction
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
