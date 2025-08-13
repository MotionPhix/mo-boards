<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., free, pro, business
            $table->string('name');
            $table->unsignedInteger('price')->default(0); // minor units
            $table->string('currency', 10)->default('MWK');
            $table->string('interval')->default('month'); // month, year
            $table->unsignedSmallInteger('interval_count')->default(1);
            $table->json('features')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
