<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('discord_url')->nullable()->after('website_url');
            $table->string('twitter_url')->nullable()->after('discord_url');
            $table->string('facebook_url')->nullable()->after('twitter_url');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['discord_url', 'twitter_url', 'facebook_url']);
        });
    }
};
