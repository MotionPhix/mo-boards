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
    Schema::table('companies', function (Blueprint $table) {
      // Company contact information
      $table->string('city', 100)->nullable()->after('address');
      $table->string('state', 50)->nullable()->after('city');
      $table->string('zip_code', 20)->nullable()->after('state');
      $table->string('country', 100)->nullable()->after('zip_code');
      $table->string('phone', 20)->nullable()->after('country');
      $table->string('email')->nullable()->after('phone');
      $table->string('website')->nullable()->after('email');

      // Contract number formatting
      $table->string('contract_number_prefix', 10)->nullable()->after('website');
      $table->string('contract_number_suffix', 10)->nullable()->after('contract_number_prefix');
      $table->string('contract_number_format', 50)->default('sequential')->after('contract_number_suffix');
      $table->integer('contract_number_length')->default(6)->after('contract_number_format');
      $table->integer('contract_number_start')->default(1)->after('contract_number_length');
      $table->integer('contract_number_current')->default(0)->after('contract_number_start');

      // Billboard code formatting
      $table->string('billboard_code_prefix', 10)->nullable()->after('contract_number_current');
      $table->string('billboard_code_suffix', 10)->nullable()->after('billboard_code_prefix');
      $table->string('billboard_code_format', 50)->default('sequential')->after('billboard_code_suffix');
      $table->integer('billboard_code_length')->default(4)->after('billboard_code_format');
      $table->integer('billboard_code_start')->default(1)->after('billboard_code_length');
      $table->integer('billboard_code_current')->default(0)->after('billboard_code_start');

      // Business settings
      $table->string('timezone', 50)->default('UTC')->after('billboard_code_current');
      $table->string('currency', 3)->default('USD')->after('timezone');
      $table->string('date_format', 20)->default('Y-m-d')->after('currency');
      $table->string('time_format', 10)->default('H:i')->after('date_format');

      // Billing and payment settings
      $table->integer('payment_terms_days')->default(30)->after('time_format');
      $table->decimal('late_fee_percentage', 5, 2)->default(0)->after('payment_terms_days');
      $table->boolean('auto_generate_invoices')->default(false)->after('late_fee_percentage');
      $table->string('tax_id', 50)->nullable()->after('auto_generate_invoices');
      $table->decimal('default_tax_rate', 5, 2)->default(0)->after('tax_id');

      // Notification settings
      $table->json('notification_settings')->nullable()->after('default_tax_rate');

      // Additional business information
      $table->text('company_description')->nullable()->after('notification_settings');
      $table->json('social_media_links')->nullable()->after('company_description');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('companies', function (Blueprint $table) {
      $table->dropColumn([
        'city', 'state', 'zip_code', 'country', 'phone', 'email', 'website',
        'contract_number_prefix', 'contract_number_suffix', 'contract_number_format', 'contract_number_length',
        'contract_number_start', 'contract_number_current',
        'billboard_code_prefix', 'billboard_code_suffix', 'billboard_code_format', 'billboard_code_length',
        'billboard_code_start', 'billboard_code_current',
        'timezone', 'currency', 'date_format', 'time_format',
        'payment_terms_days', 'late_fee_percentage', 'auto_generate_invoices', 'tax_id', 'default_tax_rate',
        'notification_settings', 'company_description', 'social_media_links'
      ]);
    });
  }
};
