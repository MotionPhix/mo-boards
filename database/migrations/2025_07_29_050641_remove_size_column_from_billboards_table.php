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
    Schema::table('billboards', function (Blueprint $table) {
      $table->dropColumn('size');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billboards', function (Blueprint $table) {
      $table->string('size')->nullable()->after('longitude');
    });
  }
};
