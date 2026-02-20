<div class="max-w-4xl w-full p-0 bg-white pt-[2px]">
    <div class="flex justify-between">
        <div class="w-5/6">
            <h2 class="font-bold text-lg leading-none">2. APPLICATION: <span class="text-sm font-normal">We hereby apply to book:</span></h2>
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
                <label class="pr-10 font-semibold">Stand N°
                    <strong>{{ $contract->Stand ? $contract->Stand->no : '' }}</strong></label>
                <label class="ml-10 font-semibold">Space <strong>__________m * __________m</strong></label>
            </div>
            @foreach ($contract->Event->Prices()->whereHas('currencies', function ($query) use ($currency) {
                    $query->where('currencies.id', $currency->id);
                })->get() as $price)
                <div class="flex justify-between items-center my-1">
                    <div class="flex items-center text-xs gap-2">
                        <input disabled type="checkbox" class="mr-2" @checked($price->id == $contract->price_id)>
                        <label class="font-bold">{{ $price->name }}
                        </label>
                        <span class="text-[8px] ml-1">{{ $price->description }}</span>
                    </div>
                    @if ($price->id == $contract->price_id)
                        <div class="text-xs">
                            @if ($contract->free_space == 0)
                                {{ $contract->Stand->space }} m² x
                                {{ $contract->price_amount }} {{ $currency->CODE }} / m²
                            @else
                                {{ $contract->Stand->space - $contract->free_space }} + {{ $contract->free_space }} free
                                m² x
                                {{ $contract->price_amount }} {{ $currency->CODE }} / m²
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
            @if ($contract->enable_tax_per_sqm)
                <div class="text-right font-bold  mb-[2px] pb-[5px]">
                    <p>Tax per SQM: {{ $contract->tax_per_sqm_amount }} {{ $currency->CODE }}/SQM</p>
                </div>
            @endif
        </div>
        <div class="w-1/4">
            <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                <p>Total: {{ $contract->space_amount - $contract->tax_per_sqm_total }} {{ $currency->CODE }}</p>
            </div>
            @if ($contract->enable_tax_per_sqm)
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
</div>
