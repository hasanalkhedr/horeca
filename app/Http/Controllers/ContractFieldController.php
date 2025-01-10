<?php

namespace App\Http\Controllers;

use App\Models\ContractField;
use App\Models\ContractType;
use Illuminate\Http\Request;

class ContractFieldController extends Controller
{
    public function storeFields(Request $request, $id)
    {
        if($request->fieldData) {
            $fields = json_decode($request->fieldData);
            foreach ($fields as $field) {
                ContractField::create([
                    'contract_type_id' => $id ?? ContractField::max('id')+1,
                    'field_name' => $field->name,
                    'field_type' => $field->type,
                ]);
            }
        }
    }
    public function viewFields(Request $request, $id)
    {
        $contract_type = ContractType::find($id);
        $fields = $contract_type->ContractFields;
        return view('contract_fields.view_fields',compact('contract_type', 'fields'));
    }
}
