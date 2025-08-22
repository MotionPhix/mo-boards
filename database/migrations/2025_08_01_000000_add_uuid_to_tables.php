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
        // Add uuid column to users table
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'uuid')) {
                $table->uuid('uuid')->after('id')->unique()->nullable();
            }
        });

        // Add uuid column to companies table
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'uuid')) {
                $table->uuid('uuid')->after('id')->unique()->nullable();
            }
        });

        // Add uuid column to billboards table
        Schema::table('billboards', function (Blueprint $table) {
            if (! Schema::hasColumn('billboards', 'uuid')) {
                $table->uuid('uuid')->after('id')->unique()->nullable();
            }
        });

        // Add uuid column to contracts table
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'uuid')) {
                $table->uuid('uuid')->after('id')->unique()->nullable();
            }
        });

        // Add uuid column to contract_templates table
        Schema::table('contract_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('contract_templates', 'uuid')) {
                $table->uuid('uuid')->after('id')->unique()->nullable();
            }
        });

        // Add uuid column to team_invitations table if it exists
        if (Schema::hasTable('team_invitations')) {
            Schema::table('team_invitations', function (Blueprint $table) {
                if (! Schema::hasColumn('team_invitations', 'uuid')) {
                    $table->uuid('uuid')->after('id')->unique()->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove uuid column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        // Remove uuid column from companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        // Remove uuid column from billboards table
        Schema::table('billboards', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        // Remove uuid column from contracts table
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        // Remove uuid column from contract_templates table
        Schema::table('contract_templates', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        // Remove uuid column from team_invitations table if it exists
        if (Schema::hasTable('team_invitations')) {
            Schema::table('team_invitations', function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
