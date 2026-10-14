<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('doctors', 'user_id')) {
            Schema::table('doctors', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Do not remove clinic account assignments automatically.
    }
};
