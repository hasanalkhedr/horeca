<?php

namespace App\Http\Controllers;

use App\Models\EffAdsPackage;
use App\Models\EffAdsOption;
use App\Models\Settings\Currency;
use Illuminate\Http\Request;
use Validator;

class EffAdsPackageController extends Controller
{
    public function index(Request $request) {
        $packages = EffAdsPackage::query();

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

        $packages = $packages->with(['currencies', 'effAdsOptions'])->paginate(10);

        return view('eff-ads-packages.index', compact('packages'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'currencies' => 'nullable|array',
            'currencies.*.id' => 'exists:currencies,id',
            'currencies.*.price' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $package = EffAdsPackage::create($request->only(['title', 'description']));

        // Sync currencies with prices
        if ($request->has('currencies')) {
            $currenciesWithPrices = [];
            foreach ($request->currencies as $currency) {
                $currenciesWithPrices[$currency['id']] = ['total_price' => $currency['price']];
            }
            $package->currencies()->sync($currenciesWithPrices);
        }

        return response()->json(['message' => 'Effective Ads package created successfully']);
    }

    public function show(EffAdsPackage $adsPackage) {
        $adsPackage->load(['currencies', 'effAdsOptions']);
        $currencies = Currency::all();

        // Get currencies not already assigned
        $assignedCurrencyIds = $adsPackage->currencies->pluck('id')->toArray();
        $availableCurrencies = Currency::whereNotIn('id', $assignedCurrencyIds)->get();

        // Get options not already assigned
        $assignedOptionIds = $adsPackage->EffAdsOptions->pluck('id')->toArray();
        $availableOptions = EffAdsOption::whereNotIn('id', $assignedOptionIds)->get();

        return view('eff-ads-packages.show', compact(
            'adsPackage',
            'currencies',
            'availableCurrencies',
            'availableOptions'
        ));
    }

    public function update(Request $request, EffAdsPackage $adsPackage) {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'currencies' => 'nullable|array',
            'currencies.*.id' => 'exists:currencies,id',
            'currencies.*.price' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $adsPackage->update($request->only(['title', 'description']));

        // Sync currencies with prices
        if ($request->has('currencies')) {
            $currenciesWithPrices = [];
            foreach ($request->currencies as $currency) {
                $currenciesWithPrices[$currency['id']] = ['total_price' => $currency['price']];
            }
            $adsPackage->currencies()->sync($currenciesWithPrices);
        }

        return response()->json(['message' => 'Effective Ads package updated successfully']);
    }

    public function destroy(EffAdsPackage $adsPackage) {
        $adsPackage->delete();
        return response()->json(['message' => 'Effective Ads package deleted successfully']);
    }

    public function attachOption(Request $request, EffAdsPackage $adsPackage) {
        $request->validate([
            'eff_ads_option_id' => 'required|exists:eff_ads_options,id'
        ]);

        $adsPackage->EffAdsOptions()->syncWithoutDetaching([$request->eff_ads_option_id]);

        return response()->json(['message' => 'Option added to package successfully']);
    }

    public function detachOption(EffAdsPackage $adsPackage, EffAdsOption $adsOption) {
        $adsPackage->EffAdsOptions()->detach($adsOption->id);

        return response()->json(['message' => 'Option removed from package successfully']);
    }

    public function addCurrency(Request $request, EffAdsPackage $adsPackage) {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'price' => 'required|numeric|min:0'
        ]);

        $adsPackage->currencies()->syncWithoutDetaching([
            $request->currency_id => ['total_price' => $request->price]
        ]);

        return response()->json(['message' => 'Currency added to package successfully']);
    }

    public function removeCurrency(EffAdsPackage $adsPackage, Currency $currency) {
        $adsPackage->currencies()->detach($currency->id);
        return response()->json(['message' => 'Currency removed from package successfully']);
    }
}
