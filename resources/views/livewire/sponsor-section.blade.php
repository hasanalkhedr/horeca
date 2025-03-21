<div class="pt-[2px] w-full">
    @if (!$with_options)
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
    @else
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
                    @if ($contract->SponsorPackage->id <=0)
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
    @endif
</div>
