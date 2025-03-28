<?php

namespace App\Http\Controllers;

use App\Models\SponsorPackage;
use App\Models\SponsorOption;
use App\Models\Settings\Currency;
use Illuminate\Http\Request;
use Validator;

class SponsorPackageController extends Controller
{
    public function index(Request $request) {
        $packages = SponsorPackage::query();

        // Search by title
        if ($request->has('search')) {
            $search = $request->input('search');
            $packages->where('title', 'like', "%{$search}%");
        }

        // Sort by column
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $packages->orderBy($sort, $direction);
        }

        $packages = $packages->with(['currencies', 'sponsorOptions'])->paginate(10);

        return view('sponsor_packages.index', compact('packages'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'currencies' => 'nullable|array',
            'currencies.*.id' => 'exists:currencies,id',
            'currencies.*.price' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $package = SponsorPackage::create($request->only(['title']));

        // Sync currencies with prices
        if ($request->has('currencies')) {
            $currenciesWithPrices = [];
            foreach ($request->currencies as $currency) {
                $currenciesWithPrices[$currency['id']] = ['total_price' => $currency['price']];
            }
            $package->currencies()->sync($currenciesWithPrices);
        }

        return response()->json(['message' => 'Ads package created successfully']);
    }

    public function show(SponsorPackage $sponsorPackage) {
        $sponsorPackage->load(['currencies', 'sponsorOptions']);
        $currencies = Currency::all();

        // Get currencies not already assigned
        $assignedCurrencyIds = $sponsorPackage->currencies->pluck('id')->toArray();
        $availableCurrencies = Currency::whereNotIn('id', $assignedCurrencyIds)->get();

        // Get options not already assigned
        $assignedOptionIds = $sponsorPackage->sponsorOptions->pluck('id')->toArray();
        $availableOptions = SponsorOption::whereNotIn('id', $assignedOptionIds)->get();

        return view('sponsor_packages.show', compact(
            'sponsorPackage',
            'currencies',
            'availableCurrencies',
            'availableOptions'
        ));
    }

    public function update(Request $request, SponsorPackage $sponsorPackage) {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'currencies' => 'nullable|array',
            'currencies.*.id' => 'exists:currencies,id',
            'currencies.*.price' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sponsorPackage->update($request->only(['title']));

        // Sync currencies with prices
        if ($request->has('currencies')) {
            $currenciesWithPrices = [];
            foreach ($request->currencies as $currency) {
                $currenciesWithPrices[$currency['id']] = ['total_price' => $currency['price']];
            }
            $sponsorPackage->currencies()->sync($currenciesWithPrices);
        }

        return response()->json(['message' => 'Ads package updated successfully']);
    }

    public function destroy(SponsorPackage $sponsorPackage) {
        $sponsorPackage->delete();
        return response()->json(['message' => 'Ads package deleted successfully']);
    }

    public function attachOption(Request $request, SponsorPackage $sponsorPackage) {
        $request->validate([
            'sponsor_option_id' => 'required|exists:sponsor_options,id'
        ]);

        $sponsorPackage->sponsorOptions()->syncWithoutDetaching([$request->sponsor_option_id]);

        return response()->json(['message' => 'Option added to package successfully']);
    }

    public function detachOption(SponsorPackage $sponsorPackage, SponsorOption $sponsorOption) {
        $sponsorPackage->sponsorOptions()->detach($sponsorOption->id);

        return response()->json(['message' => 'Option removed from package successfully']);
    }

    public function addCurrency(Request $request, SponsorPackage $sponsorPackage) {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'price' => 'required|numeric|min:0'
        ]);

        $sponsorPackage->currencies()->syncWithoutDetaching([
            $request->currency_id => ['total_price' => $request->price]
        ]);

        return response()->json(['message' => 'Currency added to package successfully']);
    }

    public function removeCurrency(SponsorPackage $sponsorPackage, Currency $currency) {
        $sponsorPackage->currencies()->detach($currency->id);
        return response()->json(['message' => 'Currency removed from package successfully']);
    }
}
