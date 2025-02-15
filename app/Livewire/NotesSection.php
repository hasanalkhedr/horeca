<?php

namespace App\Livewire;

use App\Models\Contract;
use Livewire\Component;

class NotesSection extends Component
{
    public Contract $contract;
    public function mount($contract = null)
    {
        if($contract) {
            $this->contract = $contract;
        } else {
            $this->contract = new Contract([

            ]);
        }
    }
    public function render()
    {
        return view('livewire.notes-section');
    }
}
