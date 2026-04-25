<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_status')->default('active')->after('is_admin');
            $table->timestamp('approved_at')->nullable()->after('registration_status');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('approval_status')->default('approved')->after('visibility');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->foreignId('approved_by_user_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });

        DB::table('users')->update([
            'registration_status' => 'active',
            'approved_at' => now(),
        ]);

        DB::table('organizations')->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn(['approval_status', 'approved_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_status', 'approved_at']);
        });

        Schema::dropIfExists('site_settings');
    }
};
