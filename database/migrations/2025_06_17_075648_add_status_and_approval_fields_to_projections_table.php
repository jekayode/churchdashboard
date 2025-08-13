<?php

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
        Schema::table('projections', function (Blueprint $table) {
            // Add status tracking for approval workflow
            $table->enum('status', ['draft', 'in_review', 'approved', 'rejected'])
                  ->default('draft')
                  ->after('created_by');
            
            // Add approval fields
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            
            // Add rejection fields
            $table->foreignId('rejected_by')->nullable()->constrained('users')->after('approval_notes');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            
            // Add current year designation
            $table->boolean('is_current_year')->default(false)->after('rejection_reason');
            
            // Add submission tracking
            $table->timestamp('submitted_at')->nullable()->after('is_current_year');
            
            // Add indexes for performance
            $table->index('status');
            $table->index('approved_by');
            $table->index('rejected_by');
            $table->index('is_current_year');
            $table->index(['branch_id', 'is_current_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projections', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['branch_id', 'is_current_year']);
            $table->dropIndex(['is_current_year']);
            $table->dropIndex(['rejected_by']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['status']);
            
            // Drop columns
            $table->dropColumn([
                'status',
                'approved_by',
                'approved_at',
                'approval_notes',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
                'is_current_year',
                'submitted_at'
            ]);
        });
    }
};
