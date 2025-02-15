<div class="flex justify-between pt-[1px]">
    <div class="flex justify-end border border-black w-5/6 mr-6">
        <div class="w-1/2 items-center text-xs gap-2 pb-[2px]">
            <input type="checkbox" class="mr-2" @checked($contract->if_water)>
            <label class="font-bold">Water point needed (if available)</label>
        </div>
        <div class="w-1/2 items-center text-xs gap-2 pb-[2px]">
            <input type="checkbox" class="mr-2" @checked($contract->if_electricity)>
            <label class="font-bold">Extra electricity <strong> {{$contract->electricity_text}} Watt</strong></label>
            {{-- <input type="text" class="border-b border-gray-500 w-20"> --}}
        </div>
    </div>
    <div class="w-1/6">
        <div class="text-right font-bold border border-black pb-[2px]">
            <p>{{$contract->water_electricity_amount}} US$</p>
        </div>
    </div>
</div>
