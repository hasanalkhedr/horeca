<div class="pt-[2px]">
    <div class="flex justify-between">
        <div class="border border-black w-5/6 mr-6">
            <label class="font-bold">Sponsorship / Effective Advertising <span
                    class="underline">{{ $contract->SponsorPackage ? $contract->SponsorPackage->title : '' }}</span></label>
        </div>
        <div class="w-1/6">
            <div class="text-right font-bold border border-black mb-[2px]">
                <p>{{ $contract->sponsor_amount }} US$</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between text-xs font-bold">
        <div class="w-5/6 mr-6">
            <label class="font-semibold underline">Specify <span class="font-bold">{{ $contract->specify_text }}</span></label>
        </div>
        <div class="w-1/6">

        </div>
    </div>
</div>
