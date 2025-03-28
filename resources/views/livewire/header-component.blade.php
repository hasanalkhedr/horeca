<div class="flex justify-between items-start pb-2 pt-1">
    <div class="flex items-center space-x-4">
        @if ($with_logo)
            <!-- Logo -->
            <div class="w-1/3">
                <img src="{{ asset('storage/' . $logo_path) }}" alt="Logo" class="max-w-32 h-auto">
            </div>
        @endif
        <!-- Event Details -->
        <div class="flex-grow text-center">
            <label class="text-5xl font-extrabold">{{ $contract->Event->name }}</label>
            <p class="text-gray-600 text-lg">
                <span class="font-bold text-gray-500">{{ $contract->Event->start_date->day }} -
                    {{ $contract->Event->end_date->day }} {{ $contract->Event->end_date->format('F Y') }} |
                    {{ $contract->Event->address }}, {{ $contract->Event->city }} in
                    {{ $contract->Event->country }}</span>
            </p>
        </div>
    </div>
    <!-- Contract Details -->
    <div class="text-right pt-6">
        <p class="text-gray-600 text-sm font-bold">N° <span
                class="bg-blue-100 px-2 py-[1px] inline-block font-bold">{{ $contract->contract_no }}</span></p>
        <h2 class="text-2xl font-bold mt-[1px]">Application form</h2>
    </div>
</div>


