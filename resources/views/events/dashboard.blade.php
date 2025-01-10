@extends('layouts.app')
@section('content')
    <h1 class="text-2xl">{{ $event->name }}</h1>
    <h6>{{ $event->start_date }} to {{ $event->end_date }}</h6>
    <div>{!! $event->payment_method !!}</div>
    <div class="border shadow">
        <form action="{{ route('contracts.create', $event) }}" method="get">
            @csrf
            <h4>Choose Contract type you want to add:</h4>
            @foreach ($event->ContractTypes as $ct)
                <div class="block">
                    <input class="mr-5 pr-5" type="radio" name="contract_type_id" id="{{$ct->id}}" value="{{ $ct->id }}">{{ $ct->name }}
                </div>
            @endforeach
            <x-primary-button>Add Contract</x-primary-button>
        </form>
    </div>
@endsection
