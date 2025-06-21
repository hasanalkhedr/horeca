<div class="max-w-4xl w-full p-0 pt-[2px] bg-white">
    <div class="flex justify-between">
        <div class="w-3/4 items-center gap-2 mb-[2px] mr-6 ">
            <div class=" border border-black w-full pb-1">
            <label class="font-semibold">Advertisement in Hospitality News <span
                    class="font-bold underline"></span></label>
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
                                <input type="checkbox" @checked(in_array($package->id . '_' . $option->id, $contract->ads_check ?? [])) class="mr-1">{{ $option->title }}
                            </td>
                            <td class="w-1/6">
                                {{ $option->Currencies->where('id', $currency->id)->first()
                                    ? $option->Currencies->where('id', $currency->id)->first()->pivot->price
                                    : 0 }}
                                {{ $currency->CODE }}</td>
                            @if (!$loop->last)
                                <td class="w-2/6">
                                    <input type="checkbox" @checked(in_array($package->id . '_' . $package->AdsOptions[$loop->index + 1]->id, $contract->ads_check ?? []))
                                        class="mr-1">{{ $package->AdsOptions[$loop->index + 1]->title }}
                                </td>
                                <td class="w-1/6">
                                    {{ $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $currency->id)->first()
                                        ? $package->AdsOptions[$loop->index + 1]->Currencies->where('id', $currency->id)->first()->pivot->price
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
                <p>Total: {{ $contract->advertisment_amount }} {{ $currency->CODE }}</p>
            </div>
            @if($contract->ads_discount > 0)
            <div class="text-right font-bold border border-black pb-[2px] mb-[2px]">
                <p>Discount: {{ $contract->ads_discount }} {{ $currency->CODE }}</p>
            </div>
            <div class="text-right font-bold border border-black pb-[2px] mb-[2px]">
                <p>Net: {{ $contract->ads_net }} {{ $currency->CODE }}</p>
            </div>
            @endif
        </div>
    </div>

</div>
