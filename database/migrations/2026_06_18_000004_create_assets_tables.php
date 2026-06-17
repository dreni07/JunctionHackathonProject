<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');                  // AssetType: chair | microphone | ...
            $table->string('qr_code')->unique();
            $table->string('status')->default('available'); // AssetStatus
            $table->string('current_location')->nullable(); // storage_room | Blue Hall | ...
            $table->foreignUuid('assigned_event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->timestamps();
        });

        // Full QR movement history — every time an asset changes location.
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained()->cascadeOnDelete();
            $table->string('from_location')->nullable();
            $table->string('to_location');
            $table->foreignUuid('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('moved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moved_at');
            $table->timestamps();
        });

        // How many of an asset are reserved for an event.
        Schema::create('asset_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('reserved_quantity')->default(1);
            $table->string('status')->default('reserved'); // ReservationStatus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_reservations');
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('assets');
    }
};
