<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\Report;
use App\Models\Settings\Currency;
use Livewire\Component;

class PaymentSection extends Component
{
    public Contract $contract;
    public string $paymentMethod;
    public string $bankAccount;
    public string $bankNameAddress;
    public string $swiftCode;
    public string $iban;
    public Currency $currency;
    public function mount($contract = null,$paymentMethod = null, $bankAccount = null, $bankNameAddress = null,
    $swiftCode=null, $iban=null, $currency = null) {
        if($contract) {
            $this->contract = $contract;
        } else {
            $c = new Contract([]);
            $this->contract = $c;
        }

        $this->paymentMethod = $paymentMethod;
        $this->bankAccount = $bankAccount;
        $this->bankNameAddress = $bankNameAddress;
        $this->swiftCode = $swiftCode;
        $this->iban = $iban;
        $this->currency = $currency;
    }
    public function render()
    {
        return view('livewire.payment-section');
    }
}
