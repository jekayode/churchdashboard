<?php

declare(strict_types=1);

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
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('venue');
            $table->string('service_time');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('map_embed_code')->nullable();
            $table->foreignId('pastor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('pastor_id');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
