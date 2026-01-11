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
        Schema::table('staff', function (Blueprint $table) {
            $table->integer('annual_leave_total')->default(15)->after('notes');
            $table->integer('annual_leave_used')->default(0)->after('annual_leave_total');
            $table->integer('sick_leave_total')->default(10)->after('annual_leave_used');
            $table->integer('sick_leave_used')->default(0)->after('sick_leave_total');
            $table->integer('emergency_leave_total')->default(5)->after('sick_leave_used');
            $table->integer('emergency_leave_used')->default(0)->after('emergency_leave_total');
            $table->text('leave_notes')->nullable()->after('emergency_leave_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'annual_leave_total',
                'annual_leave_used',
                'sick_leave_total',
                'sick_leave_used',
                'emergency_leave_total',
                'emergency_leave_used',
                'leave_notes',
            ]);
        });
    }
};
