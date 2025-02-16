<div class="pt-[2px]">
    <div class="flex justify-between">
        <div class="flex justify-start  text-sm  pt-[1px] w-2/3 mr-1">
            <p class="">PAYMENT METHOD: <span class="font-bold">50% upon signature-50% on/or before 8 March 2025</span></p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">SUB TOTAL 1</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px] ">
                <p>{{$contract->sub_total_1}} US$</p>
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
                <p>{{$contract->d_i_a}} US$</p>
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
                A/C No. <span class="font-bold">{{$contract->Report ? $contract->Report->bank_account : $report->bank_account}}</span> - BANQUE LIBANO-FRANCAISE S.A.L. - Sin-El Fil - Lebanon Swift Code: <span class="font-bold">BLFSLBBX</span>
                IBAN: <span class="font-bold">LB 81001000000015911338015840</span>. Please allow an extra USD 25 for each bank transfer.
            </p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">SUB TOTAL 2</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->sub_total_2}} US$</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="flex justify-start pt-[1px] w-2/3 mr-1 text-[10px]">
            <p>By signing the present application, we irrevocably undertake to pay the amount due as indicated above and formally agree to abide by the terms and conditions overleaf.</p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">+11% VAT</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->vat_amount}} US$</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="flex justify-start pt-[1px] w-2/3 mr-1 text-[10px]">
            <p>(This application must be signed by a person duly authorized.)</p>
        </div>
        <div class="w-1/3 flex justify-between">
            <div class="w-1/2 text-right font-bold">TOTAL</div>
            <div class="w-1/2 text-right font-bold border border-black mb-[2px]">
                <p>{{$contract->net_total}} US$</p>
            </div>
        </div>
    </div>
</div>
