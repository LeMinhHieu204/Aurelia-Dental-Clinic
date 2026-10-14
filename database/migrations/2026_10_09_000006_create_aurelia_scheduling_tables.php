<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_availability', function (Blueprint $table) { $table->id(); $table->foreignId('doctor_id')->constrained()->cascadeOnDelete(); $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete(); $table->unsignedTinyInteger('weekday'); $table->time('starts_at'); $table->time('ends_at'); $table->string('timezone')->default('UTC'); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['doctor_id', 'weekday', 'is_active']); });
        Schema::create('schedule_blocks', function (Blueprint $table) { $table->id(); $table->foreignId('doctor_id')->constrained()->cascadeOnDelete(); $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete(); $table->date('blocked_date'); $table->time('starts_at')->nullable(); $table->time('ends_at')->nullable(); $table->string('reason')->nullable(); $table->timestamps(); $table->index(['doctor_id', 'blocked_date']); });
        Schema::create('appointment_requests', function (Blueprint $table) { $table->id(); $table->foreignId('patient_id')->constrained()->cascadeOnDelete(); $table->foreignId('treatment_id')->constrained()->restrictOnDelete(); $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete(); $table->foreignId('location_id')->constrained()->restrictOnDelete(); $table->string('reference')->unique(); $table->date('preferred_date'); $table->time('preferred_time')->nullable(); $table->string('status')->default('REQUESTED')->index(); $table->text('message')->nullable(); $table->boolean('privacy_acknowledgement'); $table->string('source_ip_hash', 64)->nullable(); $table->timestamps(); $table->index(['doctor_id', 'preferred_date', 'preferred_time', 'status'], 'appointment_doctor_slot_status_idx'); });
        Schema::create('appointment_status_histories', function (Blueprint $table) { $table->id(); $table->foreignId('appointment_request_id')->constrained()->cascadeOnDelete(); $table->string('status'); $table->text('notes')->nullable(); $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); });
    }
    public function down(): void { foreach (['appointment_status_histories', 'appointment_requests', 'schedule_blocks', 'doctor_availability'] as $table) Schema::dropIfExists($table); }
};
