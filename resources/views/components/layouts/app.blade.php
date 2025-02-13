<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Report Builder</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                max-height: 297mm;
                /* A4 height */
                page-break-after: always;
                /* Ensures each page is printed on a new sheet */
            }

            .page:last-child {
                page-break-after: auto;
                /* Prevents an extra blank page */
            }

            .border-section {
                border: 0px solid #000;
                padding: 1px;
                margin-bottom: 2px;
            }

            .footer {
                position: absolute;
                bottom: 10mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 10px;
            }
        }
    </style>

    @vite(['resources/js/app.js', 'resources/css/app.css'])
    {{-- @livewireStyles --}}
</head>
<body class="bg-gray-100 leading-none">
    {{-- {{ $slot }} <!-- This is where Livewire components will be rendered --> --}}
    @yield('content')
    {{-- @livewireScripts --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script> <!-- Add Sortable.js --> --}}
</body>
</html>
