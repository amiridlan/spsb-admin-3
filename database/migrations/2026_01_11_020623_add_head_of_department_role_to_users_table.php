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

        if ($driver === 'mysql') {
            // MySQL: Use ALTER TABLE MODIFY
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'head_of_department', 'staff') NOT NULL DEFAULT 'staff'");
        } else {
            // SQLite and others: Drop and recreate the column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['superadmin', 'admin', 'head_of_department', 'staff'])
                    ->default('staff')
                    ->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert any head_of_department users to staff before removing the enum value
        DB::table('users')->where('role', 'head_of_department')->update(['role' => 'staff']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // MySQL: Use ALTER TABLE MODIFY
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'admin', 'staff') NOT NULL DEFAULT 'staff'");
        } else {
            // SQLite and others: Drop and recreate the column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['superadmin', 'admin', 'staff'])
                    ->default('staff')
                    ->after('email');
            });
        }
    }
};
