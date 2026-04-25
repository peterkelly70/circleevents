<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('recurrence_group')->nullable()->after('mailing_list_id');
            $table->string('repeat_frequency')->nullable()->after('visibility');
            $table->timestamp('repeat_until')->nullable()->after('repeat_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['recurrence_group', 'repeat_frequency', 'repeat_until']);
        });
    }
};
