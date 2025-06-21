<div class="pt-[1px]">
    <div class="flex justify-between">
        <div class="w-7/12 mr-6">
            <label class="block font-semibold text-sm">Additional Contact Person <span
                    class="font-bold underline">{{ $contract->ExhabitionCoordinator->name }}</span></label>
            <label class="font-bold text-sm">For and on behalf of the exhibiting company</label>
            <label class="block font-semibold text-sm">Date <span
                    class="font-bold underline">{{ $contract->contract_date }}</span></label>
            <label class="block font-semibold text-sm">Name <span
                    class="font-bold underline">{{ $contract->ContactPerson->name }}</span></label>
            <label class="block font-semibold text-sm">Position in company <span
                    class="font-bold underline">{{ $contract->ContactPerson->position }}</span></label>

            <label class="font-semibold text-xs mt-2 pt-2">For Organizers use only</label>
            <table class="w-full">
                <tr>
                    <td class="border-l border-b border-gray-500 text-left text-sm font-semibold">SR/EO</td>
                    <td class="border-l border-b border-gray-500 text-left text-sm font-semibold">SC</td>
                    <td class="border-l border-b border-gray-500 text-left text-sm font-semibold">CS</td>
                    <td class="border-l border-b border-gray-500 text-left text-sm font-semibold">PM</td>
                    <td class="border-l border-b border-r border-gray-500 text-left text-sm font-semibold">ID</td>
                </tr>
            </table>
        </div>
        <div class="w-5/12">
            <div class="text-xs font-bold border border-black mb-[2px] pb-[2px] h-full">
                Signature & Company Stamp (stamp is compulsory)
            </div>
        </div>
    </div>

</div>
