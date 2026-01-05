<div>
    {{-- Main Stats --}}
    {{ $this->getStats() }}

    {{-- Events Breakdown Modal --}}
    <x-filament::modal
        id="events-breakdown"
        width="3xl"
        slide-over
    >
        <x-slot name="heading">
            Events Detailed Breakdown
        </x-slot>

        @php
            $totalEvents = Event::count();
            $activeEvents = Event::where('end_date', '>=', now())->where('start_date', '<=', now())->count();
            $upcomingEvents = Event::where('start_date', '>', now())->count();
            $endedEvents = Event::where('end_date', '<', now())->count();

            $eventsByMonth = Event::selectRaw('MONTH(start_date) as month, COUNT(*) as count')
                ->whereYear('start_date', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();
        @endphp

        <div class="space-y-6">
            <!-- Event Status Summary -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-900">{{ $activeEvents }}</div>
                    <div class="text-sm text-blue-600">Active Events</div>
                </div>
                <div class="bg-amber-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-amber-900">{{ $upcomingEvents }}</div>
                    <div class="text-sm text-amber-600">Upcoming Events</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $endedEvents }}</div>
                    <div class="text-sm text-gray-600">Ended Events</div>
                </div>
            </div>

            <!-- Monthly Distribution -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Events by Month ({{ now()->year }})</h4>
                <div class="space-y-2">
                    @for($i = 1; $i <= 12; $i++)
                        @php
                            $monthName = Carbon\Carbon::create()->month($i)->format('M');
                            $count = $eventsByMonth[$i] ?? 0;
                            $percentage = $totalEvents > 0 ? ($count / $totalEvents) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $monthName }}</span>
                                <span class="font-medium">{{ $count }} events</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Recent Events -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Recent Events</h4>
                <div class="space-y-2">
                    @foreach(Event::latest()->limit(5)->get() as $event)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">{{ $event->name }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $event->start_date->format('M d') }} - {{ $event->end_date->format('M d, Y') }}
                                </div>
                            </div>
                            <x-filament::badge
                                :color="$event->end_date < now() ? 'gray' : ($event->start_date > now() ? 'warning' : 'success')"
                                size="sm"
                            >
                                {{ $event->end_date < now() ? 'Ended' : ($event->start_date > now() ? 'Upcoming' : 'Active') }}
                            </x-filament::badge>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button
                color="gray"
                wire:click="$dispatch('close-modal', { id: 'events-breakdown' })"
            >
                Close
            </x-filament::button>
            <x-filament::button
                color="primary"
                :href="route('filament.admin.resources.events.index')"
            >
                View All Events
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Stands Breakdown Modal --}}
    <x-filament::modal
        id="stands-breakdown"
        width="3xl"
        slide-over
    >
        <x-slot name="heading">
            Stands Detailed Breakdown
        </x-slot>

        @php
            $totalStands = Stand::count();
            $soldStands = Stand::where('status', 'Sold')->count();
            $availableStands = Stand::where('status', 'Available')->count();
            $reservedStands = Stand::where('status', 'Reserved')->count();
            $mergedStands = Stand::where('is_merged', true)->count();

            $totalSpace = Stand::sum('space');
            $soldSpace = Stand::where('status', 'Sold')->sum('space');
            $availableSpace = Stand::where('status', 'Available')->sum('space');
            $reservedSpace = Stand::where('status', 'Reserved')->sum('space');

            $avgSpacePerStand = $totalStands > 0 ? round($totalSpace / $totalStands, 1) : 0;
        @endphp

        <div class="space-y-6">
            <!-- Stand Status Summary -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-900">{{ $availableStands }}</div>
                    <div class="text-sm text-green-600">Available</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-red-900">{{ $soldStands }}</div>
                    <div class="text-sm text-red-600">Sold</div>
                </div>
                <div class="bg-amber-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-amber-900">{{ $reservedStands }}</div>
                    <div class="text-sm text-amber-600">Reserved</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-900">{{ $mergedStands }}</div>
                    <div class="text-sm text-purple-600">Merged</div>
                </div>
            </div>

            <!-- Space Distribution -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Space Distribution</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-green-600">Available: {{ number_format($availableSpace) }} sqm</span>
                            <span class="font-medium">{{ $totalSpace > 0 ? round(($availableSpace / $totalSpace) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-500 h-3 rounded-full"
                                 style="width: {{ $totalSpace > 0 ? ($availableSpace / $totalSpace) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-red-600">Sold: {{ number_format($soldSpace) }} sqm</span>
                            <span class="font-medium">{{ $totalSpace > 0 ? round(($soldSpace / $totalSpace) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-red-500 h-3 rounded-full"
                                 style="width: {{ $totalSpace > 0 ? ($soldSpace / $totalSpace) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-amber-600">Reserved: {{ number_format($reservedSpace) }} sqm</span>
                            <span class="font-medium">{{ $totalSpace > 0 ? round(($reservedSpace / $totalSpace) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-amber-500 h-3 rounded-full"
                                 style="width: {{ $totalSpace > 0 ? ($reservedSpace / $totalSpace) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-xl font-bold text-gray-900">{{ number_format($totalSpace) }}</div>
                    <div class="text-sm text-gray-600">Total Space</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-xl font-bold text-gray-900">{{ $avgSpacePerStand }}</div>
                    <div class="text-sm text-gray-600">Avg. Space per Stand</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-xl font-bold text-gray-900">{{ $totalStands }}</div>
                    <div class="text-sm text-gray-600">Total Stands</div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button
                color="gray"
                wire:click="$dispatch('close-modal', { id: 'stands-breakdown' })"
            >
                Close
            </x-filament::button>
            <x-filament::button
                color="primary"
                :href="route('filament.admin.resources.stands.index')"
            >
                View All Stands
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Contracts Breakdown Modal --}}
    <x-filament::modal
        id="contracts-breakdown"
        width="3xl"
        slide-over
    >
        <x-slot name="heading">
            Contracts Detailed Breakdown
        </x-slot>

        @php
            $totalContracts = Contract::count();
            $draftContracts = Contract::where('status', 'draft')->count();
            $interestedContracts = Contract::where('status', 'INT')->count();
            $signedNotPaidContracts = Contract::where('status', 'S&NP')->count();
            $signedPaidContracts = Contract::where('status', 'S&P')->count();

            $totalRevenue = Contract::where('status', 'S&P')->sum('net_total');
            $pendingRevenue = Contract::where('status', 'S&NP')->sum('net_total');
            $potentialRevenue = Contract::whereIn('status', ['draft', 'INT'])->sum('net_total');

            $avgContractValue = $signedPaidContracts > 0 ? round($totalRevenue / $signedPaidContracts, 2) : 0;

            $contractsByMonth = Contract::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray();
        @endphp

        <div class="space-y-6">
            <!-- Contract Status Summary -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $draftContracts }}</div>
                    <div class="text-sm text-gray-600">Draft</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-900">{{ $interestedContracts }}</div>
                    <div class="text-sm text-blue-600">Interested</div>
                </div>
                <div class="bg-amber-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-amber-900">{{ $signedNotPaidContracts }}</div>
                    <div class="text-sm text-amber-600">Not Paid</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-900">{{ $signedPaidContracts }}</div>
                    <div class="text-sm text-green-600">Paid</div>
                </div>
            </div>

            <!-- Revenue Breakdown -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Revenue Breakdown</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div class="text-sm font-medium text-green-600">Collected Revenue</div>
                                <div class="text-2xl font-bold text-green-900">${{ number_format($totalRevenue, 2) }}</div>
                            </div>
                            <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php
                                $totalAllRevenue = $totalRevenue + $pendingRevenue + $potentialRevenue;
                                $collectedPercentage = $totalAllRevenue > 0 ? ($totalRevenue / $totalAllRevenue) * 100 : 0;
                            @endphp
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $collectedPercentage }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div class="text-sm font-medium text-amber-600">Pending Revenue</div>
                                <div class="text-2xl font-bold text-amber-900">${{ number_format($pendingRevenue, 2) }}</div>
                            </div>
                            <x-heroicon-o-clock class="h-8 w-8 text-amber-500" />
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $totalAllRevenue > 0 ? ($pendingRevenue / $totalAllRevenue) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <div class="text-sm font-medium text-blue-600">Potential Revenue</div>
                                <div class="text-2xl font-bold text-blue-900">${{ number_format($potentialRevenue, 2) }}</div>
                            </div>
                            <x-heroicon-o-eye class="h-8 w-8 text-blue-500" />
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalAllRevenue > 0 ? ($potentialRevenue / $totalAllRevenue) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Contract Activity -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Monthly Contract Activity ({{ now()->year }})</h4>
                <div class="space-y-2">
                    @for($i = 1; $i <= 12; $i++)
                        @php
                            $monthName = Carbon\Carbon::create()->month($i)->format('M');
                            $count = $contractsByMonth[$i] ?? 0;
                        @endphp
                        <div class="flex items-center">
                            <div class="w-20 text-sm text-gray-600">{{ $monthName }}</div>
                            <div class="flex-1">
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-blue-500 h-3 rounded-full"
                                         style="width: {{ $totalContracts > 0 ? ($count / $totalContracts) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="w-12 text-right text-sm font-medium">{{ $count }}</div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <x-filament::button
                color="gray"
                wire:click="$dispatch('close-modal', { id: 'contracts-breakdown' })"
            >
                Close
            </x-filament::button>
            <x-filament::button
                color="primary"
                :href="route('filament.admin.resources.contracts.index')"
            >
                View All Contracts
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
