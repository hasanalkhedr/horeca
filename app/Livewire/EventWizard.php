<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Settings\Currency;
use App\Models\Settings\PaymentRate;
use App\Models\Settings\Price;
use Vildanbina\LivewireWizard\WizardComponent;
use App\Models\User;

class EventWizard extends WizardComponent
{
    public $eventId;
    //public $payment_rates;
    public $currencies;
    public $currency_id = 0;
    public $all_currencies;
    public $categories = [];

    public array $steps = [
        General::class,
        SecondStep::class,
        ThirdStep::class,
    ];

    // public function addPayment()
    // {
    //     $ps = array_map(function ($p) {
    //         return new PaymentRate((array) $p);
    //     }, json_decode($this->payment_rates, true));
    //     //$ps = json_decode($this->payment_rates);
    //     $p = new PaymentRate([
    //         "id" => null,
    //         "title" => '',
    //         "rate" => 0,
    //         "order" => 0,
    //         "date_to_pay" => ''
    //     ]);
    //     array_push($ps, $p);
    //     $this->payment_rates = json_encode($ps);
    //     $this->mergeState([
    //         'payment_rates' => $this->payment_rates,
    //     ]);
    // }
    // public function editPayment($payment_rate)
    // {
    //     $ps = array_map(function ($p) {
    //         return new PaymentRate((array) $p);
    //     }, json_decode($this->payment_rates, true));
    //     $p = new PaymentRate($payment_rate);
    //     // if ($p->id) {
    //     //     $p->update($payment_rate);
    //     // } else {
    //     //     $p = $p->create($payment_rate);
    //     // }
    //     $filtered_ps = collect($ps)->reject(fn($p) => $p->id === $payment_rate['id']);
    //     $ps = $filtered_ps->toArray();
    //     array_push($ps, $p->toArray());
    //     $this->payment_rates = json_encode($ps);
    //     $this->mergeState([
    //         'payment_rates' => $this->payment_rates,
    //     ]);
    // }
    // public function deletePayment($id)
    // {
    //     $ps = array_map(function ($p) {
    //         return new PaymentRate((array) $p);
    //     }, json_decode($this->payment_rates, true));
    //     // if ($id) {
    //     //     PaymentRate::find($id)->first()->delete();
    //     // }
    //     $filtered_ps = collect($ps)->reject(fn($p) => $p->id === $id);
    //     //array_pop($ps);
    //     $this->payment_rates = json_encode($filtered_ps);
    //     $this->mergeState([
    //         'payment_rates' => $this->payment_rates,
    //     ]);
    // }

    public function relateCurrency($id)
    {
        $cs = array_map(function ($c) {
            return new Currency((array) $c);
        }, json_decode($this->currencies, true));
        //$cs = json_decode($this->currencies);
        $c = Currency::find($id);
        array_push($cs, $c);
        $this->currencies = json_encode($cs);
        $this->mergeState([
            'currencies' => $this->currencies,
        ]);
    }
    public function unrelateCurrency($currency)
    {
        $cs = json_decode($this->currencies);
        $filtered_cs = collect($cs)->reject(fn($c) => $c->id === $currency['id']);
        $cs = $filtered_cs->values()->toArray();
        $this->currencies = json_encode($cs);
        $this->mergeState([
            'currencies' => $this->currencies,
        ]);
    }
    public function toogleCategory($id, $isChecked)
    {
        $event = $this->model();
        if ($isChecked) {
            $event->Categories()->syncWithoutDetaching($id);
            $this->categories[] = ($id);
        } else {
            $event->Categories()->detach($id);
            array_diff($this->categories, [$id]);
        }
        $this->mergeState([
            'categories' => $this->categories
        ]);
    }

    public function addPrice($price)
    {
        $price['currency_code'] = Currency::find($price['currency_id'])->CODE;
        $state = $this->getState();
        $prices = json_decode($state['prices'] ?? '[]')  ;
        array_push($prices, $price);
        $this->mergeState([
            //'prices' => json_encode($prices),
            'prices' => json_encode($prices),
        ]);

        // if($price['id'] == 0) {
        //     $event->Prices()->create($price);
        // } else {
        //     $p = Price::find($price['id']);
        //     if($p) {
        //         $p->update($price);
        //     }
        // }

        // $this->mergeState([
        //     'prices' => Price::with('Currency')->where('event_id', $event->id)->get()->map(function ($price) {
        //         return [
        //             'id' => $price->id,
        //             'name' => $price->name,
        //             'amount' => $price->amount,
        //             'currency_id' => $price->currency_id,
        //             'event_id' => $price->event_id ?? 0,
        //             'currency_code' => $price->Currency->CODE ?? null,
        //         ];
        //     }),
        // ]);
        $this->price = [
            'id' => 0,
            'name' => '',
            'amount' => 0,
            'currency_id' =>0,
            'currency_code' => '',
            'event_id' => 0,
        ];
    }

    public $tempPrice;
    public function editPrice($price){
        $this->price = $price;
        $this->tempPrice = $price;
    }
    public function updatePrice($price){
        // dd($this->tempPrice, $price);
        $price['currency_code'] = Currency::find($price['currency_id'])->CODE;
        $state = $this->getState();
        $prices = json_decode($state['prices'] ?? '[]')  ;
        $p = collect($prices)->reject(fn($p)=>$p->id == $this->tempPrice['id'] && $p->amount == $this->tempPrice['amount']);

        $prices = $p->values()->toArray();
        array_push($prices, $price);
        $this->mergeState([
            //'prices' => json_encode($prices),
            'prices' => json_encode($prices),
        ]);
        $this->price = [
            'id' => 0,
            'name' => '',
            'amount' => 0,
            'currency_id' =>0,
            'currency_code' => '',
            'event_id' => 0,
        ];
    }

    public function deletePrice($price)
    {
        if($price['id']>0)
        {
            $p = Price::find($price['id']);
            $p->delete();
        }
        $state = $this->getState();
        $prices = json_decode($state['prices'] ?? '[]')  ;
        $p = collect($prices)->reject(fn($p)=>$p->id == $price['id'] && $p->amount == $price['amount']);
        $prices = $p->values()->toArray();
        $this->mergeState([
            //'prices' => json_encode($prices),
            'prices' => json_encode($prices),
        ]);
        $this->price = [
            'id' => 0,
            'name' => '',
            'amount' => 0,
            'currency_id' =>0,
            'currency_code' => '',
            'event_id' => 0,
        ];
    }
    public $price;
    public function model()
    {
        $event = Event::findOrNew($this->eventId);
        //$this->payment_rates = json_encode($event->PaymentRates()->get());
        $this->currencies = json_encode($event->Currencies()->get());
        $this->mergeState([
            'currencies' => $this->currencies,
            //  'payment_rates' => $this->payment_rates,
        ]);
        $this->all_currencies = json_encode(Currency::all());
        $this->categories = $event->Categories()->get(['id'])->pluck('id')->toArray();

        $this->mergeState([
            'categories' => $this->categories,
            // 'prices' => Price::with('Currency')->where('event_id', $event->id)->get()->map(function ($price)  {
            //     return [
            //         'id' => $price->id,
            //         'name' => $price->name,
            //         'amount' => $price->amount,
            //         'currency_id' => $price->currency_id,
            //         'currency_code' => $price->Currency->CODE ?? null,
            //         'event_id' => $price->event_id ?? 0,
            //     ];
            // }),
        ]);
        $this->price = [
            'id' => 0,
            'name' => '',
            'amount' => 0,
            'currency_id' => 0,
            'currency_code' => '',
            'event_id' => 0,
        ];
        //dd($this->price);
        return $event;
    }
}
