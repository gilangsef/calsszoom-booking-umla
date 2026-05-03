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
        Schema::table('zoom_links', function (Blueprint $table) {
            // Menghapus index unique
            $table->dropUnique('zoom_links_meeting_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('zoom_links', function (Blueprint $table) {
            $table->unique('meeting_id');
        });
    }
};
