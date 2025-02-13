@extends('layouts.app')
@section('content')
    <h1 class="text-2xl">{{ $event->name }}</h1>
    <h6>{{ $event->start_date }} to {{ $event->end_date }}</h6>
    <div>{!! $event->payment_method !!}</div>
    <div class="border shadow">
        <form action="{{ route('contracts.create', $event) }}" method="get">
            @csrf
            <h4>Choose Contract Template you want:</h4>
            @foreach ($event->Reports as $report)
                <div class="block">
                    <input class="mr-5 pr-5" type="radio" name="report_id" id="{{ $report->id }}"
                        value="{{ $report->id }}">{{ $report->name }}
                </div>
            @endforeach
            <x-primary-button>Add Contract</x-primary-button>
        </form>
    </div>

    {{-- @livewire('generic-table', [
        'model' => App\Models\Stand::class,
        'columns' => ['id', 'no', 'created_at'],
        'searchableColumns' => ['no'],
    ])
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-xl font-bold">Total Users</h2>
            <p class="text-2xl mt-2">150</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-xl font-bold">Active Projects</h2>
            <p class="text-2xl mt-2">12</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-xl font-bold">Pending Tasks</h2>
            <p class="text-2xl mt-2">34</p>
        </div>

        <!-- Card 4 -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-xl font-bold">Revenue</h2>
            <p class="text-2xl mt-2">$4,200</p>
        </div>
    </div> --}}
@endsection
