<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "colored boxes" from the Data Schema appendix — the 48 structurally
     * independent rooms — are modelled as spaces. These columns carry the
     * appendix's per-room attributes (Table 1.2).
     */
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('room_code')->nullable()->unique()->after('id'); // RM-B1-001
            $table->string('box_ref')->nullable()->after('room_code');       // B01
            $table->string('zone_class')->nullable()->after('box_ref');      // TUMO | Public
            $table->string('functional_type')->nullable()->after('type');    // Classroom/Workshop, Tech Lab, ...
            $table->unsignedInteger('area_sqm')->nullable()->after('functional_type');
            $table->text('workload_target')->nullable()->after('area_sqm');  // Primary Automated Workload Target
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn([
                'room_code', 'box_ref', 'zone_class', 'functional_type', 'area_sqm', 'workload_target',
            ]);
        });
    }
};
