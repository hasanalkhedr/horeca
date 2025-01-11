@extends('layouts.app')
@vite(['resources/js/fillPDF.js'])
@section('content')
    <h2 class="text-2xl font-bold mb-2 text-blue-700">New Contract</h2>
{{-- @push('scripts')
    @vite(['resources/js/fillPDF.js'])
@endpush --}}
        <script>
            const fieldValues = @json($fieldValues ); // Laravel -> JS
            const contractPDF = @json($path);
            const contract = @json($contract)
        </script>
@endsection
