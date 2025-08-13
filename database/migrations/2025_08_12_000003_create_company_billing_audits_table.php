<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_billing_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // subscribe, change_plan, cancel, payment_succeeded, payment_failed
            $table->json('details')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_billing_audits');
    }
};
