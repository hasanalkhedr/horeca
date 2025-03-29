<div class="pt-[2px] w-full">
    @switch($with_options)
        @case('options_table')
            <table class="border border-gray-800 w-full">
                <thead>
                    <th colspan="2" style="padding: 5px !important" class="bg-gray-500 text-white font-bold text-center w-full">
                        {{ $contract->SponsorPackage ? $contract->SponsorPackage->title : '' }}</th>
                </thead>
                <tbody>
                    @forelse ($contract->SponsorPackage->SponsorOptions as $option)
                        @if ($loop->even)
                            @continue
                        @endif
                        <tr>
                            <td style="padding: 5px !important" class="w-1/2 border border-gray-800">{{ $option->title }}</td>
                            @if (!$loop->last)
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">
                                    {{ $contract->SponsorPackage->SponsorOptions[$loop->index + 1]->title }}</td>
                            @endif
                        </tr>
                    @empty
                        @if ($contract->SponsorPackage->id <= 0)
                            <tr>
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">Option 2</td>
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">Option 1</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">Option 3</td>
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">Option 4</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">Option 5</td>
                                <td style="padding: 5px !important" class="w-1/2 border border-gray-800">Option 6</td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        @break

        @case('package_title')
            <div class="flex justify-between">
                <div class="border border-black w-5/6 mr-6">
                    <label class="font-bold">Sponsorship / Effective Advertising <span
                            class="underline">{{ $contract->SponsorPackage ? $contract->SponsorPackage->title : '' }}</span></label>
                </div>
                <div class="w-1/6">
                    <div class="text-right font-bold border border-black mb-[2px]">
                        <p>{{ $contract->sponsor_amount }} {{ $currency->CODE }}</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-between text-xs font-bold">
                <div class="w-5/6 mr-6">
                    <label class="font-semibold underline">Specify <span
                            class="font-bold">{{ $contract->specify_text }}</span></label>
                </div>
                <div class="w-1/6">

                </div>
            </div>
        @break

        @case('packages_list')
            <div class="flex justify-between">
                <div class="border border-black w-5/6 mr-6">
                    <table class="border border-gray-800 w-full">
                        <thead>
                            <th colspan="2" style="padding: 5px !important"
                                class="bg-gray-500 text-white font-bold text-center w-full">
                                Event Sponsorship Opportunities</th>
                        </thead>
                        <tbody>
                            @if ($event)
                                @forelse ($event->SponsorPackages as $package)
                                    <tr>
                                        <td style="padding: 5px !important" class="w-1/2 text-left">
                                            <input type="checkbox" class="mr-2" @checked($package->id == $contract->sponsor_package_id)>
                                            <label class="font-semibold">{{ $package->title }}</label>
                                        </td>
                                        <td style="padding: 5px !important" class="w-1/2 text-right">
                                            <label class="font-semibold">{{ $package->currencies->where('id',$currency->id)->first()->pivot->total_price }}
                                                {{ $currency->CODE }}</label>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">NO Sponsors in this event</td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td style="padding: 5px !important" class="w-1/2 text-left">
                                        <input type="checkbox" class="mr-2" checked>
                                        <label class="font-semibold">Golden Sponsor</label>
                                    </td>
                                    <td style="padding: 5px !important" class="w-1/2 text-right">
                                        <label class="font-semibold">00,00 USD</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px !important" class="w-1/2 text-left">
                                        <input type="checkbox" class="mr-2">
                                        <label class="font-semibold">Golden Sponsor</label>
                                    </td>
                                    <td style="padding: 5px !important" class="w-1/2 text-right">
                                        <label class="font-semibold">00,00 USD</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px !important" class="w-1/2 text-left">
                                        <input type="checkbox" class="mr-2">
                                        <label class="font-semibold">Golden Sponsor</label>
                                    </td>
                                    <td style="padding: 5px !important" class="w-1/2 text-right">
                                        <label class="font-semibold">00,00 USD</label>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="w-1/6">
                    <div class="text-right font-bold border border-black mb-[2px] pb-1">
                        <p>{{ $contract->sponsor_amount }} {{ $currency->CODE }}</p>
                    </div>
                </div>
            </div>
        @break

    @endswitch
    </div>
