<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache agar perubahan langsung terasa
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. DAFTAR SEMUA PERMISSION
        $permissions = [
            // --- Custom Logic Permissions ---
            'booking.create', 'booking.verify.admin', 'booking.verify.operational', 'booking.scan.qr', 
            'booking.return.key', 'booking.cancel.own', 'report.export',
            'dashboard.admin', 'dashboard.operational', 'dashboard.pimpinan',

            // --- Damage Report Permissions ---
            'view_any_damage::report', 'view_damage::report', 'create_damage::report', 
            'update_damage::report', 'delete_damage::report',

            // --- Room Booking Permissions ---
            'view_any_room::booking', 'view_room::booking', 'create_room::booking', 
            'update_room::booking', 'delete_room::booking',

            // --- Room Permissions ---
            'view_any_room', 'view_room', 'create_room', 'update_room', 'delete_room',

            // --- User Permissions ---
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user',

            // --- Zoom Booking Permissions ---
            'view_any_zoom::booking', 'view_zoom::booking', 'create_zoom::booking', 
            'update_zoom::booking', 'delete_zoom::booking',

            // --- Zoom Link Permissions ---
            'view_any_zoom::link', 'view_zoom::link', 'create_zoom::link', 
            'update_zoom::link', 'delete_zoom::link',

            // --- Role Permissions (Spatie/Shield) ---
            'view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role',
        ];

        // Masukkan ke Database
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // 2. DEFINISI ROLE & ASSIGN PERMISSION

        // ADMIN: Ambil semua permission yang ada
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // DOSEN: Akses terbatas (buat booking & lapor kerusakan)
        $dosen = Role::firstOrCreate(['name' => 'dosen', 'guard_name' => 'web']);
        $dosen->syncPermissions([
            'view_any_room', 'view_room',
            'view_any_room::booking', 'view_room::booking', 'create_room::booking', 'booking.create', 'booking.cancel.own',
            'view_any_zoom::booking', 'view_zoom::booking', 'create_zoom::booking',
            'create_damage::report', 'view_any_damage::report',
        ]);

        // STUDENT: Mirip dosen tapi biasanya tanpa akses Zoom tertentu
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'view_any_room', 'view_room',
            'view_any_room::booking', 'view_room::booking', 'create_room::booking', 'booking.create', 'booking.cancel.own',
        ]);

        // OPERASIONAL: Fokus pada verifikasi fisik & damage report
        $operasional = Role::firstOrCreate(['name' => 'operasional', 'guard_name' => 'web']);
        $operasional->syncPermissions([
            'booking.verify.operational', 'booking.scan.qr', 'booking.return.key',
            'view_any_room::booking', 'view_room::booking', 'update_room::booking',
            'view_any_damage::report', 'view_damage::report', 'create_damage::report', 'update_damage::report',
        ]);

        $this->command->info('✅ Role dan Permission berhasil disinkronkan!');
    }
}
