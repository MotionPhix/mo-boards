<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop and recreate the table with the correct column type
        Schema::dropIfExists('team_invitations');

        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('role')->nullable();
            $table->string('invitation_token', 500)->unique(); // Increased to 500 chars
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the original table structure
        Schema::dropIfExists('team_invitations');

        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('role')->nullable();
            $table->string('invitation_token')->unique(); // Original length (255)
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }
};
