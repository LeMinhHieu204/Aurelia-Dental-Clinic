<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_availability', function (Blueprint $table): void {
            if (! Schema::hasColumn('doctor_availability', 'available_date')) {
                $table->date('available_date')->nullable()->after('location_id');
                $table->index(['doctor_id', 'available_date', 'is_active'], 'doctor_availability_exception_idx');
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. Preserve exceptional hours during rollback.
    }
};
