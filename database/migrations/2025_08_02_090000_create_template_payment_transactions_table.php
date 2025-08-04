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
        Schema::create('template_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained('contract_templates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // PayChangu payment details
            $table->string('payment_id')->unique(); // PayChangu payment ID
            $table->string('reference')->unique(); // Our internal reference
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MWK');
            $table->enum('status', ['pending', 'processing', 'paid', 'completed', 'failed', 'cancelled'])->default('pending');

            // PayChangu URLs
            $table->string('checkout_url')->nullable();
            $table->string('return_url')->nullable();
            $table->string('cancel_url')->nullable();

            // Customer details
            $table->json('customer_details');

            // Payment metadata
            $table->json('metadata')->nullable();

            // Payment tracking
            $table->timestamp('payment_initiated_at');
            $table->timestamp('payment_completed_at')->nullable();
            $table->timestamp('payment_failed_at')->nullable();

            // Additional details
            $table->string('failure_reason')->nullable();
            $table->json('paychangu_response')->nullable(); // Store full PayChangu response

            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['template_id', 'status']);
            $table->index('payment_id');
            $table->index('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_payment_transactions');
    }
};
