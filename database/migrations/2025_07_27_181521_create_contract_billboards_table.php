<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('contract_billboards', function (Blueprint $table) {
      $table->id();
      $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
      $table->foreignId('billboard_id')->constrained()->cascadeOnDelete();
      $table->decimal('rate', 10, 2); // Rate for this specific billboard in this contract
      $table->text('notes')->nullable(); // Any specific notes for this billboard
      $table->timestamps();

      $table->unique(['contract_id', 'billboard_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('contract_billboards');
  }
};
