<div class="max-w-4xl w-full p-0 bg-white pt-[2px]">
    <div class="flex justify-between">
        <div class="w-5/6">
            <h2 class="font-bold text-lg leading-none">2. APPLICATION: <span class="text-sm font-normal">We hereby apply
                    to book:</span></h2>
        </div>
        <div class="w-1/6">
            <div class="text-center px-4 mb-[2px]">
                <p class="font-bold">Your Order</p>
            </div>
        </div>
    </div>
    <div class="flex justify-between">
        <div class="w-3/4 mr-6">
            <div class="w-full items-center gap-2 mb-[2px] mr-1 border border-black">
                <label class="pr-10 font-semibold">Stand N° <strong>{{ $contract->Stand->no }}</strong></label>
                {{-- <label class="ml-10 font-semibold">Space <strong>{{ $contract->Stand->space }} m²</strong></label> --}}
                <label class="ml-10 font-semibold">Space <strong>__________m * __________m</strong></label>
            </div>

            {{-- @foreach ($contract->Event->Prices->where('currency_id', $currency->id) as $price) --}}
            @foreach ($contract->Event->Prices()->whereHas('currencies', function ($query) use ($currency) {
            $query->where('currencies.id', $currency->id);
        })->get() as $price)
                <div class="flex justify-between items-center my-1">
                    <div class="flex items-center text-xs gap-2">
                        <input disabled type="checkbox" class="mr-2" @checked($price->id == $contract->price_id)>
                        <label class="font-bold">{{ $price->name }}
                            {{-- <span class="text-[9px]">(Min {{ $price->Currencies()->find($currency->id)->pivot->amount }} {{ $currency->CODE }} / m²)</span> --}}
                        </label>
                        <span class="text-[8px] ml-1">{{ $price->description }}</span>
                    </div>
                    @if($price->id == $contract->price_id)
                    <div class="text-xs">
                        @if($contract->free_space == 0)
                            {{ $contract->Stand->space }} m² x
                            {{ $contract->price_amount }} {{ $currency->CODE }} / m²
                        @else
                            {{ $contract->Stand->space - $contract->free_space }} + {{ $contract->free_space }} free m² x
                            {{ $contract->price_amount }} {{ $currency->CODE }} / m²
                        @endif
                    </div>
                    @endif
                </div>
            @endforeach
            {{-- @if ($special_price)
                <div class="flex justify-between items-center my-1">
                    <div class="flex items-center text-xs gap-2">
                        <input disabled type="checkbox" class="mr-2" @checked($contract->contract_no != 'temp' && $contract->price_id == 0)>
                        <label class="font-bold pr-4">Special pavilion, specify:</label>
                    </div>
                    <div class="text-xs">
                    @if ($contract->price_id == 0)
                        {{ $contract->Stand->space }} m² x {{ $contract->price_amount }} {{ $currency->CODE }}
                    @endif
                    </div>
                </div>
            @endif --}}
@if($contract->enable_tax_per_sqm)
                <div class="text-right font-bold  mb-[2px] pb-[5px]">
                    <p>Tax per SQM: {{ $contract->tax_per_sqm_amount }} {{ $currency->CODE }}/SQM</p>
                </div>
            @endif

        </div>

        <div class="w-1/4">
            <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                <p>Total: {{ $contract->space_amount - $contract->tax_per_sqm_total }} {{ $currency->CODE }}</p>
            </div>
            @if($contract->enable_tax_per_sqm)
                <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                    <p>Tax: {{ $contract->tax_per_sqm_total }} {{ $currency->CODE }}</p>
                </div>
            @endif
            @if ($contract->space_discount > 0)
                <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                    <p>Discount: {{ $contract->space_discount }} {{ $currency->CODE }}</p>
                </div>
                <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                    <p>Net: {{ $contract->space_net }} {{ $currency->CODE }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- @if ($special_price)
        <div class="flex justify-between">
            <div class="w-5/6 items-center text-xs gap-2 mb-[2px]">
                <input type="checkbox" class="mr-2" @checked($contract->price_id == 0)>
                <label class="font-bold pr-4">Special pavilion, specify:</label>
                @if ($contract->price_id == 0)
                    {{ $contract->Stand->space }} m² x {{ $contract->price_amount }} {{ $currency->CODE }}
                @endif
            </div>
            <div class="w-1/6">

            </div>
        </div>
        @if ($contract->special_design_amount > 0)
            <div class="flex justify-between">
                <div class="w-5/6 text-sm items-center gap-2 mb-[2px] mr-6 border border-black">
                    <strong>{{ $contract->special_design_text }}</strong>

                    {{ $contract->Stand->space }} m² x {{ $contract->special_design_price }} {{ $currency->CODE }} /
                    m²
                </div>
                <div class="w-1/6">
                    <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                        <p>{{ $contract->special_design_amount }} {{ $currency->CODE }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="flex justify-between">
                <div class="w-5/6 text-sm items-center gap-2 mb-[2px] mr-6 border border-black">
                    <strong>Special Design Option</strong> (includes wooden platforms, carpet, wood white panel walls,
                    lighting and one counter with high stool)
                    ______ m² x ______ {{ $currency->CODE }} / m²
                </div>
                <div class="w-1/6">
                    <div class="text-right font-bold border border-black pl-16 mb-[2px] pb-[5px]">
                        <p> {{ $currency->CODE }}</p>
                    </div>
                </div>
            </div>
        @endif
    @endif --}}
</div>
