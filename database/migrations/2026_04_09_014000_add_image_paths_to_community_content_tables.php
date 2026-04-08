<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_discussion_posts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
        });

        Schema::table('organization_posts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
        });

        Schema::table('organization_messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('organization_messages', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('organization_posts', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('event_discussion_posts', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
