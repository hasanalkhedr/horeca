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
            'all_packages' => json_encode($av->toArray() ?? []),
            'event_packages' => json_encode($event->SponsorPackages()->with('SponsorOptions')->get()->toArray() ?? []),
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
        $event_packages = array_map(function ($p) {
            return new SponsorPackage((array) $p);
        }, json_decode($state['event_packages'], true));
        $event = $this->model;
        $event->SponsorPackages()->sync(array_map(function ($p) {
            return $p->id; }, $event_packages));
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
