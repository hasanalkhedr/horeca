<?php


// <?php

use App\Http\Controllers\{
    AdsOptionController,
    AdsPackageController,
    BrandController,
    ClientController,
    CompanyController,
    ContractController,
    EventController,
    Settings\CategoryController,
    Settings\CurrencyController,

    Settings\PriceController,
    SponsorOptionController,
    SponsorPackageController,
    StandController,
    HomeController
};
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\EffAdsOptionController;
use App\Http\Controllers\EffAdsPackageController;
use App\Http\Controllers\ReportController;
use App\Livewire\ReportBuilder;
use Illuminate\Support\Facades\Route;

Route::get('/admin', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    Route::get('contracts/{contract}/preview', [ContractController::class, 'preview'])->name('contracts.preview');
});
Route::get('/report-builder', ReportBuilder::class)->name('report.builder');
Route::get('/report-builder/{report}', ReportBuilder::class)->name('report.editor');
Route::get('/reports/{id}', [ReportController::class, 'show'])->name('reports.show');


require __DIR__ . '/auth.php';
