@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-4">Events</h1>

        <!-- Button to Add Event -->


        <!-- Table of Events -->
        <livewire:event-wizard event-id="{{$id}}"/>




    </div>


@endsection
