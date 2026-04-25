<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('notify_followers_one_week_before')->default(false)->after('facebook_posted_at');
            $table->boolean('notify_followers_one_day_before')->default(false)->after('notify_followers_one_week_before');
            $table->boolean('notify_followers_one_hour_before')->default(false)->after('notify_followers_one_day_before');
            $table->timestamp('follower_reminder_one_week_sent_at')->nullable()->after('notify_followers_one_hour_before');
            $table->timestamp('follower_reminder_one_day_sent_at')->nullable()->after('follower_reminder_one_week_sent_at');
            $table->timestamp('follower_reminder_one_hour_sent_at')->nullable()->after('follower_reminder_one_day_sent_at');
        });

        Schema::table('event_rsvps', function (Blueprint $table) {
            $table->boolean('remind_one_week_before')->default(false)->after('notes');
            $table->boolean('remind_one_day_before')->default(false)->after('remind_one_week_before');
            $table->boolean('remind_one_hour_before')->default(false)->after('remind_one_day_before');
            $table->timestamp('reminder_one_week_sent_at')->nullable()->after('reminder_sent_at');
            $table->timestamp('reminder_one_day_sent_at')->nullable()->after('reminder_one_week_sent_at');
            $table->timestamp('reminder_one_hour_sent_at')->nullable()->after('reminder_one_day_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_rsvps', function (Blueprint $table) {
            $table->dropColumn([
                'remind_one_week_before',
                'remind_one_day_before',
                'remind_one_hour_before',
                'reminder_one_week_sent_at',
                'reminder_one_day_sent_at',
                'reminder_one_hour_sent_at',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'notify_followers_one_week_before',
                'notify_followers_one_day_before',
                'notify_followers_one_hour_before',
                'follower_reminder_one_week_sent_at',
                'follower_reminder_one_day_sent_at',
                'follower_reminder_one_hour_sent_at',
            ]);
        });
    }
};
