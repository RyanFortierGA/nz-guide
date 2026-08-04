<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('home_name')->nullable()->after('password');
            $table->decimal('home_lat', 10, 7)->nullable()->after('home_name');
            $table->decimal('home_lng', 10, 7)->nullable()->after('home_lat');
            $table->string('home_airport', 8)->nullable()->after('home_lng');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['home_name', 'home_lat', 'home_lng', 'home_airport']);
        });
    }
};
