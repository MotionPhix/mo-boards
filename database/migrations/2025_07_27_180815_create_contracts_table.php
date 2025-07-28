<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('contracts', function (Blueprint $table) {
      $table->id();
      $table->string('contract_number')->unique();
      $table->foreignId('company_id')->constrained()->cascadeOnDelete();
      $table->foreignId('created_by')->constrained('users');
      $table->foreignId('template_id')->nullable()->constrained('contract_templates')->nullOnDelete();

      // Client Information
      $table->string('client_name');
      $table->string('client_email')->nullable();
      $table->string('client_phone')->nullable();
      $table->text('client_address')->nullable();
      $table->string('client_company')->nullable();

      // Contract Details
      $table->date('start_date');
      $table->date('end_date');
      $table->decimal('total_amount', 10, 2);
      $table->decimal('monthly_amount', 10, 2)->nullable();
      $table->enum('payment_terms', ['monthly', 'quarterly', 'semi_annual', 'annual', 'one_time']);
      $table->enum('status', ['draft', 'pending', 'active', 'completed', 'cancelled'])->default('draft');

      // Contract Terms
      $table->json('terms_and_conditions'); // Store as JSON for flexibility
      $table->json('custom_fields_data')->nullable(); // Store custom field values

      // Additional Info
      $table->text('notes')->nullable();
      $table->timestamp('signed_at')->nullable();
      $table->string('signed_by')->nullable(); // Client signature info

      $table->timestamps();

      $table->index(['company_id', 'status']);
      $table->index('contract_number');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('contracts');
  }
};
