<?php
namespace App\Http\Controllers;

use App\Models\Report;
class ReportController extends Controller
{
    public function show($id)
    {
        $report = Report::findOrFail($id);
        $components = $report->components; // No need for json_decode
        return view('reports.show', compact('report', 'components'));
    }
}
