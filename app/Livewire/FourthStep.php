<?php

namespace App\Livewire;

use App\Models\AdsPackage;
use App\Models\EffAdsPackage;
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

        $ev_ads_pa = $event->AdsPackages()->pluck('ads_packages.id')->toArray();
        $ads_av = AdsPackage::with('AdsOptions')->whereNotIn('id', $ev_ads_pa)->get();

        $ev_eff_ads_pa = $event->EffAdsPackages()->pluck('eff_ads_packages.id')->toArray();
        $eff_ads_av = EffAdsPackage::with('EffAdsOptions')->whereNotIn('id', $ev_eff_ads_pa)->get();

        $this->mergeState([
            'all_packages' => json_encode($av->toArray() ?? []),
            'event_packages' => json_encode($event->SponsorPackages()->with('SponsorOptions')->get()->toArray() ?? []),

            'all_ads_packages' => json_encode($ads_av->toArray() ?? []),
            'event_ads_packages' => json_encode($event->AdsPackages()->with('AdsOptions')->get()->toArray() ?? []),

            'all_eff_ads_packages' => json_encode($eff_ads_av->toArray() ?? []),
            'event_eff_ads_packages' => json_encode($event->EffAdsPackages()->with('EffAdsOptions')->get()->toArray() ?? []),
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

        $event_ads_packages = array_map(function ($p) {
            return new AdsPackage((array) $p);
        }, json_decode($state['event_ads_packages'], true));
        $event_eff_ads_packages = array_map(function ($p) {
            return new EffAdsPackage((array) $p);
        }, json_decode($state['event_eff_ads_packages'], true));
        $event = $this->model;
        $event->SponsorPackages()->sync(array_map(function ($p) {
            return $p->id;
        }, $event_packages));
        $event->AdsPackages()->sync(array_map(function ($p) {
            return $p->id;
        }, $event_ads_packages));
        $event->EffAdsPackages()->sync(array_map(function ($p) {
            return $p->id;
        }, $event_eff_ads_packages));
        redirect(route('events.index'));
    }
    /*
     * Step Validation
     */
    public function validate()
    {
        return [];
    }
    /*
     * Step Title
     */
    public function title(): string
    {
        return __('Sponsor&Advertisement');
    }
}
