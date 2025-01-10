<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Http\Request;
use Validator;

class ClientController extends Controller
{
    public function index(Company $company)
    {
        $clients = Client::all();
        $companies = Company::all();
        return view('clients.index', compact('clients', 'companies', 'company'));
    }

    public function show($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'position' => 'nullable|string',
            'mobile' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $client = Client::create($request->all());
        return response()->json($client, '201');
    }

    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'position' => 'nullable|string',
            'mobile' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'company_id' => 'required|exists:companies,id',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $client->update($request->all());
        return response()->json($client);
    }
    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(null, 204);
    }
}
