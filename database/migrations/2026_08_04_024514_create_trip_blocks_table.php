<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // meal | hangout | find | note
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedSmallInteger('day_index')->nullable();
            $table->time('planned_time')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('added_by_name')->nullable();
            $table->string('source')->default('host'); // host | guest
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_blocks');
    }
};
