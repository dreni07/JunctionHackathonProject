<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Line items that make up a quotation / final proposal.
     */
    public function up(): void
    {
        Schema::create('quotation_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('final_proposal_id')->constrained()->cascadeOnDelete();

            $table->string('description');
            $table->string('category')->default('other'); // QuotationLineCategory
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['final_proposal_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_line_items');
    }
};
