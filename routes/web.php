<?php


use App\Http\Controllers\BrandController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractFieldController;
use App\Http\Controllers\ContractTypeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Settings\BankAccountController;
use App\Http\Controllers\Settings\CategoryController;
use App\Http\Controllers\Settings\CurrencyController;
use App\Http\Controllers\Settings\LandController;
use App\Http\Controllers\Settings\PaymentMethodController;
use App\Http\Controllers\Settings\PaymentRateController;
use App\Http\Controllers\Settings\PriceController;
use App\Http\Controllers\Settings\PricingStrategyController;
use App\Http\Controllers\SponsorOptionController;
use App\Http\Controllers\SponsorPackageController;
use App\Http\Controllers\StandController;
use App\Http\Controllers\StandTypeController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect()->route('login');
    }
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
    Route::view('welcome', 'welcome')
    ->middleware(['auth', 'verified'])
    ->name('welcome');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::middleware(['auth', 'role:eventManager'])->group(function () {
    Route::get('/admin', function () {
        "admin role";
    });
});

Route::resource('payment_rates', PaymentRateController::class);
Route::resource('bank_accounts', BankAccountController::class);
Route::resource('currencies', CurrencyController::class);
Route::resource('categories', CategoryController::class);

Route::resource('prices', PriceController::class);
Route::resource('stands', StandController::class);
Route::post('stands/add_many',[StandController::class, 'storeMany'])->name('stands.storeMany');
Route::post('stands/import/{event_id}', [StandController::class, 'import'])->name('stands.import');

Route::resource('sponsor_packages', SponsorPackageController::class);
Route::resource('sponsor_options', SponsorOptionController::class);
Route::get('sponsor_packages/{id}/options', [SponsorPackageController::class, 'getRelatedOptions']);
Route::post('sponsor_packages/{id}/options', [SponsorPackageController::class, 'relateOption']);
Route::delete('sponsor_packages/{id}/options/{optionId}', [SponsorPackageController::class, 'unrelateOption']);

Route::resource('events', EventController::class);
Route::get('events/{event}/stands',[StandController::class, 'index'])->name('events.stands');
Route::get('events/{event}/dashboard',[EventController::class, 'dashboard'])->name('events.dashboard');

//Route::resource('contracts', ContractController::class);
Route::get('contracts/create/{event}', [ContractController::class, 'create'])->name('contracts.create');
Route::post('contracts/store', [ContractController::class, 'store'])->name('contracts.store');
Route::post('contracts/{contract}/uploadPDF', [ContractController::class, 'uploadPDF'])->name('contracts.uploadPDF');


Route::resource('companies', CompanyController::class);
Route::get('companies/{company}/brands',[BrandController::class, 'index'])->name('companies.brands');
Route::get('companies/{company}/persons',[ClientController::class, 'index'])->name('companies.clients');

Route::resource('brands', BrandController::class);
Route::resource('clients', ClientController::class);
Route::resource('contract_types', ContractTypeController::class);
Route::get('events/{event}/contract_type',[ContractTypeController::class, 'index'])->name('events.contract_types');

Route::post('events/contract_types/{id}/fields', [ContractFieldController::class,'storeFields'])->name('events.contract_types.fields');
Route::get('contract_types/{id}/fields', [ContractFieldController::class, 'viewFields'])->name('events.contract_types.view_fields');

require __DIR__ . '/auth.php';
