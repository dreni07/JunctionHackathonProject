<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Infrastructure matrix & utility specifications per room category (Table 3.1).
     */
    public function up(): void
    {
        Schema::create('infrastructure_specs', function (Blueprint $table) {
            $table->id();
            $table->string('room_category')->unique();   // Educational Units, Software Studios, ...
            $table->text('av_assets');                   // AV & Digital Infrastructure Assets
            $table->string('climate_support');           // Zoned Box HVAC
            $table->string('ingress_routing');           // Western Lift Matrix Access
            $table->unsignedInteger('power_kw');         // 15, 25, ...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructure_specs');
    }
};
