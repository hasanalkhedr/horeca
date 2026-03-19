<?php

namespace App\Livewire;

use App\Models\Report;
use Livewire\Component;

class FooterComponent extends Component
{
    public $title = '';
    public Report $report;

    public function mount($report = null) {
        if($report) {
            $this->report = $report;
        } else {
            $this->report = new Report([
                'name' => 'New Event',
                'organizer' => 'Hospitality Services s.a.r.l'
            ]);
        }
    }
    public function render()
    {
        return view('livewire.footer-component');
    }
}
