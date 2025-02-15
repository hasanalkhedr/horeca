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
        $prices = $event->Prices()->get();
        $report = Report::find($request->report_id);
        $categories = $event->Categories()->get();
        $sponsor_packages = SponsorPackage::all();
        return view('contracts.create', compact('event', 'stands', 'prices', 'report', 'categories', 'sponsor_packages'));
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
        /*if($contract_type->name === 'Application Form')
        {
        $fieldValues = [
            'Stand No m x m' => $stand->space . ' sqm',
            'Company' => $company->name,
            'undefined_2' => $stand->no,
            'Price 1' => (string) ($stand->space * $price_amount),
            'Coordinator' => $coordinator->name,
            'Additional contact person' => $contact_person->name,
            'Sub total 1' => (string) ($stand->space * $price_amount),
            'Contact person' => $contact_person->name,
            'Shell scheme includes carpet wall panels signboard stand number power point and lighting' => $price_amount == 370,
            'Space only' => $price_amount == 350,
            'Special pavilion specify' => $price_amount != 370 && $price_amount != 350,
            'undefined_5' => (string) $price_amount != 370 && $price_amount != 350 ? $price_amount : '',
            'm2 x 370 US  m2' => $stand->space,
            'm2 x 350 US  m2' => $stand->space,
            'm2 x' => $stand->space,
            'BakeryPastry' => in_array('BakeryPastry', $cats),
            'Beverage' => in_array('Beverage', $cats),
            'Catering equipment' => in_array('Catering equipment', $cats),
            'Coffee  Tea Pavilion' => in_array('Coffee  Tea Pavilion', $cats),
            'Consultancy Recruitment  Franchise' => in_array('Consultancy Recruitment  Franchise', $cats),
            'Education' => in_array('Education', $cats),
            'Food' => in_array('Food', $cats),
            'Hygiene' => in_array('Hygiene', $cats),
            'Interiors' => in_array('Interiors', $cats),
            'International Pavilion' => in_array('International Pavilion', $cats),
            'undefined' => in_array('undefined', $cats),
            'Techzone' => in_array('Techzone', $cats)
        ];
        } else if($contract_type->name === 'arabic') {
            $fieldValues = [
                'Stand #' => $stand->no,
                'Company  Firm' => $company->name,
                'Daily Contact Person' => $contact_person->name,
                'Total_space1' =>$stand->space,
            ];
        }*/
        $contract = Contract::create([
            'contract_no' => $request->contract_no,
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
            'special_design_price' => $request->special_design_price,
            'if_water' => $request->if_water,
            'if_electricity' => $request->if_electricity,
            'electricity_text' => $request->electricity_text,
            'water_electricity_amount' => $request->water_electricity_amount,
            'new_product' => $request->new_product,
            //'advertisment_amount' => '',
            'sponsor_package_id' => $request->sponsor_package_id,
            'specify_text' => $request->specify_text,
            'notes1' => $request->notes1,
            'notes2' => $request->notes2,
        ]);
        $stand->status = 'Sold';
        $stand->save();
        // foreach ($fieldValues as $fieldKey => $fieldValue) {
        //     ContractValue::create([
        //         'contract_id' => $contract->id,
        //         'contract_field_id' => $contract_type->ContractFields()->where('field_name', $fieldKey)->first()->id,
        //         'field_value' => $fieldValue,
        //     ]);
        // }

        //$path = url('/storage/' . str_replace('\\\\', '/', $request->path));
        //return view('contracts.preview', compact('fieldValues', 'path', 'contract'));
        return view('contracts.preview', compact('contract'))->layout('components.layouts.app');
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

    public function edit(Contract $contract)
    {
        $stands = $contract->event->Stands()->get();
        $prices = $contract->event->Prices()->get();
        $contract_type = $contract->ContractType;
        $categories = [
            'BakeryPastry',
            'Beverage',
            'Catering equipment',
            'Coffee  Tea Pavilion',
            'Consultancy Recruitment  Franchise',
            'Education',
            'Food',
            'Hygiene',
            'Interiors',
            'International Pavilion',
            'undefined',
            'Techzone'
        ];
        return view('contracts.edit', compact('contract', 'stands', 'prices', 'contract_type', 'categories'));
    }

    public function update(Request $request, Contract $contract)
    {
        $cats = $request->categories;
        $stand = Stand::find($request->stand_id);
        $company = Company::find($request->company_id);
        $price = Price::find($request->price_id);
        $price_amount = $price ? $price->amount : $request->special_price_amount;
        $coordinator = Client::find($request->coordinator_id);
        $contact_person = Client::find($request->contact_person);
        $fieldValues = [
            'Stand No m x m' => $stand->space . ' sqm',
            'Company' => $company->name,
            'undefined_2' => $stand->no,
            'Price 1' => (string) ($stand->space * $price_amount),
            'Coordinator' => $coordinator->name,
            'Additional contact person' => $contact_person->name,
            'Sub total 1' => (string) ($stand->space * $price_amount),
            'Contact person' => $contact_person->name,
            'Shell scheme includes carpet wall panels signboard stand number power point and lighting' => $price_amount == 370,
            'Space only' => $price_amount == 350,
            'Special pavilion specify' => $price_amount != 370 && $price_amount != 350,
            'undefined_5' => (string) $price_amount != 370 && $price_amount != 350 ? $price_amount : '',
            'm2 x 370 US  m2' => $stand->space,
            'm2 x 350 US  m2' => $stand->space,
            'm2 x' => $stand->space,
            'BakeryPastry' => in_array('BakeryPastry', $cats),
            'Beverage' => in_array('Beverage', $cats),
            'Catering equipment' => in_array('Catering equipment', $cats),
            'Coffee  Tea Pavilion' => in_array('Coffee  Tea Pavilion', $cats),
            'Consultancy Recruitment  Franchise' => in_array('Consultancy Recruitment  Franchise', $cats),
            'Education' => in_array('Education', $cats),
            'Food' => in_array('Food', $cats),
            'Hygiene' => in_array('Hygiene', $cats),
            'Interiors' => in_array('Interiors', $cats),
            'International Pavilion' => in_array('International Pavilion', $cats),
            'undefined' => in_array('undefined', $cats),
            'Techzone' => in_array('Techzone', $cats)
        ];
        $contract_type = ContractType::find($request->contract_type_id);
        $contract->Stand->status = 'Available';
        $contract->Stand->save();
        $contract->update([
            'contract_no' => $request->contract_no,
            'company_id' => $request->company_id,
            'stand_id' => $request->stand_id,
            'price_id' => $request->price_id == 0 ? null : $request->price_id,
            'price_amount' => $request->price_id == 0 ? $request->special_price_amount : null,

            'contract_type_id' => $request->contract_type_id,
            'status' => 'draft',
            'space_amount' => $stand->space * $price_amount,
        ]);
        $stand->status = 'Sold';
        $stand->save();
        foreach ($contract->ContractValues as $contractValue) {
            $contractValue->delete();
        }
        foreach ($fieldValues as $fieldKey => $fieldValue) {
            ContractValue::create([
                'contract_id' => $contract->id,
                'contract_field_id' => $contract_type->ContractFields()->where('field_name', $fieldKey)->first()->id,
                'field_value' => $fieldValue,
            ]);
        }
        $path = url('/storage/' . str_replace('\\\\', '/', $request->path));
        return view('contracts.preview', compact('fieldValues', 'path', 'contract'));
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();
        return response()->json(null, 204);
    }
}
