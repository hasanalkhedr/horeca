<?php

namespace App\Livewire;

use App\Models\Settings\BankAccount;
use App\Models\Settings\Currency;
use App\Models\Settings\PaymentRate;
use Livewire\Component;
use Vildanbina\LivewireWizard\Components\Step;
use Illuminate\Validation\Rule;

class SecondStep extends Step
{
    protected string $view = 'livewire.second-step';

    public function mount(){}

    public function icon(): string
    {
        return 'check';
    }

    public function save($state)
    {
        $event = $this->model;
        $event->vat_rate = $state['vat_rate'];
        $event->save();
        $currencies = json_decode($state['currencies']);
        $ids_prices = collect($currencies)->map(function($c) {
            return [
                'currency_id' => $c->id,
                'min_price' => $c->min_price,
            ];
        })->toArray();
        $event->Currencies()->detach();
        $event->Currencies()->sync($ids_prices);
        $event->save();
    }

    public function validate()
    {
        return [
            [
            'state.vat_rate' => ['required', 'numeric']
            ],
            [],
            [
                'state.vat_rate' => __('VAT Rate')
            ]
        ];
    }

    public function title(): string
    {
        return __('Payment Info');
    }

}
