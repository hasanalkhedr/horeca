<div class="max-w-4xl w-full p-0 pt-[2px] bg-white">
    <div class="flex justify-between">
        <div class="w-3/4 items-center gap-2 mb-[2px] mr-6 ">
            <div class=" border border-black w-full pb-1">
            <label class="font-semibold">Effective Advertisement <span
                    class="font-bold underline"></span></label>
            </div>
            @foreach ($event->EffAdsPackages as $package)
                <div class="flex justify-between">
                    <div class="w-full font-bold text-lg leading-none">
                        <h1>{{ $package->title }}</h1>
                    </div>
                </div>
                <table class="w-full mr-0">
                    @foreach ($package->EffAdsOptions as $option)
                        @if ($loop->even)
                            @continue
                        @endif
                        <tr class="text-[8px]">
                            <td class="w-2/6 text-[8px]">
                                <input disabled type="checkbox" @checked(in_array($package->id . '_' . $option->id, $contract->eff_ads_check ?? [])) class="mr-1 h-3 text-[8px]">{{ $option->title }}
                            </td>
                            <td class="w-1/6 text-[8px]">
                                {{ $option->Currencies->where('id', $currency->id)->first()
                                    ? $option->Currencies->where('id', $currency->id)->first()->pivot->price
                                    : 0 }}
                                {{ $currency->CODE }}</td>
                            @if (!$loop->last)
                                <td class="w-2/6 text-[8px]">
                                    <input disabled type="checkbox" @checked(in_array($package->id . '_' . $package->EffAdsOptions[$loop->index + 1]->id, $contract->eff_ads_check ?? []))
                                        class="mr-1 h-3 text-[8px]">{{ $package->EffAdsOptions[$loop->index + 1]->title }}
                                </td>
                                <td class="w-1/6 text-[8px]">
                                    {{ $package->EffAdsOptions[$loop->index + 1]->Currencies->where('id', $currency->id)->first()
                                        ? $package->EffAdsOptions[$loop->index + 1]->Currencies->where('id', $currency->id)->first()->pivot->price
                                        : 0 }}
                                    {{ $currency->CODE }}</td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            @endforeach
        </div>
        <div class="w-1/4">
            <div class="text-right font-bold border border-black pb-[2px] mb-[2px]">
                <p>Total: {{ $contract->eff_ads_amount }} {{ $currency->CODE }}</p>
            </div>
            @if($contract->eff_ads_discount > 0)
            <div class="text-right font-bold border border-black pb-[2px] mb-[2px]">
                <p>Discount: {{ $contract->eff_ads_discount }} {{ $currency->CODE }}</p>
            </div>
            <div class="text-right font-bold border border-black pb-[2px] mb-[2px]">
                <p>Net: {{ $contract->eff_ads_net }} {{ $currency->CODE }}</p>
            </div>
            @endif
        </div>
    </div>

</div>
