<?php

namespace App\Enums;

enum DamageReportStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Ditangani',
            self::IN_PROGRESS => 'Sedang Ditangani',
            self::RESOLVED => 'Sudah Diperbaiki',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::IN_PROGRESS => 'info',
            self::RESOLVED => 'success',
            self::CANCELLED => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::IN_PROGRESS => 'heroicon-o-check-circle',
            self::RESOLVED => 'heroicon-o-check-badge',
            self::CANCELLED => 'heroicon-o-x-mark',
        };
    }
}
