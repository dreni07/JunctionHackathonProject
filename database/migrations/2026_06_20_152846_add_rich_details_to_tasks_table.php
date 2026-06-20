<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Richer, AI-generated detail for each operational task: priority, an estimated
 * duration, where in the venue it happens, a step-by-step checklist and the
 * resources it needs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->default('medium')->after('state');
            }
            if (! Schema::hasColumn('tasks', 'estimated_minutes')) {
                $table->unsignedInteger('estimated_minutes')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('tasks', 'location')) {
                $table->string('location')->nullable()->after('estimated_minutes');
            }
            if (! Schema::hasColumn('tasks', 'checklist')) {
                $table->json('checklist')->nullable()->after('location');
            }
            if (! Schema::hasColumn('tasks', 'resources')) {
                $table->json('resources')->nullable()->after('checklist');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn(['priority', 'estimated_minutes', 'location', 'checklist', 'resources']);
        });
    }
};
