<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table) {
            $table->string('revoked_reason', 500)->nullable()->after('revoked_by_user_id');
        });

        Schema::table('event_invitations', function (Blueprint $table) {
            $table->string('revoked_reason', 500)->nullable()->after('revoked_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table) {
            $table->dropColumn('revoked_reason');
        });

        Schema::table('event_invitations', function (Blueprint $table) {
            $table->dropColumn('revoked_reason');
        });
    }
};
