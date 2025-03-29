@extends('components.layouts.app')
@section('content')
    <!-- Page 1 -->
    <div
        class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
        @foreach ($components as $component)
            @livewire($component,
            [
                'paymentMethod' => $report->payment_method,
                'bankAccount' => $report->bank_account,
                'bankNameAddress' => $report->bank_name_address,
                'swiftCode' => $report->swift_code,
                'iban' => $report->iban,
                'with_logo' => $report->with_logo,
                'logo_path' => $report->logo_path,
                'currency' => $report->Currency,
                'event' => $report->Event,
                'showCategories' => $report->show_categories,
                'special_price' => $report->special_price,
                'with_options' => $report->with_options,
                'vat' => $report->Event->vat_rate,
            ])
        @endforeach
    </div>
    <!-- Terms and Conditions -->
    <div
        class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
        @livewire('footer-component')
    </div>
@endsection
