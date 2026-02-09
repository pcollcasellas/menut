<?php

use App\Http\Controllers\HouseholdInvitationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// PWA offline fallback page
Route::view('offline', 'offline')->name('offline');

// Menu route (PWA start URL - redirects to dashboard)
Route::redirect('menu', 'dashboard')->name('menu');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('recipes', 'recipes')
    ->middleware(['auth'])
    ->name('recipes');

Route::view('shopping-list', 'shopping-list')
    ->middleware(['auth'])
    ->name('shopping-list');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('household', 'household')
    ->middleware(['auth'])
    ->name('household');

Route::get('household/invitation/{token}', [HouseholdInvitationController::class, 'show'])
    ->middleware(['auth'])
    ->name('household.invitation.show');

Route::post('household/invitation/{token}/accept', [HouseholdInvitationController::class, 'accept'])
    ->middleware(['auth'])
    ->name('household.invitation.accept');

require __DIR__.'/auth.php';
