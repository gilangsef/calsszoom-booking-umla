<?php

namespace App\Filament\Resources\ZoomBookings\Pages;

use App\Filament\Resources\ZoomBookings\ZoomBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageZoomBookings extends ManageRecords
{
    protected static string $resource = ZoomBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Booking Zoom Baru')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
