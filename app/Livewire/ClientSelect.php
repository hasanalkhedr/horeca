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

    public function selectCompany($companyId)
    {
        $company = collect($this->companyResults)->firstWhere('item.id', $companyId)['item'] ?? null;
        if (!$company) {
            return;
        }

        // Save to DB if not already stored
        $local = Company::where('pipe_id', $companyId)->first();
        if (!$local) {
            $local = Company::create([
                'pipe_id' => $company['id'], // external ID as primary key
                'name' => $company['name'],
                'CODE' => 'NOT_SET',
                // 'street' => $company['address'],
                // 'country' => $company['country_code'],
                // Add other fields if needed (e.g. address, label, etc.)
            ]);
            $people = $this->fetchPeopleForCompany($local->pipe_id);
            foreach ($people as $person) {
                $client = $local->clients()->create([
                    'pipe_id'=> $person['id'],
                    'name'=> $person['name'],
                    'mobile' => $person['phone'][0]['value'] ?? null,
                    'phone' => $person['phone'][1]['value'] ?? null,
                    'email' => $person['email'][0]['value'] ?? null,
                    'position' => $person['job_title'] ?? null,
                ]);
            }
        }
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

    public function render()
    {
        return view('livewire.client-select');
    }
}
