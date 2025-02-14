<div class="max-w-4xl mx-auto p-0 bg-white pt-[2px]">
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
        <div class="w-5/6 items-center gap-2 mb-[2px] mr-6 border border-black">
            <label class="pr-10 font-semibold">Stand N° <strong>{{ $contract->Stand->no }}</strong></label>
            <label class="ml-10 font-semibold">Space <strong>{{ $contract->Stand->space }} m²</strong></label>
        </div>
        <div class="w-1/6">
            <div class="text-right font-bold border border-black  mb-[2px] pb-[5px]">
                <p>{{ $contract->space_amount }} US$</p>
            </div>
        </div>
    </div>
    @foreach ($contract->Event->Prices as $price)
        <div class="flex justify-between">
            <div class="w-5/6 items-center text-xs gap-2 mb-[2px]">
                <input type="checkbox" class="mr-2" @checked($price->id == $contract->price_id)>
                <label class="font-bold">{{ $price->name }}</label>
                <span class="text-[8px] ml-1">{{$price->description}}</span>
                {{-- <span class="text-[8px] ml-1">(includes carpet, wall panels signboard, stand number, power point and
                lighting)</span> --}}
                {{ $contract->Stand->space }} m² x {{ $price->amount }} US$ / m²
            </div>
            <div class="w-1/6">

            </div>
        </div>
    @endforeach
    <div class="flex justify-between">
        <div class="w-5/6 items-center text-xs gap-2 mb-[2px]">
            <input type="checkbox" class="mr-2" @checked($contract->price_id == 0)>
            <label class="font-bold pr-4">Special pavilion, specify:</label>
            @if ($contract->price_id == 0)
                {{ $contract->Stand->space }} m² x {{ $contract->price_amount }} US$
            @endif
        </div>
        <div class="w-1/6">

        </div>
    </div>
    <div class="flex justify-between">
        <div class="w-5/6 text-sm items-center gap-2 mb-[2px] mr-6 border border-black">
            <strong>Special design option</strong> (includes wooden platforms, carpet, wood white panel walls, lighting
            and one counter with high stool)
            <input type="text" class="border-b border-gray-500 ml-20 w-8"> m² x 100 US$ / m²
        </div>
        <div class="w-1/6">
            <div class="text-right font-bold border border-black pl-16 mb-[2px] pb-[5px]">
                <p>US$</p>
            </div>
        </div>
    </div>
</div>
