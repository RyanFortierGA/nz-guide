<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('category'); // flying | weekend | local
            $table->string('mode'); // plane | car | ferry | walk
            $table->string('travel_time');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->text('description');
            $table->string('best_time')->nullable();
            $table->json('activities')->nullable();
            $table->text('image_url')->nullable();
            $table->text('image_url_2')->nullable();
            $table->string('airport_code', 8)->nullable();
            $table->string('airbnb_query')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
