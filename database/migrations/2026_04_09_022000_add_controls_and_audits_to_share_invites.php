<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table) {
            $table->unsignedInteger('max_uses')->nullable()->after('expires_at');
            $table->unsignedInteger('use_count')->default(0)->after('max_uses');
            $table->timestamp('revoked_at')->nullable()->after('use_count');
            $table->foreignId('revoked_by_user_id')->nullable()->after('revoked_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('event_invitations', function (Blueprint $table) {
            $table->unsignedInteger('max_uses')->nullable()->after('expires_at');
            $table->unsignedInteger('use_count')->default(0)->after('max_uses');
            $table->timestamp('revoked_at')->nullable()->after('use_count');
            $table->foreignId('revoked_by_user_id')->nullable()->after('revoked_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('invitation_audits', function (Blueprint $table) {
            $table->id();
            $table->string('invitation_type');
            $table->unsignedBigInteger('invitation_id');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['invitation_type', 'invitation_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_audits');

        Schema::table('organization_invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revoked_by_user_id');
            $table->dropColumn(['max_uses', 'use_count', 'revoked_at']);
        });

        Schema::table('event_invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revoked_by_user_id');
            $table->dropColumn(['max_uses', 'use_count', 'revoked_at']);
        });
    }
};
