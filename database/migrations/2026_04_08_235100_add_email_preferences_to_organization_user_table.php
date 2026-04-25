<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_user', function (Blueprint $table) {
            $table->timestamp('email_opt_out_at')->nullable()->after('role');
            $table->string('email_opt_out_token', 64)->nullable()->unique()->after('email_opt_out_at');
        });
    }

    public function down(): void
    {
        Schema::table('organization_user', function (Blueprint $table) {
            $table->dropColumn(['email_opt_out_at', 'email_opt_out_token']);
        });
    }
};
