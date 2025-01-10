<?php

namespace App\Http\Controllers;

use App\Models\ContractType;
use Illuminate\Http\Request;
use App\Models\Event;
use Validator;
class ContractTypeController extends Controller
{
    public function index(Event $event)
    {
        return view('contract_types.index',compact('event'));
    }

    public function show($id)
    {
        $contract_type = ContractType::findOrFail($id);
        return response()->json($contract_type);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'event_id' => 'required|exists:events,id',
            'path' => 'required|mimes:pdf|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $event = Event::find($request->event_id);
        $path = $request->file('path')->storeAs(
            'uploads\\contract_forms',
            $event->CODE . '-' . $request->name .'.'. $request->file('path')->getClientOriginalExtension()
        );
        $contract_type = ContractType::create(array_merge($request->all(), [
            'path' => str_replace("\\", "\\\\", $path)
        ]));
        return response()->json($contract_type, '201');
    }

    public function update(Request $request, ContractType $contract_type)
    {
//dd($request);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'event_id' => 'required|exists:events,id',
            'path' => 'required|mimes:pdf|max:20480',
        ]);
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $event = Event::find($request->event_id);
        $path = $request->file('path')->storeAs(
            'uploads\\contract_forms',
            $event->CODE . '-' . $request->name .'.'. $request->file('path')->getClientOriginalExtension()
        );
        $contract_type->update(array_merge($request->all(), [
            'path' => str_replace("\\", "\\\\", $path)
        ]));
        return response()->json($contract_type);
    }
    public function destroy(ContractType $contract_type)
    {
        $contract_type->delete();
        return response()->json(null, 204);
    }
}
