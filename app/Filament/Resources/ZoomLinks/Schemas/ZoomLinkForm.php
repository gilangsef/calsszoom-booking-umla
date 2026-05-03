<?php

namespace App\Filament\Resources\ZoomLinks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ZoomLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Zoom Meeting')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama / ID')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Zoom Room 1'),

                    Grid::make(2)->schema([
                        TextInput::make('meeting_id')
                            ->label('Meeting ID')
                            ->required()
                            ->unique(ignoreRecord: true),

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
                    ]),
            ]);
    }
}
