<!-- resources/views/components/sidebar-mobile.blade.php -->
<div x-data="{ showSidebar: false,settingsMenu: false, spaceManagementMenu: false, sponsorshipManagementMenu: false, clientsMenu: false, contractsMenu: false }" class="lg:hidden">
    <!-- Toggle button for showing/hiding the mobile sidebar -->
    <button @click="showSidebar = !showSidebar" class="p-4 focus:outline-none hover:bg-gray-200">
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': showSidebar, 'inline-flex':  !showSidebar }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            <path :class="{'hidden':  !showSidebar, 'inline-flex': showSidebar }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <!-- Sidebar overlay (to close the sidebar when clicking outside) -->
    <div x-show="showSidebar" class="fixed inset-0 z-40 bg-black bg-opacity-50" style="display: none;"></div>

    <!-- Mobile sidebar (only shown when `showSidebar` is true) -->
    <div x-show="showSidebar" class="fixed inset-y-0 left-0 w-64 bg-gray-100 shadow-md z-50 transform transition-transform duration-200"
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         style="display: none;">

        <!-- Close button inside the sidebar -->
        <button @click="showSidebar = false" class="p-4 focus:outline-none hover:bg-gray-200">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': showSidebar, 'inline-flex': ! showSidebar }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                <path :class="{'hidden': ! showSidebar, 'inline-flex': showSidebar }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Sidebar links -->
        <div class="flex flex-col h-full p-4">
            <x-sidebar-mobile-link href="{{route('dashboard')}}" icon="fas fa-tachometer-alt" label="Dashboard" />
        <x-sidebar-mobile-link href="{{ route('events.index') }}" icon="fas fa-calendar-alt" label="Events" />
        <x-sidebar-mobile-link href="" icon="fa-solid fa-users-gear" label="Clients" @click.prevent="clientsMenu=!clientsMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': clientsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                    fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="clientsMenu" x-transition class="pl-4">
                <x-sidebar-mobile-link href="{{ route('companies.index') }}" icon="fas fa-building" label="Companies" />
                <x-sidebar-mobile-link href="{{ route('brands.index') }}" icon="fa-brands fa-font-awesome" label="Brands" />
                <x-sidebar-mobile-link href="{{ route('clients.index') }}" icon="fa-sharp fa-solid fa-users" label="Persons" />
            </div>
        </x-sidebar-mobile-link>
        <!-- Space Management Menu -->
        <x-sidebar-mobile-link href="" icon="fas fa-th-large" label="Space Management"
            @click.prevent="spaceManagementMenu=!spaceManagementMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': spaceManagementMenu }"
                    class="ml-auto mr-2 transition-transform h-6 w-6" fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="spaceManagementMenu" x-transition class="pl-4">
                <x-sidebar-mobile-link href="{{ route('stands.index') }}" label="Stands" icon="fa fa-as fa-store" />
            </div>
        </x-sidebar-mobile-link>
        <x-sidebar-mobile-link href="" icon="fa-solid fa-hand-holding-dollar" label="Sponsorship Management"
            @click.prevent="sponsorshipManagementMenu=!sponsorshipManagementMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': sponsorshipManagementMenu }"
                    class="ml-auto mr-2 transition-transform h-6 w-6" fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="sponsorshipManagementMenu" x-transition class="pl-4">
                <x-sidebar-mobile-link href="{{ route('sponsor_packages.index') }}" label="Sponsorship Packages"
                    icon="fa-solid fa-cube" />
                <x-sidebar-mobile-link href="{{ route('sponsor_options.index') }}" label="Sponsorship Options"
                    icon="fa-solid fa-filter-circle-dollar" />
            </div>
        </x-sidebar-mobile-link>
        <x-sidebar-mobile-link href="" icon="fas fa-file-contract" label="Contracts" @click.prevent="contractsMenu=!contractsMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': contractsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                    fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="contractsMenu" x-transition class="pl-4">
                <x-sidebar-mobile-link href="{{ route('reports.index') }}" label="Contract Templates" icon="fas fa-file-contract" />
                <x-sidebar-mobile-link href="{{ route('contracts.index') }}" label="Contracts" icon="fas fa-file-contract" />
            </div>
        </x-sidebar-mobile-link>
        <x-sidebar-mobile-link href="" icon="fas fa-cog" label="Settings"
            @click.prevent="settingsMenu=!settingsMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': settingsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                    fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="settingsMenu" x-transition class="pl-4">
                {{-- <x-sidebar-mobile-link href="{{ route('payment_rates.index') }}" label="Payment Rates" icon="fa fa-pay" />
                <x-sidebar-mobile-link href="{{ route('bank_accounts.index') }}" label="Bank Accounts" icon="fa fa-pay" /> --}}
                <x-sidebar-mobile-link href="{{ route('categories.index') }}" label="Categories" icon="fa-solid fa-layer-group" />
                <x-sidebar-mobile-link href="{{ route('currencies.index') }}" label="Currencies" icon="fa-solid fa-coins" />
                <x-sidebar-mobile-link href="{{ route('prices.index') }}" label="Prices" icon="fa-solid fa-money-bill-1-wave" />
            </div>
        </x-sidebar-mobile-link>

            {{-- <x-sidebar-mobile-link href="#" icon="fas fa-tachometer-alt" label="Dashboard" />
            <x-sidebar-mobile-link href="{{route('events.index')}}" icon="fas fa-calendar-alt" label="Events" />
            <x-sidebar-mobile-link href="{{route('companies.index')}}" icon="fas fa-building" label="Companies" />
            <x-sidebar-mobile-link href="{{route('brands.index')}}" icon="fas fa-logo" label="Brands" />
            <x-sidebar-mobile-link href="{{route('clients.index')}}" icon="fas fa-client" label="Clients" />
            <x-sidebar-mobile-link href="" icon="fas fa-th-large" label="Space Management" @click.prevent="spaceManagementMenu=!spaceManagementMenu">
                <x-slot name="subIcon">
                    <svg x-bind:class="{ 'rotate-180': spaceManagementMenu }"
                        class="ml-auto mr-2 transition-transform h-6 w-6" fill="currentColor">
                        <path d="M6 15l6-6 6 6H6z" />
                    </svg>
                </x-slot>
                <div x-show="spaceManagementMenu" x-transition class="pl-4">
                    <x-sidebar-mobile-link href="{{ route('stands.index') }}" label="Stands" icon="fa fa-as fa-store" />
                </div>
            </x-sidebar-mobile-link>
            <x-sidebar-mobile-link href="" icon="fas fa-th-sponsor" label="Sponsorship Management" @click.prevent="sponsorshipManagementMenu=!sponsorshipManagementMenu">
                <x-slot name="subIcon">
                    <svg x-bind:class="{ 'rotate-180': sponsorshipManagementMenu }"
                        class="ml-auto mr-2 transition-transform h-6 w-6" fill="currentColor">
                        <path d="M6 15l6-6 6 6H6z" />
                    </svg>
                </x-slot>
                <div x-show="sponsorshipManagementMenu" x-transition class="pl-4">
                    <x-sidebar-mobile-link href="{{ route('sponsor_packages.index') }}" label="Sponsorship Packages" icon="fa fa-package" />
                    <x-sidebar-mobile-link href="{{ route('sponsor_options.index') }}" label="Sponsorship Options" icon="fa fa-option" />
                </div>
            </x-sidebar-mobile-link>
            <x-sidebar-mobile-link href="#" icon="fas fa-file-contract" label="Contracts" />
            <x-sidebar-mobile-link href="" icon="fas fa-settings" label="Settings" @click.prevent="settingsMenu=!settingsMenu">
                <x-slot name="subIcon">
                    <svg x-bind:class="{ 'rotate-180': settingsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                        fill="currentColor">
                        <path d="M6 15l6-6 6 6H6z" />
                    </svg>
                </x-slot>
                <div x-show="settingsMenu" x-transition class="pl-4">
                    <x-sidebar-mobile-link href="{{ route('payment_rates.index') }}" label="Payment Rates" icon="fa fa-pay" />
                    <x-sidebar-mobile-link href="{{ route('bank_accounts.index') }}" label="Bank Accounts" icon="fa fa-pay" />
                    <x-sidebar-mobile-link href="{{ route('categories.index') }}" label="Categories" icon="fa fa-pay" />
                    <x-sidebar-mobile-link href="{{ route('currencies.index') }}" label="Currencies" icon="fa fa-pay" />
                    <x-sidebar-mobile-link href="{{ route('prices.index') }}" label="Prices" icon="fa fa-pay" />
                </div>
            </x-sidebar-mobile-link> --}}
        </div>
    </div>
</div>
