<?php

namespace App\Filament\Resources\RoomBookings\Pages;

use App\Filament\Resources\RoomBookings\RoomBookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRoomBooking extends ViewRecord
{
    protected static string $resource = RoomBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
