<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\Category;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use Illuminate\Http\Request;
use Validator;

class PriceController extends Controller
{
    public function index()
    {
        $prices = Price::all();
        $categories = Category::all();
        $currencies = Currency::all();

        return view('settings.prices.index', compact('prices','categories', 'currencies', ));
    }
    public function show($id)
    {
        $price = Price::findOrFail($id);
        return response()->json($price);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $price = Price::create($request->all());
        return response()->json($price, '201');
    }

    public function update(Request $request, Price $price)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $price->update($request->all());
        return response()->json($price);
    }

    public function destroy(Price $price)
    {
        $price->delete();
        return response()->json(null, 204);
    }
}
