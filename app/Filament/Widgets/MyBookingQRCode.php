<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\RoomBooking;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MyBookingQRCode extends Widget
{
    protected string $view = 'filament.widgets.my-booking-qr-code';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    /**
     * Menghubungkan Widget dengan Permission Spatie
     */
    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) return false;

        // Widget ini muncul hanya jika user memiliki permission spesifik
        // atau jika Anda ingin tetap menyertakan logika Role (isStudent/isDosen)
        return $user->can('View:MyBookingQRCode');
    }

    protected function getViewData(): array
    {
        return [
            'bookings' => $this->getBookingsWithQR(),
        ];
    }

    public function getBookingsWithQR()
    {
        return RoomBooking::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', [
                BookingStatus::VERIFIED,
                BookingStatus::IN_USE
            ])
            ->whereNotNull('qr_token')
            ->whereDate('booking_date', '>=', now()->toDateString())
            ->with(['room'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();
    }
}