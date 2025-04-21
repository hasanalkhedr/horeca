<?php

// namespace App\Livewire;

// use Livewire\Component;

// class ClientSelect extends Component
// {
//     public $model;          // Parent model (e.g., Company)
//     public $dependentModel; // Dependent model (e.g., Client)
//     public $foreignKey;     // Foreign key in the dependent model
//     public $primaryKey;     // Primary key of the parent model
//     public $parentField;    // Selected parent field value
//     public $options = [];   // Options for the dependent select
//     public $placeholder;    // Placeholder for the dependent select
//     public $parentLabel;
//     public $childLabel;
//     public $child2Label;
//     public $coordinatorId;  // Existing coordinator_id
//     public $contactPerson;  // Existing contact_person

//     public function mount(
//         $model,
//         $parentLabel,
//         $childLabel,
//         $child2Label,
//         $dependentModel,
//         $foreignKey,
//         $primaryKey = 'id',
//         $placeholder = 'Select an option',
//         $parentField = null, // Existing company_id
//         $coordinatorId = null, // Existing coordinator_id
//         $contactPerson = null // Existing contact_person
//     ) {
//         $this->model = $model;
//         $this->dependentModel = $dependentModel;
//         $this->foreignKey = $foreignKey;
//         $this->primaryKey = $primaryKey;
//         $this->placeholder = $placeholder;
//         $this->parentLabel = $parentLabel;
//         $this->childLabel = $childLabel;
//         $this->child2Label = $child2Label;
//         $this->parentField = $parentField;
//         $this->coordinatorId = $coordinatorId;
//         $this->contactPerson = $contactPerson;

//         // Initialize options if parentField is set
//         if ($this->parentField) {
//             $this->myupdatedParentField();
//         }
//     }

//     public function myupdatedParentField()
//     {
//         if ($this->parentField) {
//             $this->options = app($this->dependentModel)
//                 ->where($this->foreignKey, $this->parentField)
//                 ->get([$this->primaryKey, 'name']); // Adjust 'name' field as needed
//         } else {
//             $this->options = [];
//         }
//     }

//     public function render()
//     {
//         return view('livewire.client-select');
//     }
// }

namespace App\Livewire;

