<?php

namespace App\Filament\Widgets;

use App\Models\RoomBooking;
use App\Models\ZoomBooking;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarWidget extends Widget
{
    protected string $view = 'filament.widgets.calendar-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    protected function getViewData(): array
{
    $days = [];
    $now = Carbon::today();

    for ($i = 0; $i < 7; $i++) {
        $currentDate = $now->copy()->addDays($i);
        $dateString = $currentDate->toDateString();

        $rooms = RoomBooking::with('room')
            ->whereDate('booking_date', $dateString)
            ->get()
            ->map(fn($item) => [
                'title' => $item->room->name ?? 'N/A',
                'subtitle' => $item->purpose,
                'start' => $item->start_time,
                'end' => $item->end_time,
                'icon' => 'heroicon-m-home-modern',
                'color' => 'blue',
            ]);

        $zooms = ZoomBooking::whereDate('booking_date', $dateString)
            ->get()
            ->map(fn($item) => [
                'title' => 'Zoom Meeting',
                'subtitle' => $item->topic,
                'start' => $item->start_time,
                'end' => $item->end_time,
                'icon' => 'heroicon-m-video-camera',
                'color' => 'emerald',
            ]);

        $days[] = [
            'date_label' => $currentDate->translatedFormat('D'),
            'date_number' => $currentDate->format('d M'),
            'is_today' => $currentDate->isToday(),
            'events' => $rooms->concat($zooms)->sortBy('start'), // Nama key-nya 'events'
        ];
    }

    return ['calendar' => $days];
}
}
