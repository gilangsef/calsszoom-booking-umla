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
        Schema::table('users', function (Blueprint $table) {
            // user prodi
            $table->foreignId('department_id')
            ->nullable()
            ->after('id')
            ->constrained()
            ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Hapus foreign key-nya dulu
            $table->dropForeign(['department_id']);

            // 2. Baru hapus kolomnya
            $table->dropColumn('department_id');
        });
    }
};
