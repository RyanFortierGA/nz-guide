<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedInteger('cost_flight_pp')->nullable()->after('maps_url'); // NZD return from AKL
            $table->unsignedInteger('cost_airbnb_night')->nullable()->after('cost_flight_pp'); // NZD place/night
            $table->unsignedInteger('cost_day_pp')->nullable()->after('cost_airbnb_night'); // food/activities/transport
            $table->unsignedTinyInteger('cost_suggested_nights')->nullable()->after('cost_day_pp');
            $table->unsignedInteger('cost_transport')->nullable()->after('cost_suggested_nights'); // car/ferry add-on
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->unsignedTinyInteger('party_size')->default(2)->after('setup_complete');
            $table->boolean('include_auckland_stay')->default(true)->after('party_size');
            $table->unsignedInteger('auckland_airbnb_night')->default(180)->after('include_auckland_stay');
        });

        Schema::table('trip_location', function (Blueprint $table) {
            $table->unsignedTinyInteger('nights')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'cost_flight_pp',
                'cost_airbnb_night',
                'cost_day_pp',
                'cost_suggested_nights',
                'cost_transport',
            ]);
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['party_size', 'include_auckland_stay', 'auckland_airbnb_night']);
        });

        Schema::table('trip_location', function (Blueprint $table) {
            $table->dropColumn('nights');
        });
    }
};
