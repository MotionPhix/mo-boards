<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('contracts', function (Blueprint $table) {
      $table->string('currency', 3)->nullable()->after('total_amount');
      $table->decimal('exchange_rate', 10, 6)->default(1.000000)->after('currency');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('contracts', function (Blueprint $table) {
      $table->dropColumn(['currency', 'exchange_rate']);
    });
  }
};
