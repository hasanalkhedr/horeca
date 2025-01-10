<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Settings\PaymentRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentRateController extends Controller
{
    public function index()
    {
        // Fetch all payment rates to display in the view
        $paymentRates = PaymentRate::all();
        $events = Event::all();
        return view('settings.payment_rates.index', compact('paymentRates', 'events')); // Pass the data to the view

    }
    public function show($id)
    {
        $paymentRate = PaymentRate::findOrFail($id);
        return response()->json($paymentRate);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'order' => 'required|integer|min:0',
            'date_to_pay' => 'required|date',
            'event_id' => 'requires|exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentRate = PaymentRate::create($request->all());
        return response()->json($paymentRate, 201);
    }
    public function update(Request $request, $id)
    {
        $paymentRate = PaymentRate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'order' => 'required|integer|min:0',
            'date_to_pay' => 'required|date',
            'event_id' => 'requires|exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentRate->update($request->all());
        return response()->json($paymentRate);
    }
    public function destroy($id)
    {
        $paymentRate = PaymentRate::findOrFail($id);
        $paymentRate->delete();
        return response()->json(null, 204);
    }
}
