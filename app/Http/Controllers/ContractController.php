<?php

namespace App\Http\Controllers;

use App\Models\Contract;

class ContractController extends Controller
{
    public function preview(Contract $contract)
    {
        return view('contracts.preview', compact('contract'))->layout('components.layouts.app');
    }
}
