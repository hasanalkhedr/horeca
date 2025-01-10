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

    /*
     * Initialize step fields
     */
    //public $bank_account;
    public $payment_method = '';
     public function mount()
    {
        $event = $this->model;
        $this->payment_method = $event->payment_method;

      //  $this->bank_account = $event->BankAccount;
//        $this->bank_account = BankAccount::findOrNew($this->model->bank_account_id);
        $this->mergeState([
            'vat_rate' => $this->model->vat_rate,
        //    'bank_account' => $this->bank_account? $this->bank_account->toArray() : [],
            'payment_method' => $this->payment_method,
            // 'bank_account_id' => $this->model->bank_account_id,
            // 'bank_account_name' => $this->bank_account->name ?? null,
            // 'IBAN' => $this->bank_account->IBAN ?? null,
            // 'swift_code' => $this->bank_account->swift_code ?? null,
            // 'account_name' => $this->bank_account->account_name ?? null,
            // 'event_id' => $this->bank_account->event_id ?? null,
        ]);
    }


    /*
    * Step icon
    */
    public function icon(): string
    {
        return 'check';
    }

    /*
     * When Wizard Form has submitted
     */
    public function save($state)
    {
        $event = $this->model;
        $event->vat_rate = $state['vat_rate'];
        //$event->payment_method = $state['payment_method'];
        $event->save();
       /* if($event->BankAccount) {
            $event->BankAccount->update($state['bank_account']);
            $this->bank_account = $event->BankAccount;
        } else {
            $this->bank_account = BankAccount::create(array_merge( $state['bank_account'], ['event_id'=>$event->id]));
        }*/
        // if(array_key_exists('id',$state['bank_account'])) {
        //     $this->bank_account = BankAccount::find($state['bank_account']['id'])->first();
        //     $this->bank_account->update($state['bank_account']);
        // } else {
        //    $this->bank_account = BankAccount::create(array_merge( $state['bank_account'], ['event_id'=>$event->id]));
        // }
        /*$payment_rates = array_map(function ($p) {
            return new PaymentRate((array)$p);
        }, json_decode($state['payment_rates'] ?? '[]', true));
        $a = array_diff($event->PaymentRates->pluck('id')->toArray(), array_column($payment_rates, 'id'));
        foreach ($a as $p) {
            PaymentRate::find($p)->delete();
        }
        foreach ($payment_rates as $payment_rate) {
            //$payment_rate->updateOrCreate();
            $payment_rate->event_id = $event->id;
            PaymentRate::updateOrCreate($payment_rate->toArray());
            //$payment_rate->save();
        }*/
        $currencies = array_map(function ($c) {
            return new Currency((array)$c);
        }, json_decode($state['currencies'] ?? '[]', true))  ;
        $b = array_diff($event->Currencies->pluck('id')->toArray(), array_column($currencies, 'id'));
        foreach ($b as $c) {
            $event->Currencies()->detach($c);
        }
        foreach ($currencies as $currency) {
            $event->Currencies()->syncWithoutDetaching($currency);
        }
        $event->save();
    }

    /*
     * Step Validation
     */
    public function validate()
    {
        return [
            [
                /*'state.bank_account.name' => ['required'],
                'state.bank_account.IBAN' => ['required'],
                'state.bank_account.swift_code' => ['required'],
                'state.bank_account.account_name' => ['required'],*/
//'state.payment_method' =>['required'],
            //'state.bank_account_id' => ['required', Rule::unique('events', 'bank_account_id')->ignoreModel($this->model), Rule::exists('bank_accounts', 'id')],
            'state.vat_rate' => ['required', 'numeric']
            ],
            [],
            [
                /*'state.bank_account.name' => __('Bank Account Name'),
                'state.bank_account.IBAN' => __('Bank Account IBAN'),
                'state.bank_account.swift_code' => __('Bank Account Swift Code'),
                'state.bank_account.account_name' => __('Bank Account Name'),*/
//'state.payment_method' => __('Payment Method Text'),
                //'state.bank_account_id' => __('Bank Account'),
                'state.vat_rate' => __('VAT Rate')
            ]
        ];
    }

    /*
     * Step Title
     */
    public function title(): string
    {
        return __('Payment Info');
    }

}
