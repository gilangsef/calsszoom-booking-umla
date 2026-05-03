<?php

namespace App\Filament\Resources\ZoomLinks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ZoomLinkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                ->label('Nama Zoom'),

            TextEntry::make('meeting_id')
                ->label('Meeting ID')
                ->copyable(),

            TextEntry::make('meeting_url')
                ->label('Meeting URL')
                ->url(fn ($record) => $record->meeting_url, true)
                ->copyable(),

            TextEntry::make('passcode')
                ->label('Passcode'),

            TextEntry::make('host_email')
                ->label('Host Email'),

            TextEntry::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'available' => 'success',
                    'occupied' => 'warning',
                    'inactive' => 'gray',
                    default => 'gray',
                }),

            IconEntry::make('is_active')
                ->label('Aktif')
                ->boolean(),

            TextEntry::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i'),

            TextEntry::make('updated_at')
                ->label('Diupdate')
                ->dateTime('d M Y H:i'),
            ]);
    }
}
