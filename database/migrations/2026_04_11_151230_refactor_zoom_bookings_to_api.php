<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // GANTI 'api' MENJADI 'zoom_bookings'
        Schema::table('zoom_bookings', function (Blueprint $table) {
            // 1. Putus relasi Foreign Key DULU sebelum hapus kolom
            // Format array akan otomatis mencari nama constraint: tabel_kolom_foreign
            $table->dropForeign(['zoom_link_id']);

            // 2. Setelah relasi putus, baru kolomnya dihapus
            if (Schema::hasColumn('zoom_bookings', 'zoom_link_id')) {
                $table->dropColumn('zoom_link_id');
            }

            // 3. Tambah kolom hasil generate API Zoom
            $table->string('zoom_link')->nullable()->after('purpose');
            $table->string('zoom_meeting_id')->nullable()->after('zoom_link');
            $table->string('zoom_password')->nullable()->after('zoom_meeting_id');

            // 4. Tambah kategori (Perkuliahan/Acara)
            $table->string('zoom_category')->nullable()->after('zoom_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // GANTI 'api' MENJADI 'zoom_bookings'
        Schema::table('zoom_bookings', function (Blueprint $table) {
            // 1. Hapus semua kolom baru yang tadi dibuat di method up()
            $table->dropColumn([
                'zoom_link',
                'zoom_meeting_id',
                'zoom_password',
                'zoom_category'
            ]);

            // 2. Kembalikan kolom lama yang tadi dihapus di method up()
            // Asumsi tipe datanya dulu integer/foreign ID, jika dulu string ganti ke $table->string('zoom_link_id')
            $table->unsignedBigInteger('zoom_link_id')->nullable();
        });
    }
};
