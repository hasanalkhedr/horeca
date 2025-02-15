<?php

namespace App\Livewire;

use App\Http\Controllers\ContractController;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Settings\Category;
use Livewire\Component;

class CompanyDetailsComponent extends Component
{
    public Contract $contract;

    public $company = '';
    public $billing = '';
    public $mof_number = '';
    public $commercial_register = '';
    public $contact_person = '';
    public $mobile = '';
    public $exhibition_coordinator = '';
    public $exhibition_mobile = '';
    public $mailing_address = '';
    public $country = '';
    public $phone = '';
    public $email = '';
    public $facebook = '';
    public $instagram = '';
    public $twitter = '';
    public $website = '';
    public $selected_categories = [];
    public $event_categories = [
        'Bakery/Pastry',
        'Beverage',
        'Catering Equipment',
        'Coffee & Tea Pavilion',
        'Consultancy, Recruitment & Franchise',
        'Education',
        'Food',
        'Hygiene',
        'Interiors',
        'International Pavilion',
        'Packaging/Labeling',
        'Techzone',
    ];
    public function mount($contract = null) {
        if($contract) {
            $this->contract = $contract;
        } else {
            $e = new Event([
'name' => 'EVENT NAME',
                'start_date' => '01/01/2025',
                'end_date' => '01/01/2025'
            ]);
            $p = new Client([
                'name' => 'Contact Person',
            ]);
            $p1 = new Client([
                'name' => 'Exhabition Coordinator',
            ]);
            $c = new Company([
                'name' => 'Sample Company',
            ]);
            $this->contract = new Contract([

            ]);
            $this->contract->Event = $e;
            $this->contract->Company = $c;
            $this->contract->ContactPerson = $p;
            $this->contract->ExhabitionCoordinator = $p1;
        }
    }
    public function render()
    {
        return view('livewire.company-details-component');
    }
}
