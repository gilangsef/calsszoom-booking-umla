<?php

namespace App\Filament\Resources\RoomBookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\RoomBookings\RoomBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRoomBookings extends ListRecords
{
    protected static string $resource = RoomBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Booking Room Baru')
                ->icon('heroicon-o-plus-circle')
                // Gunakan mutateFormDataUsing untuk menyuntikkan user_id
                // ->mutateFormDataUsing(function (array $data): array {
                //     $data['user_id'] = Auth::id();

                //     // SESUAIKAN DI SINI:
                //     //  PENDING
                //     $data['status'] = $data['status'] ?? BookingStatus::PENDING;

                //     return $data;
                // })
                // Opsional: Beri notifikasi sukses yang lebih jelas
                ->successNotificationTitle('Peminjaman berhasil dibuat!'),
        ];
    }
}
