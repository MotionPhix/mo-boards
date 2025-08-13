<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // subscription, invoice, charge
            $table->string('payment_id')->nullable();
            $table->string('reference')->unique();
            $table->unsignedInteger('amount')->default(0); // minor units
            $table->string('currency', 10)->default('MWK');
            $table->string('status')->default('pending'); // pending, paid, failed, refunded
            $table->string('description')->nullable();
            $table->json('raw_response')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_transactions');
    }
};
