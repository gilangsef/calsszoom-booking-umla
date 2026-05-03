<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case IN_USE = 'in_use';
    case COMPLETED = 'completed';
    case LATE_RETURN = 'late_return';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VERIFIED => 'Disetujui - Menunggu Pengambilan Kunci',
            self::IN_USE => 'Sedang Digunakan',
            self::COMPLETED => 'Selesai',
            self::LATE_RETURN => 'Terlambat Mengembalikan',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::VERIFIED => 'success',
            self::IN_USE => 'primary',
            self::COMPLETED => 'success',
            self::LATE_RETURN => 'danger',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::VERIFIED => 'heroicon-o-check-circle',
            self::IN_USE => 'heroicon-o-key',
            self::COMPLETED => 'heroicon-o-check-badge',
            self::LATE_RETURN => 'heroicon-o-exclamation-triangle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-x-mark',
        };
    }
}
