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
        Schema::create('zoom_links', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama identifier (Zoom Room 1)
            $table->string('meeting_id')->unique();
            $table->string('meeting_url');
            $table->string('passcode')->nullable();
            $table->string('host_email')->nullable();
            $table->enum('status', ['available', 'occupied', 'inactive'])->default('available');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_links');
    }
};
