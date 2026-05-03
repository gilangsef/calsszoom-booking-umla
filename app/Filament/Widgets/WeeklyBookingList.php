<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\RoomBooking;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WeeklyBookingList extends Widget
{
    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.weekly-booking-list';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'weeklyBookings' => RoomBooking::query()
                ->where('user_id', Auth::id())
                ->whereBetween('booking_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->with('room')
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->get()
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->isStudent() || $user->isDosen());
    }
}
