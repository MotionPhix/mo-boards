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
        Schema::create('purchased_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('contract_templates')->cascadeOnDelete();
            $table->decimal('purchase_price', 10, 2);
            $table->timestamp('purchased_at');
            $table->foreignId('purchased_by')->constrained('users');
            $table->json('purchase_metadata')->nullable(); // Store additional purchase info
            $table->timestamps();

            // Ensure a company can't purchase the same template twice
            $table->unique(['company_id', 'template_id']);

            // Indexes for faster queries
            $table->index(['company_id', 'purchased_at']);
            $table->index('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchased_templates');
    }
};
