<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Acoustic proximity restrictions & buffer rules (Table 2.2).
     */
    public function up(): void
    {
        Schema::create('acoustic_rules', function (Blueprint $table) {
            $table->id();
            $table->string('event_target_profile')->unique(); // Live Audio Tracking, ...
            $table->text('collision_profile');                 // simultaneous incompatible profiles
            $table->text('buffer_requirement');                // enforced separation
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acoustic_rules');
    }
};
