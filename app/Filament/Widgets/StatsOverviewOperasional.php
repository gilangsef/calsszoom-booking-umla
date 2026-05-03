<?php

namespace App\Filament\Widgets;

use App\Models\RoomBooking;
use App\Enums\BookingStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StatsOverviewOperasional extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            // 1. Menunggu Persetujuan (Status Pending)
            Stat::make('Menunggu Persetujuan', 
                RoomBooking::where('status', BookingStatus::PENDING)->count()
            )
                ->description('Segera proses permintaan baru')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([5, 10, 8, 15, 10, 20])
                ->color('warning'),

            // 2. Ruang Digunakan (Status In Use / Sedang Berlangsung)
            Stat::make('Ruang Digunakan', 
                RoomBooking::where('status', BookingStatus::IN_USE)->count()
            )
                ->description('Fasilitas aktif digunakan saat ini')
                ->descriptionIcon('heroicon-m-key')
                ->chart([2, 4, 6, 4, 8, 10])
                ->color('danger'),

            // 3. Jadwal Hari Ini (Semua booking yang tanggalnya hari ini)
            Stat::make('Jadwal Hari Ini', 
                RoomBooking::whereDate('booking_date', Carbon::today())
                    ->whereIn('status', [BookingStatus::VERIFIED, BookingStatus::IN_USE])
                    ->count()
            )
                ->description('Total agenda terverifikasi hari ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart([2, 5, 3, 8, 4, 10])
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

        // Cek permission spesifik yang sudah kamu generate
        return $user->can('View:StatsOverviewOperasional');
    }
}