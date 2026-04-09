<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('auto_post_discord_announcements')->default(false)->after('auto_post_discord_events');
        });

        Schema::table('organization_messages', function (Blueprint $table) {
            $table->timestamp('discord_posted_at')->nullable()->after('emailed_at');
        });
    }

    public function down(): void
    {
        Schema::table('organization_messages', function (Blueprint $table) {
            $table->dropColumn('discord_posted_at');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('auto_post_discord_announcements');
        });
    }
};
