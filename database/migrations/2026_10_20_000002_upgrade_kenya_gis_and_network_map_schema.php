<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade fiber_infrastructure table with comprehensive Kenya GIS fields
        Schema::table('fiber_infrastructure', function (Blueprint $table) {
            if (!Schema::hasColumn('fiber_infrastructure', 'asset_subtype')) {
                $table->string('asset_subtype', 50)->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'county')) {
                $table->string('county', 80)->nullable()->after('code')->index();
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'sub_county')) {
                $table->string('sub_county', 80)->nullable()->after('county');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'town')) {
                $table->string('town', 100)->nullable()->after('sub_county')->index();
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'estate_or_road')) {
                $table->string('estate_or_road', 150)->nullable()->after('town');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'coordinate_source')) {
                $table->string('coordinate_source', 50)->default('gps_surveyed')->after('longitude');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'elevation_m')) {
                $table->decimal('elevation_m', 8, 2)->nullable()->after('coordinate_source');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'length_meters')) {
                $table->decimal('length_meters', 12, 2)->nullable()->after('route_coordinates_json');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'azimuth_deg')) {
                $table->decimal('azimuth_deg', 6, 2)->nullable()->after('length_meters');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'frequency_ghz')) {
                $table->decimal('frequency_ghz', 6, 2)->nullable()->after('azimuth_deg');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'parent_asset_id')) {
                $table->unsignedBigInteger('parent_asset_id')->nullable()->after('site_id')->index();
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'linked_device_type')) {
                $table->string('linked_device_type', 40)->nullable()->after('parent_asset_id');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'linked_device_id')) {
                $table->unsignedBigInteger('linked_device_id')->nullable()->after('linked_device_type');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'metadata_json')) {
                $table->json('metadata_json')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'created_by_user_id')) {
                $table->unsignedBigInteger('created_by_user_id')->nullable()->after('metadata_json');
            }
            if (!Schema::hasColumn('fiber_infrastructure', 'updated_by_user_id')) {
                $table->unsignedBigInteger('updated_by_user_id')->nullable()->after('created_by_user_id');
            }
        });

        // 2. Add county & town metadata to sites
        Schema::table('sites', function (Blueprint $table) {
            if (!Schema::hasColumn('sites', 'county')) {
                $table->string('county', 80)->nullable()->after('location')->index();
            }
            if (!Schema::hasColumn('sites', 'town')) {
                $table->string('town', 100)->nullable()->after('county');
            }
        });

        // 3. Add county & town metadata to clients
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'county')) {
                $table->string('county', 80)->nullable()->after('address')->index();
            }
            if (!Schema::hasColumn('clients', 'town')) {
                $table->string('town', 100)->nullable()->after('county');
            }
            if (!Schema::hasColumn('clients', 'fiber_box_id')) {
                $table->unsignedBigInteger('fiber_box_id')->nullable()->after('site_id')->index();
            }
        });

        // 4. Add optional geospatial coordinates to mikrotik_devices & olt_devices
        Schema::table('mikrotik_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('mikrotik_devices', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('mikrotik_devices', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('mikrotik_devices', 'county')) {
                $table->string('county', 80)->nullable()->after('longitude');
            }
        });

        Schema::table('olt_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('olt_devices', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('olt_devices', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('olt_devices', 'county')) {
                $table->string('county', 80)->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fiber_infrastructure', function (Blueprint $table) {
            $cols = [
                'asset_subtype', 'county', 'sub_county', 'town', 'estate_or_road',
                'coordinate_source', 'elevation_m', 'length_meters', 'azimuth_deg',
                'frequency_ghz', 'parent_asset_id', 'linked_device_type',
                'linked_device_id', 'metadata_json', 'created_by_user_id', 'updated_by_user_id',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('fiber_infrastructure', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('sites', function (Blueprint $table) {
            foreach (['county', 'town'] as $col) {
                if (Schema::hasColumn('sites', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('clients', function (Blueprint $table) {
            foreach (['county', 'town', 'fiber_box_id'] as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('mikrotik_devices', function (Blueprint $table) {
            foreach (['latitude', 'longitude', 'county'] as $col) {
                if (Schema::hasColumn('mikrotik_devices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('olt_devices', function (Blueprint $table) {
            foreach (['latitude', 'longitude', 'county'] as $col) {
                if (Schema::hasColumn('olt_devices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
