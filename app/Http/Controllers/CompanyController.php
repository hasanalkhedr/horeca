<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Validator;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();

        return view('companies.index', compact('companies'));
    }

    public function show($id)
    {
        $company = Company::findOrFail($id);
        return response()->json($company);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'CODE' => 'required|string',
            'commerical_registry_number' => 'nullable|string',
            'vat_number' => 'nullable|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'street' => 'nullable|string',
            'po_box' => 'nullable|string',
            'mobile' => 'nullable|string',
            'phone' => 'nullable|string',
            'additional_number' => 'nullable|string',
            'fax' => 'nullable|string',
            'email' => 'nullable|string',
            'website' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            'instagram_link' => 'nullable|string',
            'x_link' => 'nullable|string',
            'stand_name' => 'nullable|string',
            'logo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $company = Company::create($request->all());
        return response()->json($company, '201');
    }

    public function update(Request $request, Company $company)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'CODE' => 'required|string',
            'commerical_registry_number' => 'nullable|string',
            'vat_number' => 'nullable|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'street' => 'nullable|string',
            'po_box' => 'nullable|string',
            'mobile' => 'nullable|string',
            'phone' => 'nullable|string',
            'additional_number' => 'nullable|string',
            'fax' => 'nullable|string',
            'email' => 'nullable|string',
            'website' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            'instagram_link' => 'nullable|string',
            'x_link' => 'nullable|string',
            'stand_name' => 'nullable|string',
            'logo' => 'nullable|string',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $company->update($request->all());
        return response()->json($company);
    }
    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(null, 204);
    }
}
