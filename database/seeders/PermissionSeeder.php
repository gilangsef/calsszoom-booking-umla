<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar permission kustom yang ingin didaftarkan ke sistem
        $customPermissions = [
            'VerifyZoom:ZoomBooking',
            // 'Generate:QR',
        ];

        foreach ($customPermissions as $permissionName) {
            // firstOrCreate memastikan tidak ada duplikat jika seeder dijalankan ulang
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info('Custom permissions have been registered to the database.');
    }
}
