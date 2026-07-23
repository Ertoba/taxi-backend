<?php

use App\Http\Controllers\Admin\Driver\KeepzPayoutController;
use App\Http\Controllers\Admin\KeepzSplitSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('settings/keepz-split', [KeepzSplitSettingsController::class, 'index'])
        ->name('keepz_split.settings');
    Route::post('settings/keepz-split', [KeepzSplitSettingsController::class, 'update'])
        ->name('keepz_split.update');

    Route::get('driver/keepz/{driverId}', [KeepzPayoutController::class, 'edit'])
        ->name('driver.keepz');
    Route::post('driver/keepz/{driverId}', [KeepzPayoutController::class, 'update'])
        ->name('driver.keepz.update');
});
