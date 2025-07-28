<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('phone')->nullable()->after('email');
      $table->foreignId('current_company_id')->nullable()->constrained('companies')->after('remember_token');
      $table->timestamp('last_active_at')->nullable()->after('current_company_id');
    });
  }

  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn(['phone', 'current_company_id', 'last_active_at']);
    });
  }
};
