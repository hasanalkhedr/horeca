<?php

namespace App\Http\Controllers\Settings;
use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Models\Settings\BankAccount;
use Illuminate\Http\Request;
use Validator;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::all();
        $events = Event::all();
        return view('settings.bank_accounts.index', compact('accounts','events'));
    }
    public function show($id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        return response()->json($bankAccount);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'IBAN' => 'required|string',
            'swift_code' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'event_id' => 'required|exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $account = BankAccount::create($request->all());
        return response()->json($account, '201');
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'IBAN' => 'required|string',
            'swift_code' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'event_id' => 'required|exists:events,id'
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $bankAccount->update($request->all());
        return response()->json($bankAccount);
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();
        return response()->json(null, 204);
    }
}
