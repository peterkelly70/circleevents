<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->where('visibility', 'community')
            ->update(['visibility' => 'unlisted']);
    }

    public function down(): void
    {
        DB::table('events')
            ->where('visibility', 'unlisted')
            ->update(['visibility' => 'community']);
    }
};
