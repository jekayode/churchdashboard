<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_settings', function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp_group_link')->nullable();
            $table->string('google_drive_link')->nullable();
            $table->text('intro_text')->nullable();
            $table->text('confirmation_body')->nullable();
            $table->timestamps();
        });

        Schema::create('builder_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('builder_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('phone');
            $table->string('email');
            $table->string('business_name');
            $table->text('business_description');
            $table->string('business_stage');
            $table->string('industry');
            $table->string('industry_other')->nullable();
            $table->string('biggest_challenge');
            $table->text('success_vision');
            $table->string('cac_status');
            $table->string('status')->default('new');
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('contacted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('email');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_registrations');
        Schema::dropIfExists('builder_resources');
        Schema::dropIfExists('builder_settings');
    }
};
