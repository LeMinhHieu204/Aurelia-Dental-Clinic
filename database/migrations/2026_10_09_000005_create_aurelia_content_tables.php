<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) { $table->id(); $table->string('full_name'); $table->string('email')->index(); $table->string('phone'); $table->timestamps(); });
        Schema::create('doctor_specialties', function (Blueprint $table) { $table->id(); $table->string('name')->unique(); $table->string('slug')->unique(); $table->timestamps(); });
        Schema::create('locations', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->text('address')->nullable(); $table->string('phone')->nullable(); $table->string('email')->nullable(); $table->string('timezone')->default('UTC'); $table->json('opening_hours')->nullable(); $table->boolean('is_active')->default(false); $table->boolean('is_demo')->default(true); $table->timestamps(); $table->index(['is_active', 'is_demo']); });
        Schema::create('treatments', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->string('short_description', 500); $table->longText('overview')->nullable(); $table->string('cover_image_url', 500)->nullable(); $table->longText('process')->nullable(); $table->longText('benefits')->nullable(); $table->longText('risks_limitations')->nullable(); $table->longText('aftercare')->nullable(); $table->json('faqs')->nullable(); $table->string('duration_label')->nullable(); $table->decimal('starting_price', 10, 2)->nullable(); $table->boolean('consultation_required')->default(true); $table->boolean('is_published')->default(false); $table->boolean('is_demo')->default(true); $table->unsignedInteger('display_order')->default(0); $table->timestamps(); $table->index(['is_published', 'is_demo']); });
        Schema::create('doctors', function (Blueprint $table) { $table->id(); $table->string('full_name'); $table->string('slug')->unique(); $table->string('professional_title'); $table->longText('bio')->nullable(); $table->text('qualifications')->nullable(); $table->string('registration_identifier')->nullable(); $table->string('languages')->nullable(); $table->string('portrait_url', 500)->nullable(); $table->boolean('is_active')->default(false); $table->boolean('is_published')->default(false); $table->boolean('is_demo')->default(true); $table->unsignedInteger('display_order')->default(0); $table->timestamps(); $table->index(['is_active', 'is_published', 'is_demo']); });
        Schema::create('doctor_specialty', function (Blueprint $table) { $table->foreignId('doctor_id')->constrained()->cascadeOnDelete(); $table->foreignId('doctor_specialty_id')->constrained()->cascadeOnDelete(); $table->primary(['doctor_id', 'doctor_specialty_id']); });
        Schema::create('doctor_location', function (Blueprint $table) { $table->foreignId('doctor_id')->constrained()->cascadeOnDelete(); $table->foreignId('location_id')->constrained()->cascadeOnDelete(); $table->primary(['doctor_id', 'location_id']); });
        Schema::create('doctor_treatment', function (Blueprint $table) { $table->foreignId('doctor_id')->constrained()->cascadeOnDelete(); $table->foreignId('treatment_id')->constrained()->cascadeOnDelete(); $table->primary(['doctor_id', 'treatment_id']); });
    }
    public function down(): void { foreach (['doctor_treatment', 'doctor_location', 'doctor_specialty', 'doctors', 'treatments', 'locations', 'doctor_specialties', 'patients'] as $table) Schema::dropIfExists($table); }
};
