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
        Schema::create('zoom_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique(); // AUTO: ZB-YYYYMMDD-XXX
            $table->foreignId('zoom_link_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            
            $table->string('purpose');
            $table->text('description')->nullable();
            $table->integer('participant_count')->default(0);
            
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'completed',
                'cancelled'
            ])->default('pending');
            
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['booking_date', 'start_time', 'end_time']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_bookings');
    }
};
