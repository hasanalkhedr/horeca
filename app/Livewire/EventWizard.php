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

    public function relateCurrency($id, $min_price)
    {
        $av_cs = json_decode($this->state['avilable_currencies']);
        $cs = json_decode($this->state['currencies']);
        $cu = (array) (collect($av_cs)->firstWhere('id', $id));

        array_push($cs, array_merge($cu, ['min_price' => $min_price]));
        $av_cs = collect($av_cs)->reject(fn($c) => $c->id == $id)->toArray();

        $this->mergeState([
            'currencies' => json_encode($cs ?? []),
            'avilable_currencies' => json_encode(array_values($av_cs) ?? [])
        ]);
    }
    public function unrelateCurrency($currency)
    {
        $av_cs = json_decode($this->state['avilable_currencies']);
        $cs = json_decode($this->state['currencies']);

        array_push($av_cs, $currency);
        $cs = collect($cs)->reject(fn($c) => $c->id == $currency['id'])->toArray();

        $this->mergeState([
            'currencies' => json_encode(array_values($cs) ?? []),
            'avilable_currencies' => json_encode($av_cs ?? [])
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

    public $tempPrice;
    public function addPrice($price)
    {
        $prices = json_decode($this->state['prices']);
        $currencies = json_decode($this->state['currencies']);
        $price['index'] = count($prices);
        foreach ($price['currencies'] as $index => $curr) {
            $curr['currency_code'] = collect($currencies)->where('id', $curr['currency_id'])->first()->CODE;
            $price['currencies'][$index] = $curr;
        }
        array_push($prices, $price);
        $this->mergeState([
            'prices' => json_encode($prices),
        ]);
    }
    public function updatePrice($price)
    {
        $prices = json_decode($this->state['prices']);
        $currencies = json_decode($this->state['currencies']);
        foreach ($price['currencies'] as $index => $curr) {
            $curr['currency_code'] = collect($currencies)->where('id', $curr['currency_id'])->first()->CODE;
            $price['currencies'][$index] = $curr;
        }
        $prices = collect($prices)->map(function ($pr) use ($price) {
            return ((array) $pr)['index'] == $price['index'] ? $price : $pr;
        })->toArray();
        $this->mergeState([
            'prices' => json_encode($prices),
        ]);
    }

    public function deletePrice($price)
    {
        $prices = json_decode($this->state['prices']);
        $prices = collect($prices)->filter(function ($pr) use ($price) {
            return ((array) $pr)['index'] != $price['index'];
        })->toArray();
        $this->mergeState([
            'prices' => json_encode($prices),
        ]);
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
        $currencies = $event->Currencies()->get()->map(function ($currency) {
            return [
                'id' => $currency->id,
                'name' => $currency->name,
                'CODE' => $currency->CODE,
                'min_price' => $currency->pivot->min_price
            ];
        })->toArray();

        $availableCurrencies = Currency::select('id', 'name', 'CODE')->whereNotIn('id', array_column($currencies, 'id'))->get();

        $this->categories = json_encode($event->Categories->toArray() ?? []);

        $price_currencies = [];
        foreach ($event->Prices as $index => $price) {
            $temp_curr = [];
            foreach ($price->Currencies as $currency) {
                $temp_curr[] = [
                    'currency_id' => $currency->id,
                    'currency_code' => $currency->CODE,
                    'amount' => $currency->pivot->amount,
                ];
            }
            $price_currencies[] = [
                'index' => $index,
                'id' => $price->id,
                'name' => $price->name,
                'description' => $price->description,
                'currencies' => $temp_curr,
            ];
        }
        $this->mergeState([
            'categories' => $this->categories,
            'all_categories' => Category::all()->toArray(),
            'currencies' => json_encode($currencies ?? []),
            'avilable_currencies' => json_encode($availableCurrencies ?? []),
            'vat_rate' => $event->vat_rate,
            'prices' => json_encode($price_currencies),
        ]);

        return $event;
    }
}
