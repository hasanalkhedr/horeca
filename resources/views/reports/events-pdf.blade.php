<!DOCTYPE html>
<html>

<head>
    <title>Events Report</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url({{ storage_path('fonts/DejaVuSans.ttf') }}) format('truetype');
        }

        @page {
            margin: 5mm 10mm 15mm 10mm;
            size: A4;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.1;
        }

        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 15mm;
            margin: 0 15mm;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            padding-top: 5px;
            color: gray;
        }

        .content {
            margin-bottom: 20mm;
        }

        .company-name {
            font-weight: normal;
            text-transform: uppercase;
            font-size: 11px;
            color: gray;
            margin-bottom: 10px;
        }

        .company-details {
            padding-top: 10px;
            font-size: 11px;
            line-height: 1.4;
        }

        .report-title {
            font-size: 30px;
            font-weight: bold;
            color: #c45911;
            margin: 5px 0 15px 0;
            text-transform: uppercase;
        }

        .report-info {
            font-size: 12px;
            text-align: right;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
            page-break-inside: avoid;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 6px;
            border: 1px solid #000;
        }

        .invoice-table th {
            background: #2d74b5;
            color: white;
            text-align: left;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 5px 0;
        }

        .summary-table th,
        .summary-table td {
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
        }

        .summary-table th {
            background: #2d74b5;
            color: white;
            font-weight: bold;
        }

        .progress-bar {
            width: 100%;
            background: #e9ecef;
            height: 15px;
            border-radius: 10px;
            margin: 5px 0;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
        }

        .progress-good {
            background: #28a745;
        }

        .progress-warning {
            background: #ffc107;
        }

        .progress-danger {
            background: #dc3545;
        }

        .achievement-good {
            color: #28a745;
            font-weight: bold;
        }

        .achievement-warning {
            color: #ffc107;
            font-weight: bold;
        }

        .achievement-danger {
            color: #dc3545;
            font-weight: bold;
        }

        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-signed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .page-break {
            page-break-after: always;
        }

        .thank-you {
            text-align: center;
            margin-top: 30px;
            font-style: italic;
            color: #2d74b5;
        }

        .section-title {
            color: #2d74b5;
            margin: 20px 0 10px 0;
            border-bottom: 2px solid #2d74b5;
            padding-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Footer -->
    <div class="footer">
        Hospitality Services S.A.R.L | Lebanon | Phone: +961XXXXXXXX | Email:<a href="mailto:admin@horeca.com">admin@horeca.com</a> | <a href="https://horeca.istdev.xyz/admin">horeca.istdev.xyz</a>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Header -->
        <div class="company-name">
            Hospitality Services S.A.R.L
        </div>

        <table width="100%" style="margin-top:10px; margin-bottom:20px;">
            <tr>
                <!-- Left side -->
                <td width="55%" valign="top">
                    <table>
                        <tr>
                            <td valign="top" style="padding-right:10px;">
                                <?php
                                $logoPath = public_path('images/logo.png');
                                $logoHtml = '';
                                if (file_exists($logoPath)) {
                                    $imageData = base64_encode(file_get_contents($logoPath));
                                    $logoHtml = '<img src="data:image/png;base64,' . $imageData . '" alt="Logo" height="60" style="max-height: 60px;">';
                                } else {
                                    $logoHtml = '<div style="font-size: 24px; font-weight: bold; color: #2d74b5; text-align: center; padding: 10px; border: 2px solid #2d74b5; border-radius: 5px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">HS</div>';
                                }
                                echo $logoHtml;
                                ?>
                                <div class="company-details">
                                    <div>Lebanon</div>
                                    <div>Phone : +961XXXXXXXX</div>
                                    <div><a href="mailto:admin@horeca.com">admin@horeca.com</a></div>
                                    <div><a href="https://horeca.istdev.xyz/admin">horeca.istdev.xyz</a></div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- Right side -->
                <td width="45%" valign="top" align="right">
                    <div class="report-title">EVENTS REPORT</div>
                    <div class="report-info">
                        <div><span style="color: rgb(45, 116, 181);font-weight: bold;">EVENTS</span>
                            {{ !empty($filters['event_ids']) ? \App\Models\Event::whereIn('id', $filters['event_ids'])->pluck('name')->implode(', ') : 'All Events' }}
                        </div>
                        @if(!empty($filters['start_date']) || !empty($filters['end_date']))
                        <div><span style="color: rgb(116, 45, 181);font-weight: bold;">DATE RANGE</span>
                            {{ $filters['start_date'] ? \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') : 'Start' }}
                            -
                            {{ $filters['end_date'] ? \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') : 'End' }}
                        </div>
                        @endif
                        <div><span style="color: rgb(45, 116, 181);font-weight: bold;">GENERATED</span>
                            {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Summary Statistics -->
        <h3 class="section-title">Summary Statistics</h3>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin: 15px 0; border-collapse: collapse;">
            <tr>
                <td width="25%" valign="top" style="padding: 5px;">
                    <div style="border: 1px solid #2d74b5; padding: 10px; text-align: center; border-radius: 5px; background: #f8f9fa;">
                        <h4 style="margin: 0 0 5px 0; color: #2d74b5; font-size: 11px; font-weight: bold;">TOTAL EVENTS</h4>
                        <div style="font-size: 16px; font-weight: bold; color: #c45911;">{{ $summaryData['total_events'] }}</div>
                    </div>
                </td>
                <td width="25%" valign="top" style="padding: 5px;">
                    <div style="border: 1px solid #2d74b5; padding: 10px; text-align: center; border-radius: 5px; background: #f8f9fa;">
                        <h4 style="margin: 0 0 5px 0; color: #2d74b5; font-size: 11px; font-weight: bold;">SPACE ACHIEVEMENT</h4>
                        <div style="font-size: 16px; font-weight: bold; color: #c45911;">{{ $summaryData['space_achievement_percent'] }}%</div>
                        <div style="font-size: 9px; color: #666;">{{ number_format($summaryData['total_sold_space']) }} / {{ number_format($summaryData['total_target_space']) }} sqm</div>
                        <div class="progress-bar">
                            <div class="progress-fill {{ $summaryData['space_achievement_percent'] >= 100 ? 'progress-good' : ($summaryData['space_achievement_percent'] >= 75 ? 'progress-warning' : 'progress-danger') }}"
                                 style="width: {{ min($summaryData['space_achievement_percent'], 100) }}%"></div>
                        </div>
                    </div>
                </td>
                <td width="25%" valign="top" style="padding: 5px;">
                    <div style="border: 1px solid #2d74b5; padding: 10px; text-align: center; border-radius: 5px; background: #f8f9fa;">
                        <h4 style="margin: 0 0 5px 0; color: #2d74b5; font-size: 11px; font-weight: bold;">SPACE AMOUNT</h4>
                        <div style="font-size: 16px; font-weight: bold; color: #c45911;">{{ $summaryData['space_amount_achievement_percent'] }}%</div>
                        <div style="font-size: 9px; color: #666;">${{ number_format($summaryData['total_space_amount']) }} / ${{ number_format($summaryData['total_target_space_amount']) }}</div>
                        <div class="progress-bar">
                            <div class="progress-fill {{ $summaryData['space_amount_achievement_percent'] >= 100 ? 'progress-good' : ($summaryData['space_amount_achievement_percent'] >= 75 ? 'progress-warning' : 'progress-danger') }}"
                                 style="width: {{ min($summaryData['space_amount_achievement_percent'], 100) }}%"></div>
                        </div>
                    </div>
                </td>
                <td width="25%" valign="top" style="padding: 5px;">
                    <div style="border: 1px solid #2d74b5; padding: 10px; text-align: center; border-radius: 5px; background: #f8f9fa;">
                        <h4 style="margin: 0 0 5px 0; color: #2d74b5; font-size: 11px; font-weight: bold;">SPONSOR AMOUNT</h4>
                        <div style="font-size: 16px; font-weight: bold; color: #c45911;">{{ $summaryData['sponsor_amount_achievement_percent'] }}%</div>
                        <div style="font-size: 9px; color: #666;">${{ number_format($summaryData['total_sponsor_amount']) }} / ${{ number_format($summaryData['total_target_sponsor_amount']) }}</div>
                        <div class="progress-bar">
                            <div class="progress-fill {{ $summaryData['sponsor_amount_achievement_percent'] >= 100 ? 'progress-good' : ($summaryData['sponsor_amount_achievement_percent'] >= 75 ? 'progress-warning' : 'progress-danger') }}"
                                 style="width: {{ min($summaryData['sponsor_amount_achievement_percent'], 100) }}%"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Events Details -->
        <h3 class="section-title">Events Details</h3>

        @foreach ($events as $index => $event)
            <?php
            $soldSpace = $event->contracts->sum(function ($contract) {
                return $contract->Stand ? $contract->Stand->space : 0;
            });
            $spaceAchievementPercent = $event->target_space > 0 ? round(($soldSpace / $event->target_space) * 100, 1) : 0;

            $spaceAmount = $event->contracts->sum(function ($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return ($contract->space_net ?? 0) * $rateToUSD;
            });
            $spaceAmountAchievementPercent = $event->target_space_amount > 0 ? round(($spaceAmount / $event->target_space_amount) * 100, 1) : 0;

            $sponsorAmount = $event->contracts->sum(function ($contract) {
                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                return ($contract->sponsor_net ?? 0) * $rateToUSD;
            });
            $sponsorAmountAchievementPercent = $event->target_sponsor_amount > 0 ? round(($sponsorAmount / $event->target_sponsor_amount) * 100, 1) : 0;

            $totalAmount = $event->contracts->sum(function($contract) {
                return ($contract->net_total ?? 0) * ($contract->Report->Currency->rate_to_usd ?? 1);
            });

            $getAchievementClass = function($percent) {
                if ($percent >= 100) return 'achievement-good';
                if ($percent >= 75) return 'achievement-warning';
                return 'achievement-danger';
            };
            ?>

            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                <!-- Event Header -->
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th colspan="4" style="background: #c45911;">
                                {{ $event->name }} - {{ $event->CODE }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="25%"><strong>Start Date:</strong> {{ $event->start_date->format('d/m/Y') }}</td>
                            <td width="25%"><strong>End Date:</strong> {{ $event->end_date->format('d/m/Y') }}</td>
                            <td width="25%"><strong>Total Contracts:</strong> {{ $event->contracts->count() }}</td>
                            <td width="25%"><strong>Total Amount:</strong> ${{ number_format($totalAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Space:</strong> {{ number_format($event->total_space, 0) }} sqm</td>
                            <td><strong>Sold Space:</strong> {{ number_format($soldSpace, 0) }} sqm</td>
                            <td><strong>Target Space:</strong> {{ number_format($event->target_space ?? 0, 0) }} sqm</td>
                            <td><strong>Space Achievement:</strong> <span class="{{ $getAchievementClass($spaceAchievementPercent) }}">{{ $spaceAchievementPercent }}%</span></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Achievement Progress -->
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Space Achievement</th>
                            <th>Space Amount Achievement</th>
                            <th>Sponsor Amount Achievement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="font-size: 14px; font-weight: bold; color: #c45911;">{{ $spaceAchievementPercent }}%</div>
                                <div style="font-size: 9px; color: #666;">{{ number_format($soldSpace) }} / {{ number_format($event->target_space ?? 0) }} sqm</div>
                                <div class="progress-bar">
                                    <div class="progress-fill {{ $spaceAchievementPercent >= 100 ? 'progress-good' : ($spaceAchievementPercent >= 75 ? 'progress-warning' : 'progress-danger') }}"
                                         style="width: {{ min($spaceAchievementPercent, 100) }}%"></div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 14px; font-weight: bold; color: #c45911;">{{ $spaceAmountAchievementPercent }}%</div>
                                <div style="font-size: 9px; color: #666;">${{ number_format($spaceAmount) }} / ${{ number_format($event->target_space_amount ?? 0) }}</div>
                                <div class="progress-bar">
                                    <div class="progress-fill {{ $spaceAmountAchievementPercent >= 100 ? 'progress-good' : ($spaceAmountAchievementPercent >= 75 ? 'progress-warning' : 'progress-danger') }}"
                                         style="width: {{ min($spaceAmountAchievementPercent, 100) }}%"></div>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 14px; font-weight: bold; color: #c45911;">{{ $sponsorAmountAchievementPercent }}%</div>
                                <div style="font-size: 9px; color: #666;">${{ number_format($sponsorAmount) }} / ${{ number_format($event->target_sponsor_amount ?? 0) }}</div>
                                <div class="progress-bar">
                                    <div class="progress-fill {{ $sponsorAmountAchievementPercent >= 100 ? 'progress-good' : ($sponsorAmountAchievementPercent >= 75 ? 'progress-warning' : 'progress-danger') }}"
                                         style="width: {{ min($sponsorAmountAchievementPercent, 100) }}%"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Contracts Details -->
                @if ($event->contracts->count() > 0)
                    <h4 style="color: #2d74b5; margin: 10px 0 5px 0;">Contracts ({{ $event->contracts->count() }})</h4>
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th width="15%">Contract Code</th>
                                <th width="25%">Client Name</th>
                                <th width="15%">Stand Space</th>
                                <th width="15%">Space Amount</th>
                                <th width="15%">Sponsor Amount</th>
                                <th width="15%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->contracts as $contract)
                                <?php
                                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                                $contractSpaceAmount = ($contract->space_net ?? 0) * $rateToUSD;
                                $contractSponsorAmount = ($contract->sponsor_net ?? 0) * $rateToUSD;
                                ?>
                                <tr>
                                    <td>{{ $contract->CODE ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ $contract->client_name ?? 'N/A' }}</strong><br>
                                        <small>{{ $contract->client_company ?? '' }}</small>
                                    </td>
                                    <td>{{ $contract->Stand ? number_format($contract->Stand->space, 0) . ' sqm' : 'N/A' }}</td>
                                    <td>${{ number_format($contractSpaceAmount, 0) }}</td>
                                    <td>${{ number_format($contractSponsorAmount, 0) }}</td>
                                    <td>
                                        <span class="status-badge
                                        @if ($contract->status === 'S&P') status-signed
                                        @elseif($contract->status === 'S&NP') status-pending
                                        @else status-pending
                                        @endif">
                                            {{ $contract->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="color: #666; font-style: italic; margin: 10px 0; text-align: center;">
                        No contracts recorded for this event
                    </p>
                @endif

                @if (!$loop->last)
                    <hr style="border-top: 2px dashed #2d74b5; margin: 20px 0;">
                @endif
            </div>
        @endforeach

        <!-- Thank You -->
        <div class="thank-you">
            <p>REPORT GENERATED BY Hospitality Services S.A.R.L SYSTEM</p>
            <p>For any inquiries, please contact: admin@horeca.com</p>
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>

</html>
