<?php

namespace App\Enums;

enum RoomStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case LOCKED = 'locked';

    public function getLabel(): string
    {
        return match($this) {
            self::AVAILABLE => 'Tersedia',
            self::OCCUPIED => 'Sedang Digunakan',
            self::UNDER_MAINTENANCE => 'Dalam Perbaikan',
            self::LOCKED => 'Terkunci',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::AVAILABLE => 'success',
            self::OCCUPIED => 'warning',
            self::UNDER_MAINTENANCE => 'danger',
            self::LOCKED => 'gray',
        };
    }
}