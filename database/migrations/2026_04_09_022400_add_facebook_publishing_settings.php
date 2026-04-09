<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('facebook_page_id')->nullable()->after('facebook_url');
            $table->text('facebook_page_access_token')->nullable()->after('facebook_page_id');
            $table->boolean('auto_post_facebook_events')->default(false)->after('facebook_page_access_token');
            $table->boolean('auto_post_facebook_announcements')->default(false)->after('auto_post_facebook_events');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('facebook_posted_at')->nullable()->after('discord_posted_at');
        });

        Schema::table('organization_messages', function (Blueprint $table) {
            $table->timestamp('facebook_posted_at')->nullable()->after('discord_posted_at');
        });
    }

    public function down(): void
    {
        Schema::table('organization_messages', function (Blueprint $table) {
            $table->dropColumn('facebook_posted_at');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('facebook_posted_at');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_page_id',
                'facebook_page_access_token',
                'auto_post_facebook_events',
                'auto_post_facebook_announcements',
            ]);
        });
    }
};
