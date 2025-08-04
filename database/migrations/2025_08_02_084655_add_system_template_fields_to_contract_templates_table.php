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
        Schema::table('contract_templates', function (Blueprint $table) {
            // Make company_id nullable to support system templates
            $table->foreignId('company_id')->nullable()->change();

            // Add system template fields
            $table->boolean('is_system_template')->default(false)->after('is_active');
            $table->decimal('price', 10, 2)->nullable()->after('is_system_template');
            $table->string('category')->nullable()->after('price');
            $table->text('features')->nullable()->after('category');
            $table->string('preview_image')->nullable()->after('features');
            $table->json('tags')->nullable()->after('preview_image');

            // Add purchased templates tracking
            $table->index(['is_system_template', 'is_active']);
            $table->index(['category', 'is_system_template']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'is_system_template',
                'price',
                'category',
                'features',
                'preview_image',
                'tags'
            ]);

            // Make company_id required again
            $table->foreignId('company_id')->nullable(false)->change();
        });
    }
};
