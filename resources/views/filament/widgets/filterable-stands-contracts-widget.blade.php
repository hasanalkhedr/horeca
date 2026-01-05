<div>
    <!-- Filter Form -->
    <div class="mb-6 bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Filter Charts</h3>
            <div class="text-sm text-gray-600">
                {{ $this->getFilterSummary() }}
            </div>
        </div>

        <form wire:submit.prevent class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{ $this->form }}

            <div class="flex items-end space-x-2">
                <x-filament::button
                    type="button"
                    color="gray"
                    size="sm"
                    wire:click="resetFilters"
                    icon="heroicon-o-arrow-path"
                >
                    Reset
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="primary"
                    size="sm"
                    icon="heroicon-o-funnel"
                    wire:click="applyFilters"
                >
                    Apply
                </x-filament::button>
            </div>
        </form>
    </div>

    <!-- Store data in hidden elements for Alpine.js -->
    <div
        x-data="{
            standsData: @js($this->getStandsData()),
            contractsData: @js($this->getContractsData()),
            standsChart: null,
            contractsChart: null,

            init() {
                this.initStandsChart();
                this.initContractsChart();
            },

            initStandsChart() {
                const ctx = document.getElementById('standsChart').getContext('2d');
                this.standsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: this.standsData.labels,
                        datasets: [{
                            data: this.standsData.data,
                            backgroundColor: this.standsData.colors,
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' stands';
                                    }
                                }
                            }
                        }
                    }
                });
            },

            initContractsChart() {
                const ctx = document.getElementById('contractsChart').getContext('2d');
                this.contractsChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: this.contractsData.labels,
                        datasets: [{
                            data: this.contractsData.counts,
                            backgroundColor: this.contractsData.colors,
                            borderColor: '#ffffff',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.raw + ' contracts';
                                    }
                                }
                            }
                        }
                    }
                });
            },

            updateCharts() {
                // Destroy existing charts
                if (this.standsChart) {
                    this.standsChart.destroy();
                }
                if (this.contractsChart) {
                    this.contractsChart.destroy();
                }

                // Update data from Livewire
                this.standsData = @js($this->getStandsData());
                this.contractsData = @js($this->getContractsData());

                // Reinitialize charts
                this.initStandsChart();
                this.initContractsChart();
            }
        }"
        x-init="init"
        @filter-updated.window="updateCharts"
    >
        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Stands Chart Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Stands Distribution</h3>
                        <p class="text-sm text-gray-600">
                            @if(!empty($selectedEvents))
                                Filtered by {{ count($selectedEvents) }} event(s)
                            @else
                                All Events
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900">{{ $this->getStandsData()['total'] }}</div>
                        <div class="text-sm text-gray-600">Total Stands</div>
                    </div>
                </div>

                <!-- Stands Chart -->
                <div class="h-64 mb-4">
                    <canvas id="standsChart"></canvas>
                </div>

                <!-- Stands Stats -->
                <div class="grid grid-cols-4 gap-3">
                    @php
                        $standsData = $this->getStandsData();
                    @endphp
                    @foreach($standsData['labels'] as $index => $label)
                        @php
                            $count = $standsData['data'][$index];
                            $space = $standsData['space'][$index];
                            $percentage = $standsData['total'] > 0 ? round(($count / $standsData['total']) * 100, 1) : 0;
                            $bgColors = [
                                'bg-green-100 border-green-200',
                                'bg-red-100 border-red-200',
                                'bg-amber-100 border-amber-200',
                                'bg-purple-100 border-purple-200',
                            ][$index];
                        @endphp
                        <div class="p-3 rounded-lg border {{ $bgColors }}">
                            <div class="text-center">
                                <div class="text-xl font-bold text-gray-900">{{ $count }}</div>
                                <div class="text-xs text-gray-600">{{ $label }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ number_format($space) }} sqm
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Contracts Chart Card -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Contracts Status</h3>
                        <p class="text-sm text-gray-600">
                            @if(!empty($selectedEvents))
                                Filtered by {{ count($selectedEvents) }} event(s)
                            @else
                                All Events
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900">{{ $this->getContractsData()['total'] }}</div>
                        <div class="text-sm text-gray-600">Total Contracts</div>
                    </div>
                </div>

                <!-- Contracts Chart -->
                <div class="h-64 mb-4">
                    <canvas id="contractsChart"></canvas>
                </div>

                <!-- Contracts Stats -->
                <div class="grid grid-cols-4 gap-3">
                    @php
                        $contractsData = $this->getContractsData();
                    @endphp
                    @foreach($contractsData['labels'] as $index => $label)
                        @php
                            $count = $contractsData['counts'][$index];
                            $amount = $contractsData['amounts'][$index];
                            $percentage = $contractsData['total'] > 0 ? round(($count / $contractsData['total']) * 100, 1) : 0;
                            $bgColors = [
                                'bg-gray-100 border-gray-200',
                                'bg-blue-100 border-blue-200',
                                'bg-amber-100 border-amber-200',
                                'bg-green-100 border-green-200',
                            ][$index];
                        @endphp
                        <div class="p-3 rounded-lg border {{ $bgColors }}">
                            <div class="text-center">
                                <div class="text-xl font-bold text-gray-900">{{ $count }}</div>
                                <div class="text-xs text-gray-600">{{ $label }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    ${{ number_format($amount, 0) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <x-heroicon-o-map-pin class="h-6 w-6 text-blue-600" />
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-blue-900">{{ $this->getStandsData()['total'] }}</div>
                        <div class="text-sm text-blue-600">Total Stands</div>
                    </div>
                </div>
                <div class="mt-4 text-sm text-blue-700">
                    {{ number_format($this->getStandsData()['totalSpace']) }} sqm total space
                </div>
            </div>

            <div class="bg-green-50 rounded-xl p-6 border border-green-100">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <x-heroicon-o-document-text class="h-6 w-6 text-green-600" />
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-green-900">{{ $this->getContractsData()['total'] }}</div>
                        <div class="text-sm text-green-600">Total Contracts</div>
                    </div>
                </div>
                <div class="mt-4 text-sm text-green-700">
                    ${{ number_format($this->getContractsData()['totalAmount'], 2) }} total revenue
                </div>
            </div>

            <div class="bg-amber-50 rounded-xl p-6 border border-amber-100">
                <div class="flex items-center">
                    <div class="p-3 bg-amber-100 rounded-lg">
                        <x-heroicon-o-chart-bar class="h-6 w-6 text-amber-600" />
                    </div>
                    <div class="ml-4">
                        @php
                            $soldStands = $this->getStandsData()['data'][1];
                            $soldPercentage = $this->getStandsData()['total'] > 0
                                ? round(($soldStands / $this->getStandsData()['total']) * 100, 1)
                                : 0;
                        @endphp
                        <div class="text-2xl font-bold text-amber-900">{{ $soldPercentage }}%</div>
                        <div class="text-sm text-amber-600">Stands Sold</div>
                    </div>
                </div>
                <div class="mt-4 text-sm text-amber-700">
                    {{ $soldStands }} sold stands
                </div>
            </div>

            <div class="bg-purple-50 rounded-xl p-6 border border-purple-100">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <x-heroicon-o-check-circle class="h-6 w-6 text-purple-600" />
                    </div>
                    <div class="ml-4">
                        @php
                            $paidContracts = $this->getContractsData()['counts'][3];
                            $paidPercentage = $this->getContractsData()['total'] > 0
                                ? round(($paidContracts / $this->getContractsData()['total']) * 100, 1)
                                : 0;
                        @endphp
                        <div class="text-2xl font-bold text-purple-900">{{ $paidPercentage }}%</div>
                        <div class="text-sm text-purple-600">Contracts Paid</div>
                    </div>
                </div>
                <div class="mt-4 text-sm text-purple-700">
                    {{ $paidContracts }} paid contracts
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
