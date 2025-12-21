<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->boolean('is_active')->default(true)->after('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_spaces');
        Schema::table('event_spaces', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
