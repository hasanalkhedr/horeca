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
        class="page mx-auto bg-white max-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[2mm] my-2 print:my-0 relative print:mx-0 print:px-0   ">
        @foreach ($contract->Report->components as $component)
            @livewire($component, [
                $contract,
                'paymentMethod' => $contract->Report->payment_method,
                'bankAccount' => $contract->Report->bank_account,
                'bankNameAddress' => $contract->Report->bank_name_address,
                'swiftCode' => $contract->Report->swift_code,
                'iban' => $contract->Report->iban,
                'with_logo' => $contract->Report->with_logo,
                'logo_path' => $contract->Report->logo_path,
                'currency' => $contract->Report->Currency,
                'event' => $contract->Event,
                'showCategories' => $contract->Report->show_categories,
                'special_price' => $contract->Report->special_price,
                'with_options'=>$contract->Report->with_options,
                'vat'=>$contract->Event->vat_rate,])
                @if(!$loop->last && !$loop->first)
                    <div class="w-full"><hr class="h-1 my-[2px] bg-blue-900 border-3 dark:bg-gray-700"></div>
                @endif
        @endforeach
    </div>
    <!-- Page 2: Terms and Conditions -->
    <div
        class="page mx-auto bg-white max-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[2mm] my-2 print:my-0 relative print:mx-0 print:px-0">
        @livewire('footer-component')
    </div>
@endsection