use App\Models\Client;
use App\Models\Company;
use File;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ClientSelect extends Component
{
    public $searchTerm = '';
    public $companyResults = [];
    public $selectedCompany = null;

    public $coordinators = [];
    public $contactPersons = [];

    public $coordinatorId = null;
    public $contactPerson = null;

    public string $apiToken = '8fadcb195d24b8130c0d63881b55ec51364df22a';

    public function mount($selectedCompany = null, $coordinatorId = null, $contactPerson = null)
    {
        $this->selectedCompany = $selectedCompany;
        $this->coordinatorId = $coordinatorId;
        $this->contactPerson = $contactPerson;

        if ($this->selectedCompany) {
            $company = Company::find($this->selectedCompany);
            if ($company) {
                $this->coordinators = $company->clients;
                $this->contactPersons = $company->clients;
                $this->searchTerm = $company->name;
            }
        }
    }

    public function performCompanySearch()
    {
        if (strlen($this->searchTerm) < 2) {
            $this->companyResults = [];
            return;
        }

        $response = Http::get("https://hospitalityservices.pipedrive.com/api/v1/organizations/search", [
            'term' => $this->searchTerm,
            'exact_match' => false,
            'fields' => 'name',
            'api_token' => $this->apiToken,
        ]);

        $this->companyResults = $response->json('data.items') ?? [];
    }

    public function getCountryFromJson($country_code)
    {
        $json_file = File::get('countries.json');
        $data = json_decode($json_file, true);
        $result = array_reduce($data, function ($carry, $item) {
            $carry[$item['id']] = $item['label'];
            return $carry;
        }, []);
        return strlen($country_code) > 0 ? $result[$country_code] : '';
    }
    public function getCityFromJson($city_code)
    {
        $json_file = File::get('cities.json');
        $data = json_decode($json_file, true);
        $result = array_reduce($data, function ($carry, $item) {
            $carry[$item['id']] = $item['label'];
            return $carry;
        }, []);
        return strlen($city_code) > 0? $result[$city_code] : '';
    }
    public function selectCompany($companyId)
    {
        $company = collect($this->companyResults)->firstWhere('item.id', $companyId)['item'] ?? null;
        if (!$company) {
            return;
        }

        // Save to DB if not already stored
        $local = Company::where('pipe_id', $companyId)->first();
        if (!$local) {
            $c = $this->fetchCompanyDetails($company['id']);
            $local = Company::create([
                'pipe_id' => $c['id'], // external ID as primary key
                'name' => $c['name'],
                'CODE' => $c['label'] ?? 'NOT_SET',
                //'commerical_registry_number' => $c[''],
                //'vat_number' => $c[''],
                'country' => $this->getCountryFromJson($c['affb271863709112a116d275ffc4d573ed7853c7']),
                'city' => $this->getCityFromJson($c['6182d0aaa2d68c248363bf6ff1cef5ef87c21799']),
                'street' => $c['e7c3f6cfd690ba7594c56660244a785d6b43c30e'],
                'po_box' => $c['0323c753854315f57d32dcbddbbc1b8738766799'],
                'mobile' => $c['588c3b4b858758dca5821c8db6ce5d208974cf2f'] . strlen($c['e19adc02be527463d1385a19de3c5ac26eea329f']) > 0 ? ', ' . $c['e19adc02be527463d1385a19de3c5ac26eea329f'] : '',
                'phone' => $c['2b7ec723f87ca44956cf5f3976119240d0dc238c'] . strlen($c['1a3a780bdc0be690f487db21056f2db76680cecb']) > 0 ? ', ' . $c['1a3a780bdc0be690f487db21056f2db76680cecb'] : '',
                'additional_number' => $c['486c3d65d60839be55a60dba0a9ffa3c4b59ab8d'],
                'fax' => $c['269f2c19aab61c8daa0d61699277b82c37a395ce'] . strlen($c['721cd66763123b5273dd22c0b3d455fc66fca7ed']) > 0 ? ', ' . $c['721cd66763123b5273dd22c0b3d455fc66fca7ed'] : '',
                'email' => $c['09a7583fcb843a55ef2830d5ee06b2a88edd9623'] . strlen($c['47f4a75a942ecd38279080869573cc7d315b6b9e']) > 0 ? ', ' . $c['47f4a75a942ecd38279080869573cc7d315b6b9e'] : '',
                'website' => $c['b553abbde7758c4d1bc07731fb21dfdfe1d81c4a'],
                'facebook_link' => $c['9da0d794d349a1c4218ea1588093c4b1843d06cc'],
                'instagram_link' => $c['28819bab1cc9066de228c6b2d3cae695fb96aa03'],
                'x_link' => $c['41c8abbe9d4ea4c933f22d31ad49ed045bb613d6'],
                'stand_name' => $c['label'],
                'logo' => $c['picture_id'],
            ]);
            $people = $this->fetchPeopleForCompany($local->pipe_id);
            foreach ($people as $person) {
                $client = $local->clients()->create([
                    'pipe_id' => $person['id'],
                    'name' => $person['name'],
                    'mobile' => $person['phone'][0]['value'] ?? null,
                    'phone' => $person['phone'][1]['value'] ?? null,
                    'email' => $person['email'][0]['value'] ?? null,
                    'position' => $person['job_title'] ?? null,
                ]);
            }
        }
        $this->searchTerm = $local->name;
        $this->selectedCompany = $local->id;
        $this->coordinators = $local->Clients;
        $this->contactPersons = $local->Clients;
    }


    public function fetchPeopleForCompany($companyId)
    {
        $response = Http::get("https://hospitalityservices.pipedrive.com/api/v1/organizations/{$companyId}/persons", [
            'api_token' => $this->apiToken,
        ]);
        $people = $response->json('data') ?? [];
        return $people;
        // $this->coordinators = $people;
        // $this->contactPersons = $people;

    }
    public function fetchCompanyDetails($companyId)
    {
        $response = Http::get("https://hospitalityservices.pipedrive.com/api/v1/organizations/{$companyId}", [
            'api_token' => $this->apiToken,
        ]);
        $company = $response->json('data') ?? [];
        return $company;
        // $this->coordinators = $people;
        // $this->contactPersons = $people;

    }

    public function render()
    {
        return view('livewire.client-select');
    }
}
