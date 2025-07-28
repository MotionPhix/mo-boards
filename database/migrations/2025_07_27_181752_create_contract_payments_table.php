<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('contract_payments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
      $table->decimal('amount', 10, 2);
      $table->date('due_date');
      $table->date('paid_date')->nullable();
      $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
      $table->string('payment_method')->nullable();
      $table->string('reference_number')->nullable();
      $table->text('notes')->nullable();
      $table->timestamps();

      $table->index(['contract_id', 'status']);
      $table->index('due_date');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('contract_payments');
  }
};
