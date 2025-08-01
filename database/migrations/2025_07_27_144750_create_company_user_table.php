<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('company_user', function (Blueprint $table) {
      $table->id();
      $table->foreignId('company_id')->constrained()->onDelete('cascade');
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->boolean('is_owner')->default(false);
      $table->timestamp('joined_at')->useCurrent();
      $table->timestamps();

      $table->unique(['company_id', 'user_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('company_user');
  }
};
