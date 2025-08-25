<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'free', 'pro', 'business'
            $table->string('name'); // Display name e.g. Starter, Professional, Enterprise
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('interval', 20)->default('month'); // month, year
            $table->unsignedInteger('interval_count')->default(1);
            $table->json('features')->nullable(); // Optional, for custom metadata
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
