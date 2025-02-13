<?php

namespace App\Livewire;

use Livewire\Component;

class FooterComponent extends Component
{
    public $title = '';
    public function render()
    {
        return view('livewire.footer-component');
    }
}
