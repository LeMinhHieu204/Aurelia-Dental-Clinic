<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table): void {
            if (! Schema::hasColumn('treatments', 'archived_at')) $table->timestamp('archived_at')->nullable();
        });

        Schema::table('content_sections', function (Blueprint $table): void {
            if (! Schema::hasColumn('content_sections', 'image_url')) $table->string('image_url', 500)->nullable();
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. Preserve archive state and CMS image references during rollback.
    }
};
