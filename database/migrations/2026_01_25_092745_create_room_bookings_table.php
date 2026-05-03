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
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique(); // AUTO: RB-YYYYMMDD-XXX
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Waktu peminjaman
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            
            // Tujuan & detail
            $table->string('purpose'); // Tujuan peminjaman
            $table->text('description')->nullable();
            $table->integer('participant_count')->default(0);
            
            // Status flow: pending_admin → pending_operational → verified → in_use → completed
            $table->enum('status', [
                'pending_admin',
                'pending_operational',
                'verified',
                'in_use',
                'completed',
                'late_return',
                'rejected',
                'cancelled'
            ])->default('pending_admin');
            
            // Admin Verification
            $table->foreignId('verified_by_admin')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_admin_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Operational Verification
            $table->foreignId('verified_by_operational')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_operational_at')->nullable();
            $table->text('operational_notes')->nullable();
            
            // QR Code System
            $table->string('qr_token')->nullable()->unique();
            $table->timestamp('qr_generated_at')->nullable();
            $table->timestamp('qr_expired_at')->nullable();
            
            // Key Management (Scan QR)
            $table->timestamp('key_taken_at')->nullable();
            $table->foreignId('key_taken_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamp('key_returned_at')->nullable();
            $table->foreignId('key_returned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Late Return Tracking
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            
            // Rejection
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes untuk performa query
            $table->index(['booking_date', 'start_time', 'end_time']);
            $table->index('status');
            $table->index('qr_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};
