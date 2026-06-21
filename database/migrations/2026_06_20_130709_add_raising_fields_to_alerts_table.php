<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table): void {
            if (! Schema::hasColumn('alerts', 'raised_by')) {
                $table->foreignId('raised_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('alerts', 'category')) {
                $table->string('category')->nullable()->after('source');
            }
            if (! Schema::hasColumn('alerts', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('dismissed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table): void {
            if (Schema::hasColumn('alerts', 'raised_by')) {
                $table->dropConstrainedForeignId('raised_by');
            }
            $table->dropColumn(['category', 'resolved_at']);
        });
    }
};
