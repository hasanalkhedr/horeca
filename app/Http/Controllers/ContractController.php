<?php

namespace App\Http\Controllers;

use App\Models\AdsOption;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Report;
use App\Models\Settings\Price;
use App\Models\SponsorPackage;
use App\Models\Stand;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        //$prices = $event->Prices()->where('currency_id', $report->Currency->id)->get();
        $prices = $event->Prices()->get();
        $categories = $event->Categories()->get();
        $sponsor_packages = $event->SponsorPackages;// SponsorPackage::all();
        $users = User::all();
        return view('contracts.create', compact('event', 'stands', 'prices', 'report', 'categories', 'sponsor_packages', 'users'));
    }
    public function store(Request $request)
    {
        $stand = Stand::find($request->stand_id);
        $contract = Contract::create([
            'contract_no' => $request->contract_no,
            'contract_date' => Carbon::parse($request->contract_date),
            'company_id' => $request->company_id,
            'exhabition_coordinator' => $request->coordinator_id,
            'contact_person' => $request->contact_person,
            'category_id' => $request->categories ? json_decode($request->categories[0])->id : null,
            'stand_id' => $request->stand_id,
            'price_id' => $request->price_id == 0 ? null : $request->price_id,
            'price_amount' => $request->price_id == 0 ? $request->special_price_amount : null,
            'event_id' => $request->event_id,
            'report_id' => $request->report_id,
            'status' => 'draft',
            'space_amount' => $request->space_amount ?? 0,
            'special_design_text' => $request->special_design_text,
            'special_design_price' => $request->special_design_price ? $request->special_design_price : 0,
            'if_water' => $request->if_water ? $request->if_water : 0,
            'if_electricity' => $request->if_electricity ? $request->if_electricity : 0,
            'electricity_text' => $request->electricity_text,
            'water_electricity_amount' => ($request->if_water || $request->if_electricity) ? $request->water_electricity_amount : 0,
            'new_product' => $request->new_product,
            'sponsor_package_id' => $request->sponsor_package_id,
            'sponsor_amount' => $request->sponsor_amount ?? 0,
            'specify_text' => $request->specify_text,
            'notes1' => $request->notes1,
            'notes2' => $request->notes2,
            'seller' => $request->seller,
            'ads_check' => $request->ads_check,
            'advertisment_amount' => $request->advertisment_amount ?? 0,
            'space_discount' => $request->space_discount ?? 0,
            'space_net' => $request->space_net ?? 0,
            'sponsor_discount' => $request->sponsor_discount ?? 0,
            'sponsor_net' => $request->sponsor_net ?? 0,
            'ads_discount' => $request->ads_discount ?? 0,
            'ads_net' => $request->ads_net ?? 0,
            'sub_total_1' => $request->sub_total_1,
            'd_i_a' => $request->d_i_a,
            'sub_total_2' => $request->sub_total_2,
            'vat_amount' => $request->vat_amount,
            'net_total' => $request->net_total,
        ]);
        $stand->status = 'Sold';
        $stand->save();
        $contract->d_i_a = $request->d_i_a;
        $contract->sub_total_2 = $request->sub_total_2;
        $contract->vat_amount = $request->vat_amount;
        $contract->net_total = $request->net_total;
        $contract->save();
        return redirect()->route('contracts.index');
        //return view('contracts.preview', compact('contract'))->layout('components.layouts.app');
    }
    public function edit(Contract $contract)
    {
        $stands = $contract->event->Stands()->get();
        $report = $contract->Report;
        //$prices = $contract->event->Prices()->where('currency_id', $report->Currency->id)->get();
        $prices = $contract->event->Prices()->get();
        $categories = $contract->event->Categories()->get();
        $sponsor_packages = $contract->event->SponsorPackages;// SponsorPackage::all();
        $users = User::all();
        return view('contracts.edit', compact('contract', 'stands', 'prices', 'report', 'categories', 'sponsor_packages', 'users'));
    }
    public function update(Request $request, Contract $contract)
    {
        $newStand = Stand::find($request->stand_id);
        $oldStand = $contract->Stand;

        $contract->update([

            'company_id' => $request->company_id,
            'exhabition_coordinator' => $request->coordinator_id,
            'contact_person' => $request->contact_person,
            'category_id' => $request->categories ? json_decode($request->categories[0])->id : null,
            'stand_id' => $request->stand_id,
            'price_id' => $request->price_id == 0 ? null : $request->price_id,
            'price_amount' => $request->price_id == 0 ? $request->special_price_amount : null,

            'status' => 'draft',
            'space_amount' => $request->space_amount ?? 0,
            'special_design_text' => $request->special_design_text,
            'special_design_price' => $request->special_design_price ? $request->special_design_price : 0,
            'if_water' => $request->if_water ? $request->if_water : 0,
            'if_electricity' => $request->if_electricity ? $request->if_electricity : 0,
            'electricity_text' => $request->electricity_text,
            'water_electricity_amount' => ($request->if_water || $request->if_electricity) ? $request->water_electricity_amount : 0,
            'new_product' => $request->new_product,
            'sponsor_package_id' => $request->sponsor_package_id,
            'sponsor_amount' => $request->sponsor_amount ?? 0,
            'specify_text' => $request->specify_text,
            'notes1' => $request->notes1,
            'notes2' => $request->notes2,
            'seller' => $request->seller,
            'ads_check' => $request->ads_check,
            'advertisment_amount' => $request->advertisment_amount ?? 0,
            'space_discount' => $request->space_discount ?? 0,
            'space_net' => $request->space_net ?? 0,
            'sponsor_discount' => $request->sponsor_discount ?? 0,
            'sponsor_net' => $request->sponsor_net ?? 0,
            'ads_discount' => $request->ads_discount ?? 0,
            'ads_net' => $request->ads_net ?? 0,
            'sub_total_1' => $request->sub_total_1,
            'd_i_a' => $request->d_i_a,
            'sub_total_2' => $request->sub_total_2,
            'vat_amount' => $request->vat_amount,
            'net_total' => $request->net_total,
        ]);
        // $contract->d_i_a = $request->d_i_a;
        // $contract->sub_total_2 = $request->sub_total_2;
        // $contract->vat_amount = $request->vat_amount;
        // $contract->net_total = $request->net_total;
        // $contract->save();
        if ($newStand->id != $oldStand->id) {
            $oldStand->status = 'Available';
            $oldStand->save();
            $newStand->status = 'Sold';
            $newStand->save();
        }

        return redirect()->route('contracts.index');
        //return view('contracts.preview', compact('contract'))->layout('components.layouts.app');

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
