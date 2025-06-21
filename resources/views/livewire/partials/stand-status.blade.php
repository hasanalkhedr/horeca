@switch($stand->status)
    @case('Available')
        <div class="flex">
            <img src="{{ asset('assets/icons/available.png') }}" alt="" class="w-8 h-8">
            <button @click="openModal('block', '{{ json_encode($stand) }}')">
                <img src="{{ asset('assets/icons/reserve.png') }}" alt="" class="w-8 h-8" title="Block">
            </button>
        </div>
    @break

    @case('Sold')
        <img src="{{ asset('assets/icons/sold.png') }}" alt="" class="w-8 h-8">
    @break

    @case('Reserved')
        <div class="flex">
            <img src="{{ asset('assets/icons/reserved.png') }}" alt="" class="w-8 h-8">
            <button @click="openModal('unblock', '{{ json_encode($stand) }}')">
                <img src="{{ asset('assets/icons/reserve.png') }}" alt="" class="w-8 h-8" title="UnBlock">
            </button>
        </div>
    @break
@endswitch
