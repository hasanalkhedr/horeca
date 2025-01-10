<?php

namespace App\Http\Controllers;

use App\Models\Settings\Currency;
use App\Models\SponsorOption;
use Illuminate\Http\Request;
use Validator;

class SponsorOptionController extends Controller
{
    public function index()
    {
        $sponsorOptions = SponsorOption::all();
        $currencies = Currency::all();
        return view('sponsor_options.index', compact('sponsorOptions', 'currencies'));
    }
    public function show($id)
    {
        $sponsorOption = SponsorOption::findOrFail($id);
        return response()->json($sponsorOption);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'currency_id' => 'nullable|exists:currencies,id',
            'price' => 'nullable|decimal:0,3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sponsorOption = SponsorOption::create($request->all());
        return response()->json($sponsorOption, 201);
    }
    public function update(Request $request, $id)
    {
        $sponsorOption = SponsorOption::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'currency_id' => 'nullable|exists:currencies,id',
            'price' => 'nullable|decimal:0,3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sponsorOption->update($request->all());
        return response()->json($sponsorOption);
    }
    public function destroy($id)
    {
        $sponsorOption = SponsorOption::findOrFail($id);
        $sponsorOption->delete();
        return response()->json(null, 204);
    }
}
