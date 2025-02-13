<?php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\Event;
class ReportController extends Controller
{
    public function index(Event $event)
    {
        $reports = Report::all();
        return view('reports.index', compact('reports','event'));
    }

    public function show($id)
    {
        $report = Report::findOrFail($id);
        $components = $report->components; // No need for json_decode
        return view('reports.show', compact('report', 'components'));
    }
    public function destroy(Report $report) {
        $report->delete();
        return response()->json(null, 204);
    }
}
