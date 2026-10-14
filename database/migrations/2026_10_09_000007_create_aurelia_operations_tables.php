<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('email'); $table->string('phone')->nullable(); $table->string('subject')->nullable(); $table->text('message'); $table->string('status')->default('NEW')->index(); $table->timestamps(); });
        Schema::create('reviews', function (Blueprint $table) { $table->id(); $table->string('approved_name'); $table->string('location_label')->nullable(); $table->text('body'); $table->unsignedTinyInteger('rating')->nullable(); $table->date('review_date')->nullable(); $table->string('source')->nullable(); $table->boolean('permission_granted')->default(false); $table->boolean('is_published')->default(false); $table->boolean('is_demo')->default(true); $table->timestamps(); $table->index(['is_published', 'permission_granted', 'is_demo']); });
        Schema::create('media_assets', function (Blueprint $table) { $table->id(); $table->string('filename'); $table->string('path'); $table->string('mime_type'); $table->text('alt_text')->nullable(); $table->string('license')->nullable(); $table->text('attribution')->nullable(); $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); });
        Schema::create('content_sections', function (Blueprint $table) { $table->id(); $table->string('key')->unique(); $table->string('title')->nullable(); $table->longText('body')->nullable(); $table->boolean('is_published')->default(false); $table->timestamps(); });
        Schema::create('site_settings', function (Blueprint $table) { $table->id(); $table->string('key')->unique(); $table->text('value')->nullable(); $table->boolean('is_public')->default(false); $table->timestamps(); });
        Schema::create('audit_logs', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('action'); $table->nullableMorphs('auditable'); $table->json('metadata')->nullable(); $table->string('ip_hash', 64)->nullable(); $table->timestamps(); $table->index(['action', 'created_at']); });
    }
    public function down(): void { foreach (['audit_logs', 'site_settings', 'content_sections', 'media_assets', 'reviews', 'inquiries'] as $table) Schema::dropIfExists($table); }
};
