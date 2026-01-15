<?php

use Illuminate\Support\Facades\Route;
use Modules\MeetFusion\Http\Controllers\MeetFusionController;
use Modules\MeetFusion\Http\Controllers\GoogleMeetController;
use Modules\MeetFusion\Http\Controllers\ApiSettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Google Meet routes
Route::middleware(['web', 'auth', 'admin'])->group(function () {
    // OAuth routes
    Route::get('google-meet/authorize', [GoogleMeetController::class, 'authorize'])->name('google-meet.authorize');
    Route::get('google-meet/callback', [GoogleMeetController::class, 'callback'])->name('google-meet.callback');
    
    // API Settings routes
    Route::post('google-meet/settings', [ApiSettingsController::class, 'storeGoogleMeetSettings'])->name('google-meet.settings.store');
    
    // Meeting management routes
    Route::post('google-meet/meetings', [MeetFusionController::class, 'createGoogleMeeting'])->name('google-meet.meetings.create');
    Route::get('google-meet/meetings/{meetingId}', [MeetFusionController::class, 'showGoogleMeeting'])->name('google-meet.meetings.show');
});
