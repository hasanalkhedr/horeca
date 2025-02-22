@extends('components.layouts.app')
@section('content')
    <!-- Page 1 -->
    <div class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
        @foreach ($components as $component)
        {{-- @if($component == 'header-component')
           @php
               $fields = [
                'name' => 'HORECA',
                'dates' => '1-10 Feb, 2025',
                'location' => 'Homs, Almoushrefah',
                'contract_no' => '0C-0123546',
                'readOnly' => true,
                'reportId' => $report->id,
            ]
           @endphp
        @endif --}}
            @livewire($component, ['paymentMethod' => $report->payment_method ,
            'bankAccount' => $report->bank_account ,
            'bankNameAddress' => $report->bank_name_address ,
            'swiftCode' => $report->swift_code ,
            'iban' => $report->iban,
            'with_logo' => $report->with_logo,
            'logo_path' => $report->logo_path,
            'currency' => $report->Currency], )
        @endforeach
        <!-- Header Section -->
        {{-- @if (in_array('header', $components))
        @livewire('header-component', [
            'name' => 'HORECA',
            'dates' => '1-10 Feb, 2025',
            'location' => 'Homs, Almoushrefah',
            'contract_no' => '0C-0123546',
            'readOnly' => true,
            'reportId' => $report->id,
        ])
    @endif

    <!-- Company Details Section -->
    @if (in_array('company_details', $components))
        @livewire('company-details-component')
    @endif
    <!-- Application Section -->
    @if (in_array('price_section', $components))
        @livewire('price-section-component')
    @endif
    @if (in_array('water_section', $components))
        @livewire('water-section')
    @endif
    @if (in_array('new_product', $components))
        @livewire('new-product-section')
    @endif
    @if (in_array('sponsor_section', $components))
        @livewire('sponsor-section')
    @endif
    @if (in_array('advertisement_section', $components))
        @livewire('advertisement-section')
    @endif
    @if (in_array('payment_section', $components))
        @livewire('payment-section')
    @endif
    @if (in_array('notes_section', $components))
        @livewire('notes-section')
    @endif
    @if (in_array('signature_section', $components))
        @livewire('signature-section')
    @endif --}}


        {{-- <!-- Sponsorship / Advertising Section -->
        <div class="border-section">
            <h2 class="text-lg font-semibold mb-4">Sponsorship / Effective Advertising</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p><strong>Advertisement in Hospitality News HORECA issue:</strong></p>
                    <p><input type="checkbox"> Double page with ribbon 6,000 US$</p>
                    <p><input type="checkbox"> Inside back cover 3,200 US$</p>
                    <p><input type="checkbox"> 1st double 5,500 US$</p>
                    <p><input type="checkbox"> Full page ad - HORECA special rate 1,299 US$</p>
                </div>
                <div>
                    <p><input type="checkbox"> Double page spread 4,500 US$</p>
                    <p><input type="checkbox"> Feature full page - advertorial 1,750 US$</p>
                    <p><input type="checkbox"> Flyer 3,500 - 5,000 US$</p>
                    <p><input type="checkbox"> Half page ad - Quarter page ad 1,575 US$ - 1,090 US$</p>
                </div>
            </div>
        </div>

        <!-- Total Amounts Section -->
        <div class="border-section">
            <h2 class="text-lg font-semibold mb-4">TOTAL AMOUNTS</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p><strong>Stand Booking:</strong> ________________________ US$</p>
                    <p><strong>Special Design Option:</strong> ________________________ US$</p>
                    <p><strong>Sponsorship/Advertising:</strong> ________________________ US$</p>
                </div>
                <div>
                    <p><strong>Subtotal:</strong> ________________________ US$</p>
                    <p><strong>Bank Transfer Fee:</strong> 25 US$</p>
                    <p><strong>Total Amount Due:</strong> ________________________ US$</p>
                </div>
            </div>
        </div>

        <!-- Payment Method Section -->
        <div class="border-section">
            <h2 class="text-lg font-semibold mb-4">PAYMENT METHOD</h2>
            <p>50% upon signature - 50% on or before 8 March 2025</p>
            <p class="text-sm text-gray-600">
                No application will be considered binding upon the Organizer unless accompanied by the appropriate payment.
                All payments have to be made by cash or bank transfer to the order of Hospitality Services s.a.r.l.
                A/C No. 15911338015840 - BANQUE LIBANO-FRANCAISE S.A.L. - Sin-EI Fli - Lebanon Swift Code: BLFSLBBX
                IBAN: LB 8100100000015911338015840. Please allow an extra USD 25 for each bank transfer.
            </p>
        </div>

        <!-- Signature Section -->
        <div class="border-section">
            <p class="text-sm text-gray-600">
                By signing the present application, we irrevocably undertake to pay the amount due as indicated above and formally agree to abide by the terms and conditions overleaf.
                (This application must be signed by a person duly authorized.)
            </p>
            <div class="mt-4">
                <p><strong>Additional contact person:</strong> ________________________</p>
                <p><strong>For and on behalf of the exhibiting company:</strong></p>
                <p><strong>Date:</strong> ________________________</p>
                <p><strong>Name:</strong> ________________________</p>
                <p><strong>Position in company:</strong> ________________________</p>
            </div>
        </div> --}}
    </div>
    <!-- Terms and Conditions -->
    <div
        class="page mx-auto bg-white min-h-[297mm] max-w-[210mm] shadow-lg print:shadow-none px-[5mm] py-[1mm] my-8 print:my-0 relative">
        {{-- @if (in_array('footer', $components)) --}}
        @livewire('footer-component')
        {{-- @endif --}}
    </div>


@endsection
