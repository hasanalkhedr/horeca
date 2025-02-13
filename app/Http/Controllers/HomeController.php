<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Stand;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function dashboard()
    {
        $availableStands = Stand::available()->get();
        $totalSpace = Stand::sum('space');
        $soldSpace = Stand::where('status', 'Sold')->sum('space');
        $lastContracts = Contract::orderBy('created_at', 'desc')->get();
        $currentEvents = Event::where('apply_start_date', '<=', Carbon::now())->where('apply_deadline_date', '>=', Carbon::now())->get();
        return view('dashboard', compact('availableStands', 'lastContracts', 'totalSpace', 'soldSpace', 'currentEvents'));
    }
}
