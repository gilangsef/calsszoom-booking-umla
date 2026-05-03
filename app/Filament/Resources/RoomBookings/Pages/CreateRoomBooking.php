<?php

namespace App\Filament\Resources\RoomBookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\RoomBookings\RoomBookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRoomBooking extends CreateRecord
{
    protected static string $resource = RoomBookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Otomatis set user_id ke user yang sedang login
        $data['user_id'] = Auth::id();

        // Anda juga bisa memastikan status awal di sini jika belum di-set
        $data['status'] = BookingStatus::PENDING;

        return $data;
    }
}
