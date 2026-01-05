<div class="max-w-5xl w-full p-0 bg-white">
    <!-- Title Section -->
    <div class="flex justify-between items-center px-2">
        <h2 class="text-lg leading-none font-bold">1. COMPANY DETAILS</h2>
        <span class="text-sm font-bold uppercase"></span>
    </div>
    <!-- Company Details Fields -->
    <div>
        <div class="grid grid-cols-2 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">Company</div>
                <div class="px-2">{{ $contract->Company->name }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">(for billing)</div>
                <div class="px-2">{{ $contract->Company->CODE }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">M.O.F. N°</div>
                <div class="px-2">{{ $contract->Company->vat_number }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    Commercial Register N°</div>
                <div class="px-2">{{ $contract->Company->commerical_registry_number }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">Contact Person</div>
                <div class="px-2">{{ $contract->ContactPerson ? $contract->ContactPerson->name : '' }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    Mobile</div>
                <div class="px-2">{{ $contract->ContactPerson ? $contract->ContactPerson->mobile : '' }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">Exhibition Coordinator</div>
                <div class="px-2">{{ $contract->ExhabitionCoordinator ? $contract->ExhabitionCoordinator->name : '' }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    Mobile</div>
                <div class="px-2">{{ $contract->ExhabitionCoordinator ? $contract->ExhabitionCoordinator->mobile : '' }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">Mailing Address</div>
                <div class="px-2">{{ $contract->Company->po_box }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    Country</div>
                <div class="px-2">{{ $contract->Company->country }}</div>
            </div>
        </div>
        <div class="grid grid-cols-3 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">Phone</div>
                <div class="px-2">{{ $contract->Company->phone }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    Email</div>
                <div class="px-2">{{ $contract->Company->email }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    Website</div>
                <div class="px-2">{{ $contract->Company->website }}</div>
            </div>
        </div>
        <div class="grid grid-cols-3 border border-black mb-[1px]">
            <div class="flex">
                <div class="px-2 font-semibold">facebook page</div>
                <div class="px-2">{{ $contract->Company->facebook_link }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    instagram</div>
                <div class="px-2">{{ $contract->Company->instagram_link }}</div>
            </div>
            <div class="flex">
                <div class="px-2 font-semibold">
                    X (twitter)</div>
                <div class="px-2">{{ $contract->Company->x_link }}</div>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    @if($showCategories)
    <div>
        <table class="border border-black w-full">
            <tr>
                <td class="border border-gray-800 p-0" rowspan="2"><span class="font-bold text-lg block leading-none">Category</span><span class="text-xs"> (please select)</span></td>
                @php
                    $l =floor(count($contract->Event->Categories) / 2);
                @endphp
                @foreach ($contract->Event->Categories as $index => $category)
                    <td class="border border-gray-800 items-center text-center  p-0">
                        <label class="flex items-center">
                            <input type="checkbox" class="w-3 h-3 border rounded"
                                @if ($contract->category_id == $category->id) checked @endif>
                            <span class="text-[10px]">{{ $category->name }}</span>
                        </label>
                    </td>
                    @if ($index == $l - 1)
            </tr>
            <tr>
                @endif
                @endforeach
            </tr>
        </table>
    </div>
    @endif
</div>
