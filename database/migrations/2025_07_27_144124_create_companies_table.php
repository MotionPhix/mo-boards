<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry')->nullable();
            $table->enum('size', ['1-10', '11-50', '51-200', '200+'])->nullable();
            $table->text('address')->nullable();
            // Allow both legacy and new keys so tests (SQLite) accept new values; later migrations can shrink in MySQL
            $table->enum('subscription_plan', ['starter', 'professional', 'enterprise', 'free', 'pro', 'business'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
