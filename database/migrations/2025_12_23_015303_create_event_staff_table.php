<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->string('role')->nullable(); // e.g., 'coordinator', 'technician', 'security'
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent duplicate assignments
            $table->unique(['event_id', 'staff_id']);

            $table->index('event_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_staff');
    }
};
