<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('locations', 'country')) $table->string('country')->nullable();
            if (! Schema::hasColumn('locations', 'city')) $table->string('city')->nullable();
            if (! Schema::hasColumn('locations', 'map_url')) $table->string('map_url', 500)->nullable();
            if (! Schema::hasColumn('locations', 'public_visibility')) $table->boolean('public_visibility')->default(true);
            if (! Schema::hasColumn('locations', 'closure_days')) $table->json('closure_days')->nullable();
        });

        Schema::table('doctors', function (Blueprint $table): void {
            if (! Schema::hasColumn('doctors', 'archived_at')) $table->timestamp('archived_at')->nullable();
        });

        Schema::table('treatments', function (Blueprint $table): void {
            if (! Schema::hasColumn('treatments', 'duration_minutes')) $table->unsignedSmallInteger('duration_minutes')->nullable();
            if (! Schema::hasColumn('treatments', 'price_currency')) $table->string('price_currency', 3)->default('USD');
            if (! Schema::hasColumn('treatments', 'seo_title')) $table->string('seo_title')->nullable();
            if (! Schema::hasColumn('treatments', 'seo_description')) $table->string('seo_description', 320)->nullable();
            if (! Schema::hasColumn('treatments', 'requires_approval')) $table->boolean('requires_approval')->default(true);
        });

        Schema::table('appointment_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('appointment_requests', 'scheduled_start_utc')) $table->dateTime('scheduled_start_utc')->nullable();
            if (! Schema::hasColumn('appointment_requests', 'scheduled_end_utc')) $table->dateTime('scheduled_end_utc')->nullable();
            if (! Schema::hasColumn('appointment_requests', 'timezone')) $table->string('timezone')->nullable();
            if (! Schema::hasColumn('appointment_requests', 'internal_notes')) $table->text('internal_notes')->nullable();
            if (! Schema::hasColumn('appointment_requests', 'confirmed_at')) $table->dateTime('confirmed_at')->nullable();
            $table->index(['doctor_id', 'scheduled_start_utc', 'scheduled_end_utc', 'status'], 'appointment_scheduled_window_idx');
        });

        Schema::table('appointment_status_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('appointment_status_histories', 'metadata')) $table->json('metadata')->nullable();
        });

        Schema::table('inquiries', function (Blueprint $table): void {
            if (! Schema::hasColumn('inquiries', 'internal_notes')) $table->text('internal_notes')->nullable();
            if (! Schema::hasColumn('inquiries', 'assigned_to')) $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('media_assets', function (Blueprint $table): void {
            if (! Schema::hasColumn('media_assets', 'disk')) $table->string('disk')->default('public');
            if (! Schema::hasColumn('media_assets', 'size_bytes')) $table->unsignedBigInteger('size_bytes')->nullable();
            if (! Schema::hasColumn('media_assets', 'width')) $table->unsignedInteger('width')->nullable();
            if (! Schema::hasColumn('media_assets', 'height')) $table->unsignedInteger('height')->nullable();
            if (! Schema::hasColumn('media_assets', 'source')) $table->string('source')->nullable();
            if (! Schema::hasColumn('media_assets', 'sha256')) $table->string('sha256', 64)->nullable()->index();
            if (! Schema::hasColumn('media_assets', 'is_public')) $table->boolean('is_public')->default(true);
        });

        Schema::table('content_sections', function (Blueprint $table): void {
            if (! Schema::hasColumn('content_sections', 'sort_order')) $table->unsignedInteger('sort_order')->default(0);
            if (! Schema::hasColumn('content_sections', 'seo_title')) $table->string('seo_title')->nullable();
            if (! Schema::hasColumn('content_sections', 'seo_description')) $table->string('seo_description', 320)->nullable();
            if (! Schema::hasColumn('content_sections', 'published_at')) $table->timestamp('published_at')->nullable();
            if (! Schema::hasColumn('content_sections', 'updated_by')) $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        $now = now();
        foreach ([
            ['name' => 'super_admin', 'description' => 'Full system access, including security and audit controls.'],
            ['name' => 'receptionist', 'description' => 'Appointments, scheduling, inquiries, and limited patient contacts.'],
            ['name' => 'doctor', 'description' => 'Assigned appointment schedule and limited operational data.'],
        ] as $role) {
            DB::table('roles')->updateOrInsert(['name' => $role['name']], $role + ['created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // Phase 3 rollback is intentionally conservative. Additive columns remain recoverable
        // until an operator has reviewed production backups and explicitly removes them.
    }
};
