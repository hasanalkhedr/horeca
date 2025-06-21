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
use App\Http\Controllers\ReportController;
use App\Livewire\ReportBuilder;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard and profile
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('verified')->name('dashboard');
    Route::view('welcome', 'welcome')->middleware('verified')->name('welcome');
    Route::view('profile', 'profile')->name('profile');

    // Admin-specific routes
    Route::middleware(['role:eventManager'])->group(function () {
        Route::get('/admin', function () {
            return "admin role";
        });
    });

    // Payment settings
    Route::resource('currencies', CurrencyController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('prices', PriceController::class);

    // Stands
    Route::resource('stands', StandController::class);
    Route::post('stands/add_many', [StandController::class, 'storeMany'])->name('stands.storeMany');
    Route::post('stands/import/{event_id}', [StandController::class, 'import'])->name('stands.import');
    Route::put('stands/{stand}/block', [StandController::class, 'block'])->name('stands.block');
    Route::put('stands/{stand}/unblock', [StandController::class, 'unblock'])->name('stands.unblock');

    // Sponsorship
    //Route::resource('sponsor_packages', SponsorPackageController::class);
    Route::resource('sponsor_options', SponsorOptionController::class);
    //Route::get('sponsor_packages/{id}/options', [SponsorPackageController::class, 'getRelatedOptions']);
    //Route::post('sponsor_packages/{id}/options', [SponsorPackageController::class, 'relateOption']);
    //Route::delete('sponsor_packages/{id}/options/{optionId}', [SponsorPackageController::class, 'unrelateOption']);

    //SponsorPackage Routes
    Route::group(['prefix' => 'sponsor_packages', 'as' => 'sponsor_packages.'], function () {
        // Basic CRUD routes
        Route::get('/', [SponsorPackageController::class, 'index'])->name('index');
        Route::post('/', [SponsorPackageController::class, 'store'])->name('store');
        Route::get('/{sponsorPackage}', [SponsorPackageController::class, 'show'])->name('show');
        Route::put('/{sponsorPackage}', [SponsorPackageController::class, 'update'])->name('update');
        Route::delete('/{sponsorPackage}', [SponsorPackageController::class, 'destroy'])->name('destroy');

        // Currency management routes
        Route::post('/{sponsorPackage}/add-currency', [SponsorPackageController::class, 'addCurrency'])
            ->name('add-currency');
        Route::delete('/{sponsorPackage}/remove-currency/{currency}', [SponsorPackageController::class, 'removeCurrency'])
            ->name('remove-currency');

        // Option management routes
        Route::post('/{sponsorPackage}/attach-option', [SponsorPackageController::class, 'attachOption'])
            ->name('attach-option');
        Route::delete('/{sponsorPackage}/detach-option/{sponsorOption}', [SponsorPackageController::class, 'detachOption'])
            ->name('detach-option');
    });
    // Events
    Route::resource('events', EventController::class);
    Route::get('events/{event}/stands', [StandController::class, 'index'])->name('events.stands');
    Route::get('events/{event}/dashboard', [EventController::class, 'dashboard'])->name('events.dashboard');

    // Contracts
    Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('contracts/create/{event}', [ContractController::class, 'create'])->name('contracts.create');
    Route::get('contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::post('contracts/store', [ContractController::class, 'store'])->name('contracts.store');
    Route::put('contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::post('contracts/{contract}/uploadPDF', [ContractController::class, 'uploadPDF'])->name('contracts.uploadPDF');
    Route::get('contracts/{contract}/preview', [ContractController::class, 'preview'])->name('contracts.preview');

    // Companies, brands, and clients
    Route::resource('companies', CompanyController::class);
    Route::get('companies/{company}/brands', [BrandController::class, 'index'])->name('companies.brands');
    Route::get('companies/{company}/persons', [ClientController::class, 'index'])->name('companies.clients');
    Route::resource('brands', BrandController::class);
    Route::resource('clients', ClientController::class);

    Route::get('events/{event}/contracts', [ContractController::class, 'index'])->name('events.contracts');
});
Route::get('/report-builder', ReportBuilder::class)->name('report.builder');
Route::get('/report-builder/{report}', ReportBuilder::class)->name('report.editor');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/{id}', [ReportController::class, 'show'])->name('reports.show');
Route::get('events/{event}/reports', [ReportController::class, 'index'])->name('events.reports');
Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

// AdsPackage Routes
Route::group(['prefix' => 'ads-packages', 'as' => 'ads-packages.'], function () {
    // Basic CRUD routes
    Route::get('/', [AdsPackageController::class, 'index'])->name('index');
    Route::post('/', [AdsPackageController::class, 'store'])->name('store');
    Route::get('/{adsPackage}', [AdsPackageController::class, 'show'])->name('show');
    Route::put('/{adsPackage}', [AdsPackageController::class, 'update'])->name('update');
    Route::delete('/{adsPackage}', [AdsPackageController::class, 'destroy'])->name('destroy');

    // Currency management routes
    Route::post('/{adsPackage}/add-currency', [AdsPackageController::class, 'addCurrency'])
        ->name('add-currency');
    Route::delete('/{adsPackage}/remove-currency/{currency}', [AdsPackageController::class, 'removeCurrency'])
        ->name('remove-currency');

    // Option management routes
    Route::post('/{adsPackage}/attach-option', [AdsPackageController::class, 'attachOption'])
        ->name('attach-option');
    Route::delete('/{adsPackage}/detach-option/{adsOption}', [AdsPackageController::class, 'detachOption'])
        ->name('detach-option');
});
// AdsOption Routes
Route::group(['prefix' => 'ads-options', 'as' => 'ads-options.'], function () {
    // Basic CRUD routes
    Route::get('/', [AdsOptionController::class, 'index'])->name('index');
    Route::post('/', [AdsOptionController::class, 'store'])->name('store');
    Route::get('/{adsOption}', [AdsOptionController::class, 'show'])->name('show');
    Route::put('/{adsOption}', [AdsOptionController::class, 'update'])->name('update');
    Route::delete('/{adsOption}', [AdsOptionController::class, 'destroy'])->name('destroy');

    // Additional currency routes
    Route::post('ads-options/{adsOption}/add-currency', [AdsOptionController::class, 'addCurrency'])
        ->name('add-currency');

    Route::delete('ads-options/{adsOption}/remove-currency/{currency}', [AdsOptionController::class, 'removeCurrency'])
        ->name('remove-currency');
});

// Auth routes
//Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::resource('users', UserController::class);
// Route to assign a role to a user
Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])
    ->name('users.assignRole');

// Route to unassign a role from a user
Route::delete('users/{user}/unassign-role/{role}', [UserController::class, 'unassignRole'])
    ->name('users.unassignRole');
Route::resource('roles', RoleController::class);
Route::post('roles/{role}/give-permission', [RoleController::class, 'givePermission'])
    ->name('roles.givePermission');

Route::delete('roles/{role}/remove-permission/{permission}', [RoleController::class, 'removePermission'])
    ->name('roles.removePermission');
require __DIR__ . '/auth.php';
