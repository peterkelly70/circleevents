<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->text('discord_webhook_url')->nullable()->after('facebook_url');
            $table->boolean('auto_post_discord_events')->default(false)->after('discord_webhook_url');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('discord_posted_at')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('discord_posted_at');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['discord_webhook_url', 'auto_post_discord_events']);
        });
    }
};
