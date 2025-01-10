<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Company;
use Illuminate\Http\Request;
use Validator;

class BrandController extends Controller
{
    public function index(Company $company)
    {
        $brands = Brand::all();
        $companies = Company::all();
        return view('brands.index', compact('brands', 'companies', 'company'));
    }

    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        return response()->json($brand);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'logo' => 'required|string',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $brand = Brand::create($request->all());
        return response()->json($brand, '201');
    }

    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'logo' => 'required|string',
            'company_id' => 'required|exists:companies,id',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $brand->update($request->all());
        return response()->json($brand);
    }
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return response()->json(null, 204);
    }
}
