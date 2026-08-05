<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->text('maps_url')->nullable()->after('airbnb_query');
        });

        Schema::table('sub_locations', function (Blueprint $table) {
            $table->text('maps_url')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('maps_url');
        });

        Schema::table('sub_locations', function (Blueprint $table) {
            $table->dropColumn('maps_url');
        });
    }
};
