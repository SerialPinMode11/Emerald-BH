<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DevAdminController;
use App\Http\Controllers\LandOwnerController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::get('/explore', [CustomerController::class, 'explore'])->name('explore');
        Route::get('/properties/{property}', [CustomerController::class, 'showProperty'])->name('properties.show');
        Route::get('/rentals', [CustomerController::class, 'rentals'])->name('rentals');
        Route::get('/agreements/{agreement}/sign', [CustomerController::class, 'signAgreement'])->name('agreements.sign');
        Route::post('/properties/{property}/request', [CustomerController::class, 'requestRent'])->name('properties.request');
        Route::post('/agreements/{agreement}/sign', [CustomerController::class, 'sign'])->name('agreements.sign.submit');
    });

    Route::middleware('role:land_owner')->prefix('land-owner')->name('land-owner.')->group(function () {
        Route::get('/dashboard', [LandOwnerController::class, 'dashboard'])->name('dashboard');
        Route::get('/properties', [LandOwnerController::class, 'properties'])->name('properties');
        Route::get('/properties/{property}/rental', [LandOwnerController::class, 'showPropertyRental'])->name('properties.rental');
        Route::post('/properties/{property}/payments/{transaction}/record', [LandOwnerController::class, 'recordRentPayment'])->name('properties.payments.record');
        Route::post('/properties', [LandOwnerController::class, 'storeProperty'])->name('properties.store');
        Route::post('/agreements/{agreement}/sign', [LandOwnerController::class, 'signAgreement'])->name('agreements.sign');
    });

    Route::middleware('role:community')->prefix('community')->name('community.')->group(function () {
        Route::get('/portal', [CommunityController::class, 'portal'])->name('portal');
        Route::get('/agreements/{agreement}', [CommunityController::class, 'showAgreement'])->name('agreements.show');
        Route::post('/agreements/{agreement}/activate', [CommunityController::class, 'activateAgreement'])->name('agreements.activate');
        Route::get('/disputes/{transaction}', [CommunityController::class, 'disputeResolution'])->name('disputes.show');
        Route::patch('/agreements/{agreement}/status', [CommunityController::class, 'updateAgreementStatus'])->name('agreements.status');
        Route::post('/disputes/{transaction}/resolve', [CommunityController::class, 'resolveDispute'])->name('disputes.resolve');
    });

    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/approvals', [SuperAdminController::class, 'approvals'])->name('approvals');
        Route::put('/properties/{property}/approve', [SuperAdminController::class, 'approveProperty'])->name('properties.approve');
        Route::put('/properties/{property}/reject', [SuperAdminController::class, 'rejectProperty'])->name('properties.reject');
        Route::patch('/agreements/{agreement}/assign', [SuperAdminController::class, 'assignMediator'])->name('agreements.assign');
        Route::post('/change-requests', [SuperAdminController::class, 'storeChangeRequest'])->name('change-requests.store');
    });

    Route::middleware('role:dev_admin')->prefix('dev-admin')->name('dev-admin.')->group(function () {
        Route::get('/dashboard', [DevAdminController::class, 'dashboard'])->name('dashboard');
        Route::patch('/change-requests/{changeRequest}', [DevAdminController::class, 'updateChangeRequest'])->name('change-requests.update');
        Route::put('/change-requests/{changeRequest}/deploy', [DevAdminController::class, 'deploy'])->name('change-requests.deploy');
    });
});
