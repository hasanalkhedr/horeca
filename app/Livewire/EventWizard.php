<?php

namespace App\Livewire;

use App\Models\AdsPackage;
use App\Models\Event;
use App\Models\Settings\Category;
use App\Models\Settings\Currency;
use App\Models\Settings\Price;
use App\Models\SponsorPackage;
use Vildanbina\LivewireWizard\WizardComponent;
use App\Models\User;

class EventWizard extends WizardComponent
{
    public $eventId;
    public $currencies;
    public $currency_id = 0;
    public $all_currencies;
    public $categories = [];

    public array $steps = [
        General::class,
        SecondStep::class,
        ThirdStep::class,
        FourthStep::class,
    ];

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
    public function toogleCategory($category, $isChecked)
    {
        ///$event = $this->model();
        $this->categories = $this->state['categories'];
        $cats = array_map(function ($c) {
            return new Category((array) $c);
        }, json_decode($this->categories, true));
        if ($isChecked) {
            /// $event->Categories()->syncWithoutDetaching($category['id']);
            // $this->categories[] = ($category);
            array_push($cats, Category::find($category['id']));
        } else {
            $filtered_cs = collect($cats)->reject(fn($c) => $c->name === $category['name']);

            $cats = $filtered_cs->toArray();
            //$this->categories = array_diff($this->categories,[$category]);
            /// $event->Categories()->detach($category['id']);
            //$this->categories = array_diff($this->categories, [$id]);
        }
        //dd($this->categories);
        $this->mergeState([
            'categories' => json_encode($cats),
        ]);
        //dd($cats);
    }

    public function addPrice($price)
    {
        $price['currency_code'] = Currency::find($price['currency_id'])->CODE;
        $state = $this->getState();
        $prices = json_decode($state['prices'] ?? '[]');
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
            'currency_id' => 0,
            'currency_code' => '',
            'event_id' => 0,
            'description' => ''
        ];
    }

    public $tempPrice;
    public function editPrice($price)
    {
        $this->price = $price;
        $this->tempPrice = $price;
    }
    public function updatePrice($price)
    {
        // dd($this->tempPrice, $price);
        $price['currency_code'] = Currency::find($price['currency_id'])->CODE;
        $state = $this->getState();
        $prices = json_decode($state['prices'] ?? '[]');
        $p = collect($prices)->reject(fn($p) => $p->id == $this->tempPrice['id'] && $p->amount == $this->tempPrice['amount']);

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
            'currency_id' => 0,
            'currency_code' => '',
            'event_id' => 0,
            'description' => ''
        ];
    }

    public function deletePrice($price)
    {
        if ($price['id'] > 0) {
            $p = Price::find($price['id']);
            $p->delete();
        }
        $state = $this->getState();
        $prices = json_decode($state['prices'] ?? '[]');
        $p = collect($prices)->reject(fn($p) => $p->id == $price['id'] && $p->amount == $price['amount']);
        $prices = $p->values()->toArray();
        $this->mergeState([
            //'prices' => json_encode($prices),
            'prices' => json_encode($prices),
        ]);
        $this->price = [
            'id' => 0,
            'name' => '',
            'amount' => 0,
            'currency_id' => 0,
            'currency_code' => '',
            'event_id' => 0,
            'description' => ''
        ];
    }

    public $all_packages = [];
    public $event_packages = [];
    public function addPackageToEvent($package)
    {
        // $event = $this->model();
        // $event->SponsorPackages()->syncWithoutDetaching($package['id']);
        // $ev_pa = $event->SponsorPackages()->pluck('id')->toArray();
        // $av = SponsorPackage::all()->whereNotIn('id', $ev_pa)->toArray();
        // $this->mergeState([
        //     'all_packages' => $av,
        //     'event_packages' => $event->SponsorPackages->toArray()
        // ]);
        ///$event = $this->model();
        $this->all_packages = array_map(function ($p) {
            return new SponsorPackage((array) $p);
        }, json_decode($this->state['all_packages'], true));
        $this->event_packages = array_map(function ($p) {
            return new SponsorPackage((array) $p);
        }, json_decode($this->state['event_packages'], true));
        // dd($this->all_packages, $this->event_packages);
        array_push($this->event_packages, $package);
        $this->all_packages = collect($this->all_packages)->reject(fn($p) => $p->id === $package['id']);
        $this->mergeState([
            'all_packages' => json_encode($this->all_packages),
            'event_packages' => json_encode($this->event_packages),
        ]);
    }
    public function removePackageFromEvent($package)
    {
        // $event = $this->model();
        // $event->SponsorPackages()->detach($package['id']);
        // $ev_pa = $event->SponsorPackages()->pluck('id')->toArray();
        // $av = SponsorPackage::all()->whereNotIn('id', $ev_pa)->toArray();
        // $this->mergeState([
        //     'all_packages' => $av,
        //     'event_packages' => $event->SponsorPackages->toArray()
        // ]);
        $this->all_packages = array_map(function ($p) {
            return new SponsorPackage((array) $p);
        }, json_decode($this->state['all_packages'], true));
        $this->event_packages = array_map(function ($p) {
            return new SponsorPackage((array) $p);
        }, json_decode($this->state['event_packages'], true));
        // dd($this->all_packages, $this->event_packages);
        array_push($this->all_packages, $package);
        $this->event_packages = collect($this->event_packages)->reject(fn($p) => $p->id === $package['id']);
        $this->mergeState([
            'all_packages' => json_encode($this->all_packages),
            'event_packages' => json_encode($this->event_packages),
        ]);
    }


    public $all_ads_packages = [];
    public $event_ads_packages = [];
    public function addAdsPackageToEvent($package)
    {
        $this->all_ads_packages = array_map(function ($p) {
            return new AdsPackage((array) $p);
        }, json_decode($this->state['all_ads_packages'], true));

        $this->event_ads_packages = array_map(function ($p) {
            return new AdsPackage((array) $p);
        }, json_decode($this->state['event_ads_packages'], true));

        array_push($this->event_ads_packages, $package);

        $this->all_ads_packages = collect($this->all_ads_packages)
            ->reject(fn($p) => $p->id === $package['id'])->toArray();

        $this->mergeState([
            'all_ads_packages' => json_encode($this->all_ads_packages),
            'event_ads_packages' => json_encode($this->event_ads_packages),
        ]);
    }
    public function removeAdsPackageFromEvent($package)
    {
        $this->all_ads_packages = array_map(function ($p) {
            return new AdsPackage((array) $p);
        }, json_decode($this->state['all_ads_packages'], true));
        $this->event_ads_packages = array_map(function ($p) {
            return new AdsPackage((array) $p);
        }, json_decode($this->state['event_ads_packages'], true));
        array_push($this->all_ads_packages, $package);
        $this->event_ads_packages = collect($this->event_ads_packages)->reject(fn($p) => $p->id === $package['id']);
        $this->mergeState([
            'all_ads_packages' => json_encode($this->all_ads_packages),
            'event_ads_packages' => json_encode($this->event_ads_packages),
        ]);
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
        $this->categories = json_encode($event->Categories->toArray() ?? []);
        //()->get(['id'])->pluck('id')
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
            'description' => ''
        ];

        //dd($this->price);
        return $event;
    }
}
