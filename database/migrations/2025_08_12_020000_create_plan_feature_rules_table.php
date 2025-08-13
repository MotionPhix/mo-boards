<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_feature_rules', function (Blueprint $table) {
            $table->id();
            $table->string('plan_id'); // e.g., free, pro, business
            $table->string('key');     // e.g., billboards.max
            $table->string('value');   // store as string; cast at read time
            $table->timestamps();
            $table->unique(['plan_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_feature_rules');
    }
};
