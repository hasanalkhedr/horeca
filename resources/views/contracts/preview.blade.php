{{-- @extends('layouts.app')
@vite(['resources/js/fillPDF.js'])
@section('content')
    <h2 class="text-2xl font-bold mb-2 text-blue-700">New Contract</h2>

        <script>
            const fieldValues = @json($fieldValues ); // Laravel -> JS
            const contractPDF = @json($path);
            const contract = @json($contract)
        </script>
@endsection --}}
@extends('components.layouts.app')
@section('content')
    <!-- Page 1 -->
    <div
        class="page page-container mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
        @foreach ($contract->Report->components as $component)
            @livewire($component, [$contract])
        @endforeach
    </div>
    <!-- Page 2: Terms and Conditions -->
    <div
        class="page page-container mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
        @livewire('footer-component')
    </div>
@endsection
