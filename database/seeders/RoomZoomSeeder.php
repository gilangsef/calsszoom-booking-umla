<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\ZoomLink;
use Illuminate\Database\Seeder;

class RoomZoomSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA RUANGAN
        $buildings = [
            'A' => [1, 2], // Gedung A lantai 1 & 2
            'B' => [1],    // Gedung B lantai 1
        ];

        foreach ($buildings as $building => $floors) {
            foreach ($floors as $floor) {
                // Pola: A102-109, A202-209, B101-109
                $start = ($building === 'A') ? 2 : 1;

                for ($i = $start; $i <= 9; $i++) {
                    $roomCode = "{$building}{$floor}0{$i}";

                    // Menggunakan updateOrCreate agar ID lanjut & tidak bentrok
                    Room::updateOrCreate(
                        ['code' => $roomCode], // Kunci pencarian (Unique)
                        [
                            'name' => $roomCode,
                            'building' => "Gedung {$building}",
                            'floor' => $floor,
                            'capacity' => 32,
                            'facilities' => ['Proyektor', 'TV LED', 'Whiteboard', 'WiFi', 'Komputer', 'AC'],
                            'status' => 'available',
                            'description' => null,
                            'image' => null,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // 2. DATA ZOOM (Room11 - 30)
        for ($i = 11; $i <= 30; $i++) {
            ZoomLink::updateOrCreate(
                ['name' => "Room{$i}"], // Kunci pencarian
                [
                    'meeting_id' => '923 5820 2510',
                    'meeting_url' => 'https://zoom.us/j/92358202510',
                    'passcode' => 'UMLA',
                    'host_email' => 'umla@gmail.com',
                    'status' => 'available',
                    'description' => 'gas',
                    'is_active' => true,
                ]
            );
        }
    }
}
