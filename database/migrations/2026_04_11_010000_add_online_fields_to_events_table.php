<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->after('description');
            $table->string('online_url')->nullable()->after('is_online');
            $table->string('venue_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'online_url']);
            $table->string('venue_name')->nullable(false)->change();
        });
    }
};
