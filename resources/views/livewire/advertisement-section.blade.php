<div class="max-w-4xl w-full p-0 pt-[2px] bg-white">
    <div class="flex justify-between">
        <div class="w-5/6 items-center gap-2 mb-[2px] mr-6 border border-black">
            <label class="font-semibold">Advertisement in Hospitality News <span
                    class="font-bold underline"></span></label>
        </div>
        <div class="w-1/6">
            <div class="text-right font-bold border border-black pb-[2px] mb-[2px]">
                <p>{{ $contract->advertisment_amount }} {{ $currency->CODE }}</p>
            </div>
        </div>
    </div>
    @foreach ($event->AdsPackages as $package)
        <div class="flex justify-between">
            <div class="w-full font-bold text-lg leading-none">
                <h1>{{ $package->title }}</h1>
            </div>
        </div>
        <table class="w-full">
            @foreach ($package->AdsOptions as $option)
                @if ($loop->even)
                    @continue
                @endif
                <tr class="text-xs">
                    <td class="w-2/6">
                        <input type="checkbox" @checked(in_array($package->id.'_'.$option->id,$contract->ads_check ?? []) )
                            class="mr-1">{{ $option->title }}</td>
                    <td class="w-1/6">{{ $option->Currencies->where('id', $currency->id)->first() ?
                        $option->Currencies->where('id', $currency->id)->first()->pivot->price : 0}}
                        {{ $currency->CODE }}</td>
                    @if (!$loop->last)
                        <td class="w-2/6">
                            <input type="checkbox" @checked(in_array($package->id.'_'.$package->AdsOptions[$loop->index + 1]->id,$contract->ads_check ?? []))
                                class="mr-1">{{ $package->AdsOptions[$loop->index + 1]->title }}</td>
                        <td class="w-1/6">
                            {{ $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $currency->id)->first() ?
                            $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $currency->id)->first()->pivot->price : 0 }}
                            {{ $currency->CODE }}</td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endforeach
    {{-- <div class="flex justify-between">
        <div class="w-full font-bold text-lg leading-none">
            <h1>HORECA issue</h1>
        </div>
    </div>
    <table class="w-full">
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">Double page with ribbon</td>
            <td class="w-1/6">6,000 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Inside back cover</td>
            <td class="w-1/6">3,200 US$</td>
        </tr>
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">1st double</td>
            <td class="w-1/6">6,000 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Full page ad - HORECA special rate</td>
            <td class="w-1/6">1,299 US$</td>
        </tr>
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">Double page spread</td>
            <td class="w-1/6">4,500 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Feature full page - advertorial</td>
            <td class="w-1/6">1,750 US$</td>
        </tr>
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">Flyer</td>
            <td class="w-1/6">3,500-5,000 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Half page ad - Quarter page ad</td>
            <td class="w-1/6">1,575-1,090 US$</td>
        </tr>
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">Hard bound page</td>
            <td class="w-1/6">3,500 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Insert</td>
            <td class="w-1/6">895 US$</td>
        </tr>
    </table>
    <div class="flex justify-between">
        <div class="w-full font-bold text-lg leading-none">
            <h1>Online@hospitalitynewsmag.com</h1>
        </div>
    </div>
    <table class="w-full">
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">Bronze bundle</td>
            <td class="w-1/6">1,950 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Gold bundle</td>
            <td class="w-1/6">5,500 US$</td>
        </tr>
        <tr class="text-xs">
            <td class="w-2/6"><input type="checkbox" class="mr-1">Silver bundle</td>
            <td class="w-1/6">3,000 US$</td>
            <td class="w-2/6"><input type="checkbox" class="mr-1">Platinum bundle</td>
            <td class="w-1/6">9,500 US$</td>
        </tr>
    </table> --}}
</div>
