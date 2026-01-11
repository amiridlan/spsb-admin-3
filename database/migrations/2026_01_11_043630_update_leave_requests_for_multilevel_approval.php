<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Step 1: Add new HR reviewer columns
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('hr_reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->text('hr_review_notes')->nullable()->after('hr_reviewed_by');
            $table->timestamp('hr_reviewed_at')->nullable()->after('hr_review_notes');
        });

        // Step 2: Rename existing reviewer columns to head_reviewer
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->renameColumn('reviewed_by', 'head_reviewed_by');
            $table->renameColumn('review_notes', 'head_review_notes');
            $table->renameColumn('reviewed_at', 'head_reviewed_at');
        });

        // Step 3: Update status enum to include 'hr_approved'
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'hr_approved', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            // SQLite: Drop index first, then drop and recreate column
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropIndex('leave_requests_status_index');
            });

            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('leave_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'hr_approved', 'approved', 'rejected', 'cancelled'])
                    ->default('pending')
                    ->after('reason')
                    ->index();
            });
        }

        // Step 4: Migrate existing approved requests
        // For existing approved requests, populate both hr_reviewed_by and head_reviewed_by with same user
        DB::table('leave_requests')
            ->where('status', 'approved')
            ->update([
                'hr_reviewed_by' => DB::raw('head_reviewed_by'),
                'hr_review_notes' => DB::raw('head_review_notes'),
                'hr_reviewed_at' => DB::raw('head_reviewed_at'),
            ]);

        // Step 5: Migrate existing rejected requests (assume rejected by HR)
        DB::table('leave_requests')
            ->where('status', 'rejected')
            ->update([
                'hr_reviewed_by' => DB::raw('head_reviewed_by'),
                'hr_review_notes' => DB::raw('head_review_notes'),
                'hr_reviewed_at' => DB::raw('head_reviewed_at'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Step 1: Revert status enum
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            // SQLite: Drop index first, then drop and recreate column
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropIndex('leave_requests_status_index');
            });

            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('leave_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                    ->default('pending')
                    ->after('reason')
                    ->index();
            });
        }

        // Step 2: Rename head_reviewer columns back to reviewer
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->renameColumn('head_reviewed_by', 'reviewed_by');
            $table->renameColumn('head_review_notes', 'review_notes');
            $table->renameColumn('head_reviewed_at', 'reviewed_at');
        });

        // Step 3: Drop HR reviewer columns
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['hr_reviewed_by']);
            $table->dropColumn(['hr_reviewed_by', 'hr_review_notes', 'hr_reviewed_at']);
        });
    }
};
