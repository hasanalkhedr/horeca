<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\ContractValue;
use App\Models\Event;
use App\Models\Report;
use App\Models\Settings\Price;
use App\Models\SponsorPackage;
use App\Models\Stand;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Event $event)
    {
        return view('contracts.index', compact('event'));
    }
    public function create(Request $request, Event $event)
    {
        $stands = $event->availableStands()->get();
        $report = Report::find($request->report_id);
        $prices = $event->Prices()->where('currency_id', $report->Currency->id)->get();
        $categories = $event->Categories()->get();
        $sponsor_packages = $event->SponsorPackages;// SponsorPackage::all();
        $users = User::all();
        return view('contracts.create', compact('event', 'stands', 'prices', 'report', 'categories', 'sponsor_packages', 'users'));
    }
    public function store(Request $request)
    {
        $event = Event::find($request->event_id);
        $report = Report::find($request->report_id);
        $cats = $request->categories;
        $stand = Stand::find($request->stand_id);
        $company = Company::find($request->company_id);
        $price = Price::find($request->price_id);
        $price_amount = $price ? $price->amount : $request->special_price_amount;
        $coordinator = Client::find($request->coordinator_id);
        $contact_person = Client::find($request->contact_person);

        $contract = Contract::create([
            'contract_no' => $request->contract_no,
            'contract_date' => Carbon::parse($request->contract_date),
            'company_id' => $request->company_id,
            'stand_id' => $request->stand_id,
            'price_id' => $request->price_id == 0 ? null : $request->price_id,
            'price_amount' => $request->price_id == 0 ? $request->special_price_amount : null,
            'event_id' => $request->event_id,
            'report_id' => $request->report_id,
            'status' => 'draft',
            'space_amount' => $stand->space * $price_amount,
            'contact_person' => $request->contact_person,
            'exhabition_coordinator' => $request->coordinator_id,
            'special_design_text' => $request->special_design_text,
            'special_design_price' => $request->special_design_price ? $request->special_design_price : 0,
            'if_water' => $request->if_water ? $request->if_water : 0,
            'if_electricity' => $request->if_electricity ? $request->if_electricity : 0,
            'electricity_text' => $request->electricity_text,
            'water_electricity_amount' => ($request->if_water || $request->if_electricity) ? $request->water_electricity_amount : 0,
            'new_product' => $request->new_product,
            //'advertisment_amount' => '',
            'sponsor_package_id' => $request->sponsor_package_id,
            'specify_text' => $request->specify_text,
            'notes1' => $request->notes1,
            'notes2' => $request->notes2,
            'category_id' => $request->categories ? json_decode($request->categories[0])->id : null,
            'seller' => $request->seller,
        ]);
        $stand->status = 'Sold';
        $stand->save();

        return view('contracts.preview', compact('contract'))->layout('components.layouts.app');
    }
    public function edit(Contract $contract)
    {
        $stands = $contract->event->Stands()->get();
        $report = $contract->Report;
        $prices = $contract->event->Prices()->where('currency_id', $report->Currency->id)->get();
        $categories = $contract->event->Categories()->get();
        $sponsor_packages = $contract->event->SponsorPackages;// SponsorPackage::all();
        $users = User::all();
        return view('contracts.edit', compact('contract', 'stands', 'prices', 'report', 'categories', 'sponsor_packages', 'users'));
    }
    public function update(Request $request, Contract $contract)
    {
        $newStand = Stand::find($request->stand_id);
        $oldStand = $contract->Stand;
        $company = Company::find($request->company_id);
        $price = Price::find($request->price_id);
        $price_amount = $price ? $price->amount : $request->special_price_amount;
        $coordinator = Client::find($request->coordinator_id);
        $contact_person = Client::find($request->contact_person);

        $contract->update([
            //'contract_no' => $request->contract_no,
            //'contract_date' => Carbon::parse($request->contract_date),
            'company_id' => $request->company_id,
            'stand_id' => $request->stand_id,
            'price_id' => $request->price_id == 0 ? null : $request->price_id,
            'price_amount' => $request->price_id == 0 ? $request->special_price_amount : null,
            //'event_id' => $request->event_id,
            //'report_id' => $request->report_id,
            'status' => 'draft',
            'space_amount' => $newStand->space * $price_amount,
            'contact_person' => $request->contact_person,
            'exhabition_coordinator' => $request->coordinator_id,
            'special_design_text' => $request->special_design_text,
            'special_design_price' => $request->special_design_price ? $request->special_design_price : 0,
            'if_water' => $request->if_water ? $request->if_water : 0,
            'if_electricity' => $request->if_electricity ? $request->if_electricity : 0,
            'electricity_text' => $request->electricity_text,
            'water_electricity_amount' => ($request->if_water || $request->if_electricity) ? $request->water_electricity_amount : 0,
            'new_product' => $request->new_product,
            //'advertisment_amount' => '',
            'sponsor_package_id' => $request->sponsor_package_id,
            'specify_text' => $request->specify_text,
            'notes1' => $request->notes1,
            'notes2' => $request->notes2,
            'category_id' => $request->categories ? json_decode($request->categories[0])->id : null,
            'seller' => $request->seller,
        ]);
        if($newStand->id != $oldStand->id) {
            $oldStand->status = 'Available';
            $oldStand->save();
            $newStand->status = 'Sold';
            $newStand->save();
        }

        return view('contracts.preview', compact('contract'))->layout('components.layouts.app');

    }
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return response()->json(null, 204);
    }
    public function preview(Contract $contract)
    {
        return view('contracts.preview', compact('contract'))->layout('components.layouts.app');
    }
    public function uploadPDF(Request $request, Contract $contract)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/pdf',
        ]);

        // Save the file to storage
        $path = $request->file('file')->storeAs(
            'uploads\\contracts',
            $contract->contract_no . '-' . $contract->Event->CODE . '-' . $contract->ContractType->name . '.pdf',
        );
        $contract->path = $path;
        $contract->save();
        return response()->json([
            'message' => 'PDF uploaded successfully',
            'path' => $path,
        ]);

    }
}
