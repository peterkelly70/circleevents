<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('font_size', 20)->default('medium')->after('bio');
            $table->string('organization_theme_override', 40)->nullable()->after('font_size');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('organization_theme_override');
            $table->dropColumn('font_size');
        });
    }
};
