<?php
namespace App\Livewire;

use App\Models\Contract;
use Livewire\Component;
use App\Models\Event;
class HeaderComponent extends Component
{

    public bool $with_logo;
    // protected $listeners = ['updateWithLogo']; // Listen for the event
    // public function updateWithLogo($value)
    // {
    //     $this->with_logo = $value; // Update the property
    // }
    public string $logo_path;
    public Contract $contract;
    // public $event_name = 'Event Name';
    // public $event_dates = 'Event Dates';
    // public $event_location = 'Event Location';
    // public $contract_no = '12345';

    // public function mount($name = 'event_name', $dates = 'event_dates placeholder', $location = 'event_location placeholder', $contract_no = 'CR-010235')
    // {
    //     $this->event_name = $name;
    //     $this->event_dates = $dates;
    //     $this->event_location = $location;
    //     $this->contract_no = $contract_no;
    // }
    public function mount($contract = null, $with_logo = null, $logo_path = null)
    {
        if ($contract) {
            $this->contract = $contract;
        } else {
            $e = new Event([
                'name' => 'EVENT NAME',
                'start_date' => '01/01/2025',
                'end_date' => '01/01/2025'
            ]);
            $this->contract = new Contract([

            ]);
            $this->contract->Event = $e;
        }
        $this->with_logo = $with_logo;
        $this->logo_path = $logo_path;
        // $this->contract = $contract ?? new Contract([
        //     'Event' => new Event([
        //         'name' => 'EVENT Name',
        //     ])
        // ]);
    }

    public function render()
    {
        return view('livewire.header-component');
    }
}
