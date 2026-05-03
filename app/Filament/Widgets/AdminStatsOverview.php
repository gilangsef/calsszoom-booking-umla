<?php

namespace App\Filament\Widgets;

use App\Models\ZoomBooking;
use App\Models\DamageReport;
use App\Models\RoomBooking;
use App\Enums\DamageReportStatus;
use App\Enums\BookingStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '15s';

    /**
     * Menghubungkan Widget dengan Permission Spatie
     */
    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) return false;

        // Widget ini muncul jika user adalah Admin 
        // ATAU punya permission untuk melihat dashboard (misal 'View:AdminStats')
        return $user->can('View:AdminStatsOverview');
    }

    protected function getStats(): array
    {
        // Pastikan User yang bisa melihat widget ini juga punya akses ke data di dalamnya
        return [
            // Stats 1: Booking Kelas
            Stat::make('Booking Kelas Hari Ini', 
                RoomBooking::whereDate('booking_date', Carbon::today())
                    ->whereIn('status', [BookingStatus::VERIFIED, BookingStatus::IN_USE])
                    ->count()
            )
                ->description('Penggunaan ruangan aktif')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('info'),

            // Stats 2: Booking Zoom
            Stat::make('Booking Zoom Hari Ini', 
                ZoomBooking::whereDate('booking_date', Carbon::today())
                    ->where('status', 'verified')
                    ->count()
            )
                ->description('Sesi meeting terjadwal')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('success'),

            // Stats 3: Laporan Kerusakan
            Stat::make('Laporan Kerusakan Pending', 
                DamageReport::where('status', DamageReportStatus::PENDING)->count()
            )
                ->description('Butuh tindakan segera')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),
        ];
    }
}