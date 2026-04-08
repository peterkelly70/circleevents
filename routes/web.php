<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventDiscussionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventInvitationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MailingListController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationEmailPreferenceController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\OrganizationMessageController;
use App\Http\Controllers\OrganizationPostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/event-invitations/{token}', [EventInvitationController::class, 'accept'])->name('event-invitations.accept');
Route::get('/organizations/{organization:slug}', [OrganizationController::class, 'show'])->name('organizations.show');
Route::get('/organization-invitations/{token}', [OrganizationInvitationController::class, 'accept'])->name('organizations.invitations.accept');
Route::get('/organizations/{organization:slug}/email-preferences/{token}/opt-out', [OrganizationEmailPreferenceController::class, 'optOut'])->name('organizations.email-preferences.opt-out');
Route::get('/mailing-lists/{mailingList:slug}', [MailingListController::class, 'show'])->name('mailing-lists.show');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/events/{event:slug}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::get('/organizations/{organization:slug}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::patch('/organizations/{organization:slug}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::post('/organizations/{organization:slug}/follow', [OrganizationMemberController::class, 'follow'])->name('organizations.follow');
    Route::post('/organizations/{organization:slug}/members/promote', [OrganizationMemberController::class, 'promote'])->name('organizations.members.promote');
    Route::post('/organizations/{organization:slug}/invitations', [OrganizationInvitationController::class, 'store'])->name('organizations.invitations.store');
    Route::post('/organizations/{organization:slug}/posts', [OrganizationPostController::class, 'store'])->name('organizations.posts.store');
    Route::post('/organizations/{organization:slug}/messages', [OrganizationMessageController::class, 'store'])->name('organizations.messages.store');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::post('/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::patch('/events/{event:slug}', [EventController::class, 'update'])->name('events.update');
    Route::post('/events/{event:slug}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
    Route::post('/events/{event:slug}/invitations', [EventInvitationController::class, 'store'])->name('events.invitations.store');
    Route::post('/events/{event:slug}/discussion', [EventDiscussionController::class, 'store'])->name('events.discussion.store');
    Route::post('/mailing-lists', [MailingListController::class, 'store'])->name('mailing-lists.store');
    Route::post('/mailing-lists/{mailingList:slug}/subscribe', [MailingListController::class, 'subscribe'])->name('mailing-lists.subscribe');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
