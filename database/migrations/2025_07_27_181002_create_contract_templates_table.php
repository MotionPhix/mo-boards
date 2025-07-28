<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('contract_templates', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->text('description')->nullable();
      $table->json('default_terms'); // Store default contract terms as JSON
      $table->json('custom_fields')->nullable(); // Store custom field definitions
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('contract_templates');
  }
};
