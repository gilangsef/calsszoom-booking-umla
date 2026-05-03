<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. UPDATE DATA LAMA TERLEBIH DAHULU (PENTING!)
        // Ubah semua yang 'pending_admin' atau 'pending_operational' menjadi 'pending'
        DB::table('room_bookings')
            ->whereIn('status', ['pending_admin', 'pending_operational'])
            ->update(['status' => 'pending']);

        Schema::table('room_bookings', function (Blueprint $table) {
            // 2. Hapus kolom lama
            $table->dropForeign(['verified_by_admin']);
            $table->dropColumn(['verified_by_admin', 'verified_admin_at', 'admin_notes']);

            $table->dropForeign(['verified_by_operational']);
            $table->dropColumn(['verified_by_operational', 'verified_operational_at', 'operational_notes']);

            // 3. Tambahkan kolom baru
            $table->foreignId('verified_by')->after('status')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->after('verified_by')->nullable();
            $table->text('verification_notes')->after('verified_at')->nullable();
        });

        // 4. Update ENUM menggunakan Native SQL (Karena .change() sering bermasalah dengan ENUM)
        DB::statement("ALTER TABLE room_bookings MODIFY COLUMN status ENUM('pending', 'verified', 'in_use', 'completed', 'late_return', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('room_bookings', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'verification_notes']);

            // Kembalikan kolom lama (kosongan)
            $table->foreignId('verified_by_admin')->nullable()->constrained('users');
            $table->timestamp('verified_admin_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->foreignId('verified_by_operational')->nullable()->constrained('users');
            $table->timestamp('verified_operational_at')->nullable();
            $table->text('operational_notes')->nullable();
        });

        DB::statement("ALTER TABLE room_bookings MODIFY COLUMN status ENUM('pending_admin', 'pending_operational', 'verified', 'in_use', 'completed', 'late_return', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending_admin'");
    }
};
