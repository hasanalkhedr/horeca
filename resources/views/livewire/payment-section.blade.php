<div class="pt-[2px]">
    <div class="flex justify-between">
        <div class="flex justify-start  text-sm  pt-[1px] w-2/3 mr-1">
            {{-- <p class="">PAYMENT METHOD: <span class="font-bold">50% upon signature-50% on/or before 8 March 2025</span></p> --}}
            <p class="">PAYMENT METHOD: <span class="font-bold">{{$paymentMethod}}</span></p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">SUB TOTAL 1</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px] ">
                <p>{{$contract->sub_total_1}} {{$currency->CODE}}</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="flex justify-start pt-[1px] w-2/3 mr-1 text-[10px]">
            <p>
                No application will be considered binding upon the Organizer unless accompanied by the appropriate payment. All payments have to be made by cash or bank transfer to the order of Hospitality Services s.a.r.l.
            </p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">D.I.A.</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->d_i_a}} {{$currency->CODE}}</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="flex justify-start pt-[1px] w-2/3 mr-1 text-[10px]">
            {{-- <p>
                A/C No. <span class="font-bold">15911338015840</span> - BANQUE LIBANO-FRANCAISE S.A.L. - Sin-El Fil - Lebanon Swift Code: <span class="font-bold">BLFSLBBX</span>
                IBAN: <span class="font-bold">LB 81001000000015911338015840</span>. Please allow an extra USD 25 for each bank transfer.
            </p> --}}
            <p>
                A/C No. <span class="font-bold">{{$bankAccount}}</span> - {{$bankNameAddress}} Swift Code: <span class="font-bold">{{$swiftCode}}</span>
                IBAN: <span class="font-bold">{{$iban}}</span>. Please allow an extra USD 25 for each bank transfer.
            </p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">SUB TOTAL 2</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->sub_total_2}} {{$currency->CODE}}</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="flex justify-start pt-[1px] w-2/3 mr-1 text-[10px]">
            <p>By signing the present application, we irrevocably undertake to pay the amount due as indicated above and formally agree to abide by the terms and conditions overleaf.</p>
        </div>
        @if($contract->vat_amount >0)
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">+{{$vat}}% VAT</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->vat_amount}} {{$currency->CODE}}</p>
            </div>
        </div>
        @endif
    </div>
    <div class="flex justify-between">
        <div class="flex justify-start pt-[1px] w-2/3 mr-1 text-[10px]">
            <p>(This application must be signed by a person duly authorized.)</p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">TOTAL</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->net_total}} {{$currency->CODE}}</p>
            </div>
        </div>
    </div>
</div>
