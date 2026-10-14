<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('location_treatment')) {
            Schema::create('location_treatment', function (Blueprint $table): void {
                $table->foreignId('location_id')->constrained()->cascadeOnDelete();
                $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
                $table->primary(['location_id', 'treatment_id']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Preserve clinic assignments during rollback.
    }
};
