<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mailing_lists', function (Blueprint $table): void {
            $table->boolean('is_default')->default(false)->after('organization_id');
        });

        $organizations = DB::table('organizations')->orderBy('id')->get();

        foreach ($organizations as $organization) {
            $defaultList = DB::table('mailing_lists')
                ->where('organization_id', $organization->id)
                ->orderBy('id')
                ->first();

            if ($defaultList) {
                DB::table('mailing_lists')
                    ->where('id', $defaultList->id)
                    ->update(['is_default' => true]);

                continue;
            }

            DB::table('mailing_lists')->insert([
                'organization_id' => $organization->id,
                'is_default' => true,
                'name' => $organization->name.' updates',
                'slug' => Str::slug($organization->name.' updates').'-'.Str::lower(Str::random(6)),
                'description' => 'Organization-wide updates and announcements.',
                'audience' => 'all-members',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('mailing_lists', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }
};
