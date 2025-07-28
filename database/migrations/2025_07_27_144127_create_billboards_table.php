<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('billboards', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained()->onDelete('cascade');
      $table->string('name');
      $table->string('code')->unique();
      $table->text('location');
      $table->decimal('latitude', 10, 8)->nullable();
      $table->decimal('longitude', 11, 8)->nullable();
      $table->string('size')->nullable();
      $table->decimal('width', 8, 2)->nullable();
      $table->decimal('height', 8, 2)->nullable();
      $table->decimal('monthly_rate', 10, 2)->default(0);
      $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
      $table->text('description')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('billboards');
  }
};
