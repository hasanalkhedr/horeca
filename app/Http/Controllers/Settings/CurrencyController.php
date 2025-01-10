<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\Currency;
use Illuminate\Http\Request;
use Validator;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return view('settings.currencies.index', compact('currencies'));
    }
    public function show($id)
    {
        $currency = Currency::findOrFail($id);
        return response()->json($currency);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'CODE' => 'required|string|max:10',
            'country' => 'nullable|string|max:255',
            'rate_to_usd' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $currency = Currency::create($request->all());
        return response()->json($currency, '201');
    }

    public function update(Request $request, Currency $currency)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'CODE' => 'required|string|max:10',
            'country' => 'nullable|string|max:255',
            'rate_to_usd' => 'nullable|numeric',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $currency->update($request->all());
        return response()->json($currency);
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return response()->json(null, 204);
    }
}
