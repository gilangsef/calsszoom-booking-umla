<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\RoomBooking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentStats extends BaseWidget
{
    // Menentukan urutan widget agar muncul paling atas
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = Auth::id();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return [
            // 1. Total Booking Minggu Ini
            Stat::make('Booking Minggu Ini',
                RoomBooking::where('user_id', $userId)
                    ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
                    ->count()
            )
                ->description('Total jadwal kamu minggu ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            // 2. Status Late (Akumulasi Menit)
            Stat::make('Total Keterlambatan',
                RoomBooking::where('user_id', $userId)
                    ->sum('late_minutes') . ' Menit'
            )
                ->description('Akumulasi telat balikkan kunci')
                ->descriptionIcon('heroicon-m-clock')
                ->color(fn($state) => (int)$state > 0 ? 'danger' : 'success'),

            // 3. Active Booking (Verified & In Use)
            Stat::make('Booking Aktif',
                RoomBooking::where('user_id', $userId)
                    ->whereIn('status', [
                        BookingStatus::VERIFIED,
                        BookingStatus::IN_USE
                    ])
                    ->count()
            )
                ->description('Booking yang sedang berjalan/siap')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
        ];
    }

    /**
     * Menghubungkan Widget dengan Permission Spatie
     */
    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) return false;

        // Widget muncul jika user punya permission View:StudentStats
        return $user->can('View:StudentStats');
    }
}