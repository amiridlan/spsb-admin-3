<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // First, update all 'hr_approved' status to 'pending'
        // These are requests that have HR approval but not Head approval yet
        DB::table('leave_requests')
            ->where('status', 'hr_approved')
            ->update(['status' => 'pending']);

        if ($driver === 'mysql') {
            // For MySQL, use ALTER TABLE to modify the ENUM
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            // For SQLite, we need to recreate the table (SQLite doesn't support ALTER COLUMN)
            // Drop and recreate the status column with new enum values
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('leave_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                    ->default('pending')
                    ->after('reason');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // For MySQL, restore the 'hr_approved' status to the ENUM
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'hr_approved', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        } else {
            // For SQLite, recreate with old enum values
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('leave_requests', function (Blueprint $table) {
                $table->enum('status', ['pending', 'hr_approved', 'approved', 'rejected', 'cancelled'])
                    ->default('pending')
                    ->after('reason');
            });
        }

        // Restore 'hr_approved' status for records that have hr_reviewed_by but not head_reviewed_by
        DB::table('leave_requests')
            ->where('status', 'pending')
            ->whereNotNull('hr_reviewed_by')
            ->whereNull('head_reviewed_by')
            ->update(['status' => 'hr_approved']);
    }
};
