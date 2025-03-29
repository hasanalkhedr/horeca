<?php

namespace App\Http\Controllers;

use App\Models\AdsOption;
use App\Models\Settings\Currency;
use Illuminate\Http\Request;
use Validator;

class AdsOptionController extends Controller
{
    public function index(Request $request) {
        $options = AdsOption::query();

        // Search by title
        if ($request->has('search')) {
            $search = $request->input('search');
            $options->where('title', 'like', "%{$search}%");
        }

        // Sort by column
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $options->orderBy($sort, $direction);
        }

        $options = $options->paginate(10);

        return view('ads-options.index', compact('options'));
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

        $option = AdsOption::create($request->only(['title', 'description']));

        // Sync currencies with prices
        if ($request->has('currencies')) {
            $currenciesWithPrices = [];
            foreach ($request->currencies as $currency) {
                $currenciesWithPrices[$currency['id']] = ['price' => $currency['price']];
            }
            $option->currencies()->sync($currenciesWithPrices);
        }

        return response()->json(['message' => 'Ads option created successfully']);
    }

    public function update(Request $request, AdsOption $adsOption) {
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

        $adsOption->update($request->only(['title', 'description']));

        // Sync currencies with prices
        if ($request->has('currencies')) {
            $currenciesWithPrices = [];
            foreach ($request->currencies as $currency) {
                $currenciesWithPrices[$currency['id']] = ['price' => $currency['price']];
            }
            $adsOption->currencies()->sync($currenciesWithPrices);
        }

        return response()->json(['message' => 'Ads option updated successfully']);
    }

    public function destroy(AdsOption $adsOption) {
        $adsOption->delete();
        return response()->json(['message' => 'Ads option deleted successfully']);
    }

    public function show(AdsOption $adsOption) {
        $adsOption->load('currencies');
        $currencies = Currency::all();

        // Get currencies not already assigned
        $assignedCurrencyIds = $adsOption->currencies->pluck('id')->toArray();
        $availableCurrencies = Currency::whereNotIn('id', $assignedCurrencyIds)->get();

        return view('ads-options.show', compact('adsOption', 'currencies', 'availableCurrencies'));
    }

    /**
     * Add currency to ads option
     */
    public function addCurrency(Request $request, AdsOption $adsOption) {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required|exists:currencies,id',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $adsOption->currencies()->syncWithoutDetaching([
            $request->currency_id => ['price' => $request->price]
        ]);

        return redirect()->back()->with('success', 'Currency added successfully');
    }

    /**
     * Remove currency from ads option
     */
    public function removeCurrency(AdsOption $adsOption, Currency $currency) {
        $adsOption->currencies()->detach($currency->id);
        return redirect()->back()->with('success', 'Currency removed successfully');
    }
}
