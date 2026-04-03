<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        {{-- Summary Cards --}}
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Summary Statistics
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Events --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Events</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->summaryData['total_events'] }}</p>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Space Achievement --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Space Achievement</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->summaryData['space_achievement_percent'] }}%</p>
                            <p class="text-xs text-gray-500">{{ number_format($this->summaryData['total_sold_space']) }} / {{ number_format($this->summaryData['total_target_space']) }} sqm</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($this->summaryData['space_achievement_percent'], 100) }}%"></div>
                            </div>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full ml-3">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Space Amount Achievement --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Space Amount Achievement</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->summaryData['space_amount_achievement_percent'] }}%</p>
                            <p class="text-xs text-gray-500">${{ number_format($this->summaryData['total_space_amount']) }} / ${{ number_format($this->summaryData['total_target_space_amount']) }}</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ min($this->summaryData['space_amount_achievement_percent'], 100) }}%"></div>
                            </div>
                        </div>
                        <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full ml-3">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Sponsor Amount Achievement --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sponsor Amount Achievement</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->summaryData['sponsor_amount_achievement_percent'] }}%</p>
                            <p class="text-xs text-gray-500">${{ number_format($this->summaryData['total_sponsor_amount']) }} / ${{ number_format($this->summaryData['total_target_sponsor_amount']) }}</p>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min($this->summaryData['sponsor_amount_achievement_percent'], 100) }}%"></div>
                            </div>
                        </div>
                        <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full ml-3">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Achievement Progress by Event
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Space Achievement Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Space Achievement (%)</h4>
                    <div style="height: 200px; position: relative;">
                        <canvas id="space-achievement-chart"></canvas>
                    </div>
                </div>

                {{-- Space Amount Achievement Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Space Amount Achievement (%)</h4>
                    <div style="height: 200px; position: relative;">
                        <canvas id="space-amount-achievement-chart"></canvas>
                    </div>
                </div>

                {{-- Sponsor Amount Achievement Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Sponsor Amount Achievement (%)</h4>
                    <div style="height: 200px; position: relative;">
                        <canvas id="sponsor-amount-achievement-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Events Details Table --}}
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Events Details
            </h3>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Space</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Space Achievement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Space Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sponsor Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contracts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->events as $event)
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
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $event->CODE }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($soldSpace, 0) }} / {{ number_format($event->target_space ?? 0, 0) }} sqm</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium
                                            {{ $spaceAchievementPercent >= 100 ? 'text-green-600' : ($spaceAchievementPercent >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $spaceAchievementPercent }}%
                                        </span>
                                        @if($spaceAchievementPercent >= 100)
                                            <svg class="w-4 h-4 text-green-500 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                        <div class="{{ $spaceAchievementPercent >= 100 ? 'bg-green-600' : ($spaceAchievementPercent >= 75 ? 'bg-yellow-600' : 'bg-red-600') }} h-1 rounded-full"
                                             style="width: {{ min($spaceAchievementPercent, 100) }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($spaceAmount, 0) }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $spaceAmountAchievementPercent }}% of target</div>
                                    <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                        <div class="{{ $spaceAmountAchievementPercent >= 100 ? 'bg-green-600' : ($spaceAmountAchievementPercent >= 75 ? 'bg-yellow-600' : 'bg-red-600') }} h-1 rounded-full"
                                             style="width: {{ min($spaceAmountAchievementPercent, 100) }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($sponsorAmount, 0) }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $sponsorAmountAchievementPercent }}% of target</div>
                                    <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                        <div class="{{ $sponsorAmountAchievementPercent >= 100 ? 'bg-green-600' : ($sponsorAmountAchievementPercent >= 75 ? 'bg-yellow-600' : 'bg-red-600') }} h-1 rounded-full"
                                             style="width: {{ min($sponsorAmountAchievementPercent, 100) }}%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $event->contracts->count() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    ${{ number_format($totalAmount, 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Contract Details --}}
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Contract Details
            </h3>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contract</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Space</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Space Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sponsor Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->events as $event)
                            @foreach($event->contracts as $contract)
                                <?php
                                $rateToUSD = $contract->Report->Currency->rate_to_usd ?? 1;
                                $spaceAmount = ($contract->space_net ?? 0) * $rateToUSD;
                                $sponsorAmount = ($contract->sponsor_net ?? 0) * $rateToUSD;
                                $totalAmount = ($contract->net_total ?? 0) * $rateToUSD;
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $contract->CODE ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $contract->client_name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $contract->client_company ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $contract->Stand ? number_format($contract->Stand->space, 0) . ' sqm' : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        ${{ number_format($spaceAmount, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        ${{ number_format($sponsorAmount, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        ${{ number_format($totalAmount, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $contract->status === 'S&P' ? 'bg-green-100 text-green-800' :
                                               ($contract->status === 'S&NP' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $contract->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Ensure chart data is available
                const eventNames = @json($this->chartData['event_names'] ?? []);
                const spaceAchievement = @json($this->chartData['space_achievement'] ?? []);
                const spaceAmountAchievement = @json($this->chartData['space_amount_achievement'] ?? []);
                const sponsorAmountAchievement = @json($this->chartData['sponsor_amount_achievement'] ?? []);

                console.log('Chart data:', {
                    eventNames,
                    spaceAchievement,
                    spaceAmountAchievement,
                    sponsorAmountAchievement
                });

                // Prevent multiple chart initializations
                let chartsInitialized = false;

                // Wait for elements to be available
                setTimeout(function() {
                    if (chartsInitialized) return;
                    chartsInitialized = true;

                    // Chart options with better performance
                    const chartOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 500 // Shorter animation for faster loading
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 150
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Hide legend for cleaner look
                            }
                        }
                    };

                    // Space Achievement Chart
                    const spaceCanvas = document.getElementById('space-achievement-chart');
                    if (spaceCanvas) {
                        console.log('Space chart canvas found:', spaceCanvas);
                        try {
                            new Chart(spaceCanvas, {
                                type: 'bar',
                                data: {
                                    labels: eventNames,
                                    datasets: [{
                                        label: 'Space Achievement %',
                                        data: spaceAchievement,
                                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                        borderColor: 'rgba(34, 197, 94, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: chartOptions
                            });
                        } catch (error) {
                            console.error('Error creating space chart:', error);
                        }
                    } else {
                        console.error('Space chart canvas not found');
                    }

                    // Space Amount Achievement Chart
                    const spaceAmountCanvas = document.getElementById('space-amount-achievement-chart');
                    if (spaceAmountCanvas) {
                        console.log('Space amount chart canvas found:', spaceAmountCanvas);
                        try {
                            new Chart(spaceAmountCanvas, {
                                type: 'bar',
                                data: {
                                    labels: eventNames,
                                    datasets: [{
                                        label: 'Space Amount Achievement %',
                                        data: spaceAmountAchievement,
                                        backgroundColor: 'rgba(250, 204, 21, 0.8)',
                                        borderColor: 'rgba(250, 204, 21, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: chartOptions
                            });
                        } catch (error) {
                            console.error('Error creating space amount chart:', error);
                        }
                    } else {
                        console.error('Space amount chart canvas not found');
                    }

                    // Sponsor Amount Achievement Chart
                    const sponsorCanvas = document.getElementById('sponsor-amount-achievement-chart');
                    if (sponsorCanvas) {
                        console.log('Sponsor chart canvas found:', sponsorCanvas);
                        try {
                            new Chart(sponsorCanvas, {
                                type: 'bar',
                                data: {
                                    labels: eventNames,
                                    datasets: [{
                                        label: 'Sponsor Amount Achievement %',
                                        data: sponsorAmountAchievement,
                                        backgroundColor: 'rgba(147, 51, 234, 0.8)',
                                        borderColor: 'rgba(147, 51, 234, 1)',
                                        borderWidth: 1
                                    }]
                                },
                                options: chartOptions
                            });
                        } catch (error) {
                            console.error('Error creating sponsor chart:', error);
                        }
                    } else {
                        console.error('Sponsor chart canvas not found');
                    }
                }, 200); // Reduced delay to 200ms for faster loading
            });
        </script>
    @endpush
</x-filament-panels::page>
