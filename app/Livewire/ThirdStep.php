<?php

namespace App\Livewire;


use App\Models\Settings\Category;
use App\Models\Settings\Price;
use Vildanbina\LivewireWizard\Components\Step;

class ThirdStep extends Step
{
    protected string $view = 'livewire.third-step';

    public function mount()
    {
    }
    public function icon(): string
    {
        return 'check';
    }
    public function save($state)
    {
        $event = $this->model;
        // categories
        $categories = array_map(function ($c) {
            return new Category((array) $c);
        }, json_decode($state['categories'], true));

        $cats = [];
        foreach ($categories as $c) {
            $cats[] = Category::where('name', 'like', $c['name'])->get('id')->toArray();
        }
        $ids = collect($cats)
            ->flatten(1) // Flatten the nested arrays by one level
            ->pluck('id') // Extract the 'id' values
            ->toArray(); // Convert the collection back to an array
        $event->Categories()->sync($ids);

        // prices
        $prices = json_decode($state['prices']);
        $pp = collect($prices)->map(function ($p) {
            return [
                'price' => new Price((array) $p),
                'currencies' => collect($p->currencies)->map(function($c) {
                                return [
                                    'currency_id' => $c->currency_id,
                                    'amount' => $c->amount
                                ];
                            })->toArray()
            ];
        })->toArray();

        foreach ($event->Prices as $p) {
            if(!in_array($p->id, array_column(array_column($pp,'price'),'id'))) {
                $p->delete();
            }
        }

        foreach ($pp as $p) {
            $t = $event->prices()->updateOrCreate($p['price']->toArray());
            $t->Currencies()->sync($p['currencies']);
        }

        $event->save();
        // dd($pp);
        // foreach ($prices as $price) {
        //     $price->event_id = $event->id;

        //     //if ($price->id == 0 || $price->id == "")  {
        //         // $p = $event->Prices()->create([
        //         //     'name' => $price->name,
        //         //     'description' => $price->description
        //         // ]);
        //         $ids_amounts = collect($price->currencies)->map(function($c) {
        //             return [
        //                 'currency_id' => $c->currency_id,
        //                 'amount' => $c->amount
        //             ];
        //         })->toArray();
        //         dd($price);
        //         $p = array_map(function($item)  {
        //             unset($item['currencies']);
        //             return $item;
        //         }, (array)$price);
        //         $ps = $event->Prices()->updateOrCreate($p);
        //         //$p['currencies'] = $ids_amounts;
        //         $ps->Currencies()->sync($ids_amounts);
        //     // } else {
        //     //     $p = Price::find($price->id);
        //     if ($p) {
        //         $p->update([
        //             'name' => $price->name,
        //             'currency_id' => $price->currency_id,
        //             'event_id' => $price->event_id,
        //             'amount' => $price->amount,
        //             'description' => $price->description
        //         ]);
        //     }
        //}
        //}
        // redirect(route('events.index'));
    }

    public function validate()
    {
        return [
        ];
    }
    public function title(): string
    {
        return __('Pricing Details');
    }

}
