<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('events.index', compact('events'));
    }
    public function show($id)
    {
        $event = Event::findOrFail($id);
        return response()->json($event);
    }

    public function create() {
        return view('events.create');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'CODE' => 'required|unique|string|max:50',
            'name' => 'required|unique|string|max:100',
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'apply_start_date' => 'required|date',
            'apply_deadline_date' => 'required|date',
            'total_space' => 'required|decimal:0,3',
            'space_to_sell' => 'required|decimal:0,3',
            'free_space' => 'required|decimal:0,3',
            'remaining_space_to_sell' => 'required|decimal:0,3',
            'remaining_free_space' => 'required|decimal:0,3',
            'vat_rate' => 'required|decimal:0,3',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_method' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $event = Event::create($request->all());
        return response()->json($event, '201');
    }

    public function edit(Event $event) {
        return view('events.edit', $event);
    }
    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'CODE' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'apply_start_date' => 'required|date',
            'apply_deadline_date' => 'required|date',
            'total_space' => 'required|decimal:0,3',
            'space_to_sell' => 'required|decimal:0,3',
            'free_space' => 'required|decimal:0,3',
            'remaining_space_to_sell' => 'required|decimal:0,3',
            'remaining_free_space' => 'required|decimal:0,3',
            'vat_rate' => 'required|decimal:0,3',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_method' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $event->update($request->all());
        return response()->json($event);
    }
    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }


    public function dashboard(Request $request, Event $event) {
        return view('events.dashboard', compact('event'));
    }
}
