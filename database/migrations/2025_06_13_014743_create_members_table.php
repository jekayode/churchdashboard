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
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('anniversary')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('occupation')->nullable();
            $table->string('nearest_bus_stop')->nullable();
            $table->date('date_joined')->nullable();
            $table->date('date_attended_membership_class')->nullable();
            $table->enum('teci_status', [
                'not_started', '100_level', '200_level', '300_level', 
                '400_level', '500_level', 'graduated', 'paused'
            ])->default('not_started');
            $table->enum('growth_level', ['core', 'pastor', 'growing', 'new_believer'])->default('new_believer');
            $table->json('leadership_trainings')->nullable();
            $table->enum('member_status', ['visitor', 'member', 'volunteer', 'leader', 'minister'])->default('visitor');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('member_status');
            $table->index('teci_status');
            $table->index('growth_level');
            $table->index('deleted_at');
            $table->index(['branch_id', 'member_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
