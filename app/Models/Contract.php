<?php

namespace App\Models;

use App\Models\Settings\Category;
use App\Models\Settings\Price;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Contract extends Model
{
    protected $wasRecentlyUpdated = false;
    protected static function booted()
    {
        static::creating(function (Contract $contract) {
            $contract->contract_no = Contract::generateContractNumber($contract);
        });
        static::created(function (Contract $contract) {
            // $contract->sponsor_amount = $contract->SponsorPackage ? $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first() ? $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first()->pivot->total_price : 0 : 0;
//$contract->special_design_amount = $contract->special_design_price * $contract->Stand->space;
           // $contract->sub_total_1 = $contract->space_amount + $contract->sponsor_amount + $contract->advertisment_amount +             $contract->special_design_amount + $contract->water_electricity_amount;
           // $contract->d_i_a = 0;// $contract->sub_total_1;
           // $contract->sub_total_2 = $contract->sub_total_1 - $contract->d_i_a;
           // $contract->vat_amount = $contract->sub_total_2 * $contract->Event->vat_rate / 100;
           // $contract->net_total = $contract->sub_total_2 + $contract->vat_amount;
// $contract->save();
        });
        static::updated(function (Contract $contract) {
            if (!$contract->wasRecentlyUpdated) {
                // $contract->sponsor_amount = $contract->SponsorPackage ?
                //     $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first() ?
                //     $contract->SponsorPackage->currencies->where('id', $contract->Report->Currency->id)->first()->pivot->total_price : 0 : 0;
//                 $contract->special_design_amount = $contract->special_design_price * $contract->Stand->space;
                // $contract->sub_total_1 = $contract->space_amount + $contract->sponsor_amount +
                //     $contract->advertisment_amount + $contract->special_design_amount +
                //     $contract->water_electricity_amount;
                // $contract->d_i_a = 0;//$contract->sub_total_1;
                // $contract->sub_total_2 = $contract->sub_total_1 - $contract->d_i_a;
                // $contract->vat_amount = $contract->sub_total_2 * $contract->Event->vat_rate / 100;
                // $contract->net_total = $contract->sub_total_2 + $contract->vat_amount;
                // $contract->wasRecentlyUpdated = true;
 //               $contract->save();
            }
        });
        static::deleting(function ($document) {
            // Check if file exists and delete it
            if ($document->path && Storage::exists($document->path)) {
                Storage::delete($document->path);
            }
        });
    }
    public static function generateContractNumber(Contract $contract)
    {
        $latestContract = Contract::where([['event_id', '=', $contract->event_id], /*['contract_type_id', '=', $contract->contract_type_id]*/])->orderBy('contract_no', 'desc')->first();
        if ($latestContract) {
            $lastContractNumber = intval(substr($latestContract->contract_no, -3));
            $newContractNumber = $lastContractNumber + 1;
        } else {
            $newContractNumber = 1;
        }
        return 'CR-' . $contract->Event->CODE . '-' . str_pad($newContractNumber, 3, '0', STR_PAD_LEFT);
    }
    protected $fillable = [
        'contract_no',
        'company_id',
        'contract_date',
        'stand_id',
        'price_id',
        'event_id',
        'space_amount',
        'sponsor_amount',
        'advertisment_amount',
        //'total_amount',
        'status',
        'path',
        'price_amount',
        'report_id',
        'contact_person',
        'exhabition_coordinator',
        'special_design_text',
        'special_design_price',
        'special_design_amount',
        'if_water',
        'if_electricity',
        'electricity_text',
        'water_electricity_amount',
        'new_product',
        'sponsor_package_id',
        'specify_text',
        'notes1',
        'notes2',
        'sub_total_1',
        'd_i_a',
        'sub_total_2',
        'vat_amount',
        'net_total',
        'category_id',
        'seller',
        'ads_check',
        'space_discount',
        'space_net',
        'sponsor_discount',
        'sponsor_net',
        'ads_discount',
        'ads_net',
        'eff_ads_check',
        'eff_ads_amount',
        'eff_ads_discount',
        'eff_ads_net',
    ];
    protected $casts = [
        'contrat_date' => 'date',
        'ads_check' => 'array',
        'eff_ads_check' => 'array',
    ];
    public function Company()
    {
        return $this->belongsTo(Company::class);
    }
    public function Event()
    {
        return $this->belongsTo(Event::class);
    }
    public function Price()
    {
        return $this->belongsTo(Price::class);
    }
    public function Stand()
    {
        return $this->belongsTo(Stand::class);
    }
    public function Report()
    {
        return $this->belongsTo(Report::class);
    }

    public function ContactPerson()
    {
        return $this->belongsTo(Client::class, 'contact_person');
    }
    public function ExhabitionCoordinator()
    {
        return $this->belongsTo(Client::class, 'exhabition_coordinator');
    }
    public function SponsorPackage()
    {
        return $this->belongsTo(SponsorPackage::class);
    }
    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
    public function Seller()
    {
        return $this->belongsTo(User::class);
    }
    public function AdsPackage()
    {
        return $this->belongsTo(AdsPackage::class);
    }
    public function EffAdsPackage()
    {
        return $this->belongsTo(EffAdsPackage::class);
    }
}
