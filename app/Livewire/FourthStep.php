<?php

namespace App\Livewire;


use App\Models\Settings\Category;
use App\Models\Settings\Price;
use App\Models\SponsorPackage;
use Vildanbina\LivewireWizard\Components\Step;

class FourthStep extends Step
{
    protected string $view = 'livewire.fourth-step';

    /*
     * Initialize step fields
     */
    public function mount()
    {
        $event = $this->model;
        $ev_pa = $event->SponsorPackages()->pluck('id')->toArray();
        $av = SponsorPackage::with('SponsorOptions')->whereNotIn('id', $ev_pa)->get();
        $this->mergeState([
            'all_packages' => json_decode($av),
            'event_packages' => json_decode($event->SponsorPackages()->with('SponsorOptions')->get())
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
        return __('Sponsor&Advertisement');
    }

}
