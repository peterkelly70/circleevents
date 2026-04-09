<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table) {
            $table->string('share_code', 16)->nullable()->unique()->after('token');
            $table->timestamp('expires_at')->nullable()->after('share_code');
            $table->string('email')->nullable()->change();
        });

        Schema::table('event_invitations', function (Blueprint $table) {
            $table->string('share_code', 16)->nullable()->unique()->after('token');
            $table->timestamp('expires_at')->nullable()->after('share_code');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table) {
            $table->dropUnique('organization_invitations_share_code_unique');
            $table->dropColumn(['share_code', 'expires_at']);
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('event_invitations', function (Blueprint $table) {
            $table->dropUnique('event_invitations_share_code_unique');
            $table->dropColumn(['share_code', 'expires_at']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
