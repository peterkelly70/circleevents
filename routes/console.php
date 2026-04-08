<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:send-reminders', function () {
    $sent = app(\App\Support\EventReminderSender::class)->send();

    $this->info("Sent {$sent} event reminder emails.");
})->purpose('Send reminder emails to attendees who are going to upcoming events');

Schedule::command('events:send-reminders')->hourly();
