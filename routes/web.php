<?php


// <?php

use App\Http\Controllers\{
    BrandController,
    ClientController,
    CompanyController,
    ContractController,
    EventController,
    Settings\BankAccountController,
    Settings\CategoryController,
    Settings\CurrencyController,

    Settings\PaymentRateController,
    Settings\PriceController,
    SponsorOptionController,
    SponsorPackageController,
    StandController,
    HomeController
};
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
    Route::get('/dashboard',[HomeController::class, 'dashboard'])->middleware('verified')->name('dashboard');
    Route::view('welcome', 'welcome')->middleware('verified')->name('welcome');
    Route::view('profile', 'profile')->name('profile');

    // Admin-specific routes
    Route::middleware(['role:eventManager'])->group(function () {
        Route::get('/admin', function () {
            return "admin role";
        });
    });

    // Payment settings
    Route::resource('payment_rates', PaymentRateController::class);
    Route::resource('bank_accounts', BankAccountController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('prices', PriceController::class);

    // Stands
    Route::resource('stands', StandController::class);
    Route::post('stands/add_many', [StandController::class, 'storeMany'])->name('stands.storeMany');
    Route::post('stands/import/{event_id}', [StandController::class, 'import'])->name('stands.import');

    // Sponsorship
    Route::resource('sponsor_packages', SponsorPackageController::class);
    Route::resource('sponsor_options', SponsorOptionController::class);
    Route::get('sponsor_packages/{id}/options', [SponsorPackageController::class, 'getRelatedOptions']);
    Route::post('sponsor_packages/{id}/options', [SponsorPackageController::class, 'relateOption']);
    Route::delete('sponsor_packages/{id}/options/{optionId}', [SponsorPackageController::class, 'unrelateOption']);

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
Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

// Auth routes
require __DIR__ . '/auth.php';
