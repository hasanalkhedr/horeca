<?php

namespace App\Livewire;


use App\Models\Settings\Category;
use App\Models\Settings\Price;
use Vildanbina\LivewireWizard\Components\Step;

class ThirdStep extends Step
{
    protected string $view = 'livewire.third-step';

    /*
     * Initialize step fields
     */
    public function mount()
    {
        $event = $this->model;
        //$this->categories = Category::all();
        $this->mergeState([
            'all_categories' => Category::all()->toArray(),
            //'prices' => $event->Prices ?? [],
        ]);

        $this->mergeState([
            'prices' => json_encode($event->Prices->map(function ($price) {

                return [
                    'id' => $price->id,
                    'name' => $price->name,
                    'amount' => $price->amount,
                    'currency_id' => $price->currency_id,
                    'currency_code' => $price->Currency->CODE ?? null,
                    'event_id' => $price->event_id ?? 0,
                    'description' =>$price->description
                ];
            })->toArray()),

            //'prices' => json_encode(array_merge($event->Prices->toArray(),['currency_code'=>]),
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
        $prices = json_decode($state['prices'] ?? '[]');
        $event = $this->model;
        foreach ($prices as $price) {
            $price->event_id = $event->id;
            if ($price->id == 0) {
                $event->Prices()->create([
                    'name' => $price->name,
                    'currency_id'=>$price->currency_id,
                    //'event_id' => $price->event_id,
                    'amount' => $price->amount,
                    'description' => $price->description
                ]);
            } else {
                $p = Price::find($price->id);
                if ($p) {
                    $p->update([
                        'name' => $price->name,
                        'currency_id'=>$price->currency_id,
                        'event_id' => $price->event_id,
                        'amount' => $price->amount,
                        'description' => $price->description
                    ]);
                }
            }
        }
        redirect(route('events.index'));
    }
    /*
     * Step Validation
     */
    public function validate()
    {
        return [
        ];
    }
    /*
     * Step Title
     */
    public function title(): string
    {
        return __('Pricing Details');
    }

}
