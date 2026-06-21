<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tenant_finance_profiles')) {
            return;
        }

        Schema::create('tenant_finance_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('annual_budget', 14, 2)->default(0);
            $table->decimal('operating_reserve', 14, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_finance_profiles');
    }
};
