<?php

namespace App\Http\Controllers;

use App\Imports\StandsImport;
use App\Models\Event;
use App\Models\Settings\Category;
use App\Models\Settings\Land;
use App\Models\Stand;
use App\Models\StandType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class StandController extends Controller
{
    public function index(Event $event)
    {
        $stands = Stand::all();
        $events = Event::all();
        //$stand_types = StandType::all();
        $categories = Category::all();
        return view('stands.index', compact('stands', 'events',/* 'stand_types',*/ 'categories', 'event'));
    }

    public function show($id)
    {
        $stand = Stand::findOrFail($id);
        return response()->json($stand);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no' => 'required|string|max:20',
            'space' => 'required|decimal:0,3',
            'event_id' => 'required|exists:events,id',
            'category_id' => 'nullable|exists:categories,id',
            //'stand_type_id' => 'nullable|exists:stand_types,id',
            'deductable' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $stand = Stand::create($request->all());
        return response()->json($stand, '201');
    }
    public function storeMany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prefix' => 'required|string',
            'num' => 'required|numeric|min:0',
            'space' => 'required|decimal:0,3',
            'event_id' => 'required|exists:events,id',
            'category_id' => 'nullable|exists:categories,id',
            //'stand_type_id' => 'nullable|exists:stand_types,id',
            'deductable' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $num = $request->num;
        for ($i = 1; $i <= $num; $i++) {
            Stand::create([
                'no' => $request->prefix . '-' . $i,
                'space' => $request->space,
                'event_id' => $request->event_id,
                'category_id' => $request->category_id,
                'deductable' => $request->deductable,
            ]);
        }
        return response()->json([], '201');
    }
    public function import(Request $request, int $event_id)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Validate the file type and size
        ]);

        if ($request->hasFile('file')) {
            try {
                $file = $request->file('file');
                Excel::import(new StandsImport($event_id), $file);

                return response()->json(['message' => 'File imported successfully!'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'File import failed: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function update(Request $request, Stand $stand)
    {
        $validator = Validator::make($request->all(), [
            'no' => 'required|string|max:20',
            'space' => 'required|decimal:0,3',
            'event_id' => 'required|exists:events,id',
            'category_id' => 'nullable|exists:categories,id',
            //'stand_type_id' => 'nullable|exists:stand_types,id',
            'deductable' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $stand->update($request->all());
        return response()->json($stand);
    }
    public function destroy(Stand $stand)
    {
        $stand->delete();
        return response()->json(null, 204);
    }
    public function block(Stand $stand)
    {
        if ($stand->status == 'Available') {
            $stand->update(['status' => 'Reserved']);
            return response()->json($stand);
        } else {
            return response()->json(['error' => 'You can not block sold or preBlocked stand'], 422);
        }
    }
}
