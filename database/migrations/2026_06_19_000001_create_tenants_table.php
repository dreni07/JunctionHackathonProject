<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A tenant is a Pyramid branch (e.g. TUMO TIRANA). Each branch defines its
     * own set of worker roles, stored as a JSON list in `roles`.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            // The kinds of workers that operate within this branch, e.g.
            // ["Learning Coach", "Workshop Leader", ...].
            $table->json('roles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
