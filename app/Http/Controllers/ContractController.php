<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Event;
use App\Models\Settings\Price;
use App\Models\Stand;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function create(Request $request, Event $event){
        $stands = $event->Stands()->get();
        $prices = $event->Prices()->get();
        $contract_type = ContractType::find($request->contract_type_id);
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
        return view('contracts.create', compact('event', 'stands', 'prices', 'contract_type', 'categories'));
    }
    public function store(Request $request) {
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
            'Price 1' => (string) ( $stand->space * $price_amount ),
            'Coordinator' => $coordinator->name,
            'Additional contact person' => $contact_person->name,
            'Sub total 1' => (string) ( $stand->space * $price_amount ),
            'Contact person' => $contact_person->name,
            'Shell scheme includes carpet wall panels signboard stand number power point and lighting' =>  $price_amount == 370,
            'Space only' => $price_amount == 350,
            'Special pavilion specify' => $price_amount != 370 &&  $price_amount != 350 ,
            'undefined_5' => (string) $price_amount != 370 &&  $price_amount != 350 ? $price_amount : '',
            'm2 x 370 US  m2' => $stand->space,
            'm2 x 350 US  m2' => $stand->space,
            'm2 x' => $stand->space,
            'BakeryPastry' => in_array('BakeryPastry',$cats),
            'Beverage' => in_array('Beverage',$cats),
            'Catering equipment' => in_array('Catering equipment',$cats),
            'Coffee  Tea Pavilion' => in_array('Coffee  Tea Pavilion',$cats),
            'Consultancy Recruitment  Franchise' => in_array('Consultancy Recruitment  Franchise',$cats),
            'Education' => in_array('Education',$cats),
            'Food' => in_array('Food',$cats),
            'Hygiene' => in_array('Hygiene',$cats),
            'Interiors' => in_array('Interiors',$cats),
            'International Pavilion' => in_array('International Pavilion',$cats),
            'undefined' => in_array('undefined',$cats),
            'Techzone' => in_array('Techzone',$cats)
        ];
        $contract_type = ContractType::find($request->contract_type_id);

        // $fields = [];

        // foreach ($contract_type->ContractFields as $field) {
        //     $fields[$field->field_name] = $request->get(str_replace(' ','_',$field->field_name));
        // }

        $path =url('/storage/'. str_replace('\\\\','/',$request->path));
        return view('contracts.preview', compact('fieldValues', 'path'));
    }
}
