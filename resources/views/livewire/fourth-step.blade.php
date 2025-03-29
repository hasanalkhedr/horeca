<div class="">
    <!-- Sponsorship Packages -->
    <div class="w-full px-4 mx-4">
        <h2>Sponsorship Packages</h2>
        <div class="flex w-full">
            <div class="w-1/2">
                <div x-data="{ all_packages: @entangle('state.all_packages') }">
                    <h1>Available Packages</h1>
                    <template x-for="package in JSON.parse(all_packages)">
                        <div x-data="{ open: false }" class="border rounded-md">
                            <button @click="open = !open" type="button"
                                class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                                <span class="font-medium text-gray-700" x-text="package.title"></span>
                                <svg :class="{ 'transform rotate-180': open }"
                                    class="w-5 h-5 text-gray-500 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse class="p-3 space-y-2">
                                <div class="text-sm text-gray-600">
                                    <template x-for="opt in package.sponsor_options">
                                        <li><span x-text="opt.title"></span></li>
                                    </template>
                                </div>
                                <button wire:click="addPackageToEvent(package)" type="button"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                    Add this package to the event
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="w-1/2">
                <div x-data="{ event_packages: @entangle('state.event_packages') }">
                    <h1>Currently Event Packages</h1>
                    <template x-for="package in JSON.parse(event_packages)">
                        <div x-data="{ open: false }" class="border rounded-md">
                            <button @click="open = !open" type="button"
                                class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                                <span class="font-medium text-gray-700" x-text="package.title"></span>
                                <svg :class="{ 'transform rotate-180': open }"
                                    class="w-5 h-5 text-gray-500 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse class="p-3 space-y-2">
                                <div class="text-sm text-gray-600">
                                    <template x-for="opt in package.sponsor_options">
                                        <li><span x-text="opt.title"></span></li>
                                    </template>
                                </div>
                                <button wire:click="removePackageFromEvent(package)" type="button"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                    Remove this package from event
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
    </div>

    <!-- Advertisement Packages -->
    <div class="w-full px-4 mx-4">
        <h2>Advertisement Packages</h2>
        <div class="flex w-full">
            <div class="w-1/2">
                <div x-data="{ all_ads_packages: @entangle('state.all_ads_packages') }">
                    <h1>Available Packages</h1>
                    <template x-for="package in JSON.parse(all_ads_packages)">
                        <div x-data="{ open: false }" class="border rounded-md">
                            <button @click="open = !open" type="button"
                                class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                                <span class="font-medium text-gray-700" x-text="package.title"></span>
                                <svg :class="{ 'transform rotate-180': open }"
                                    class="w-5 h-5 text-gray-500 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse class="p-3 space-y-2">
                                <div class="text-sm text-gray-600">
                                    <template x-for="opt in package.ads_options">
                                        <li><span x-text="opt.title"></span></li>
                                    </template>
                                </div>
                                <button wire:click="addAdsPackageToEvent(package)" type="button"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                    Add this package to the event
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="w-1/2">
                <div x-data="{ event_ads_packages: @entangle('state.event_ads_packages') }">
                    <h1>Currently Event Packages</h1>
                    <template x-for="package in JSON.parse(event_ads_packages)">
                        <div x-data="{ open: false }" class="border rounded-md">
                            <button @click="open = !open" type="button"
                                class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center">
                                <span class="font-medium text-gray-700" x-text="package.title"></span>
                                <svg :class="{ 'transform rotate-180': open }"
                                    class="w-5 h-5 text-gray-500 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse class="p-3 space-y-2">
                                <div class="text-sm text-gray-600">
                                    <template x-for="opt in package.ads_options">
                                        <li><span x-text="opt.title"></span></li>
                                    </template>
                                </div>
                                <button wire:click="removeAdsPackageFromEvent(package)" type="button"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                                    Remove this package from event
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <hr class="h-0.5 my-1 bg-gray-500 w-full mx-4" />
    </div>
</div>
