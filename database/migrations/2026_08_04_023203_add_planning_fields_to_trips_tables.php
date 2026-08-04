<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('visitor_name')->nullable()->after('name');
            $table->dateTime('arrives_at')->nullable()->after('visitor_name');
            $table->dateTime('departs_at')->nullable()->after('arrives_at');
            $table->string('share_token', 64)->nullable()->unique()->after('departs_at');
            $table->string('share_blurb')->nullable()->after('share_token');
            $table->boolean('setup_complete')->default(false)->after('share_blurb');
        });

        Schema::table('trip_location', function (Blueprint $table) {
            $table->unsignedSmallInteger('day_index')->nullable()->after('sort_order');
            $table->time('planned_time')->nullable()->after('day_index');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'visitor_name',
                'arrives_at',
                'departs_at',
                'share_token',
                'share_blurb',
                'setup_complete',
            ]);
        });

        Schema::table('trip_location', function (Blueprint $table) {
            $table->dropColumn(['day_index', 'planned_time']);
        });
    }
};
