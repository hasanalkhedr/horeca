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
        class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[7mm] py-[5mm] my-8 print:my-0 relative">
        @foreach ($contract->Report->components as $component)
            @livewire($component, [$contract, 'paymentMethod' => $contract->Report->payment_method, 'bankAccount' => $contract->Report->bank_account, 'bankNameAddress' => $contract->Report->bank_name_address, 'swiftCode' => $contract->Report->swift_code, 'iban' => $contract->Report->iban, 'with_logo' => $contract->Report->with_logo, 'logo_path' => $contract->Report->logo_path,
            'currency' => $contract->Report->Currency])
        @endforeach
    </div>
    <!-- Page 2: Terms and Conditions -->
    <div
        class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[7mm] py-[5mm] my-8 print:my-0 relative">
        @livewire('footer-component')
    </div>
@endsection
