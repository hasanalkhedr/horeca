<?php

namespace App\Livewire;

use Livewire\Component;

class ToggleSwitch extends Component
{
    public $model;
    public $value;
    public $disabled = false;

    public function mount($model, $value, $disabled = false)
    {
        $this->model = $model;
        $this->value = $value;
        $this->disabled = $disabled;
    }

    public function updated($property)
    {
        if (!$this->disabled) {
            $this->emitUp('toggleUpdated', $this->value);
        }
    }

    public function render()
    {
        return view('livewire.toggle-switch');
    }
}
