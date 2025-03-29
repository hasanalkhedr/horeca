<?php

namespace App\Http\Controllers;

use App\Models\Settings\Currency;
use App\Models\SponsorOption;
use App\Models\SponsorPackage;
use DB;
use Illuminate\Http\Request;
use Validator;

class SponsorPackageController extends Controller
{
    public function index()
    {
        $sponsorPackages = SponsorPackage::all();
        $allOptions = SponsorOption::all();
        $currencies = Currency::all();
        return view('sponsor_packages.index', compact('sponsorPackages', 'allOptions', 'currencies'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sponsorPackage = SponsorPackage::create($request->all());
        return response()->json($sponsorPackage, 201);
    }
    public function show($id)
    {
        $sponsorPackage = SponsorPackage::findOrFail($id);
        return response()->json($sponsorPackage);
    }
    public function update(Request $request, $id)
    {
        $sponsorPackage = SponsorPackage::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $sponsorPackage->update($request->all());
        return response()->json($sponsorPackage);
    }
    public function destroy($id)
    {
        $sponsorPackage = SponsorPackage::findOrFail($id);
        $sponsorPackage->delete();
        return response()->json(null, 204);
    }
    // Fetch related options for a specific sponsor package
    public function getRelatedOptions($id)
    {
        $sponsorPackage = SponsorPackage::with('sponsorOptions.currency')->findOrFail($id);
        return response()->json($sponsorPackage->sponsorOptions);
    }
    // Relate a new option to a sponsor package
    public function relateOption(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'option_id' => [
                'required',
                'exists:sponsor_options,id',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $exists = DB::table('sponsor_option_sponsor_package')
                        ->where('package_id', $id)
                        ->where('option_id', $request->option_id)
                        ->exists();

                    if ($exists) {
                        $fail('The package already have this option.');
                    }
                },
            ],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $sponsorPackage = SponsorPackage::findOrFail($id);

        // Relate the option if validation passes
        $sponsorPackage->sponsorOptions()->attach($request->option_id);

        return response()->json(['success' => true]);
    }
    // Unlink an option from a sponsor package
    public function unrelateOption($id, $optionId)
    {
        $sponsorPackage = SponsorPackage::findOrFail($id);
        $sponsorPackage->sponsorOptions()->detach($optionId);
        return response()->json(['success' => true]);
    }
}
