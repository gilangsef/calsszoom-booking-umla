<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
{
    // TAHAP 1: Ubah kolom menjadi VARCHAR dulu agar bisa menerima teks apapun
    DB::statement("ALTER TABLE room_bookings MODIFY COLUMN status VARCHAR(255)");

    // TAHAP 2: Bersihkan datanya (Ubah semua yang aneh-aneh jadi 'pending')
    DB::table('room_bookings')
        ->whereNotIn('status', ['pending', 'verified', 'rejected', 'in_use', 'completed', 'cancelled'])
        ->update(['status' => 'pending']);

    // TAHAP 3: Kembalikan ke ENUM dengan definisi baru
    DB::statement("ALTER TABLE room_bookings MODIFY COLUMN status ENUM(
        'pending',
        'verified',
        'rejected',
        'in_use',
        'completed',
        'cancelled'
    ) DEFAULT 'pending'");
}

    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            // Kembalikan ke struktur lama jika diperlukan (rollback)
            DB::statement("ALTER TABLE room_bookings MODIFY COLUMN status ENUM(
                'pending_admin',
                'pending_operational',
                'verified',
                'rejected'
            ) DEFAULT 'pending_admin'");
        });
    }
};
