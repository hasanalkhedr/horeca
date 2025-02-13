<!-- resources/views/components/sidebar-large.blade.php -->
<div x-data="{ sideOpen: true, userSubMenuOpen: false, settingsMenu: false, spaceManagementMenu: false, sponsorshipManagementMenu: false, clientsMenu: false, contractsMenu: false }"
    class="hidden lg:flex flex-shrink-0 bg-gray-100 shadow-md h-full border-r border-gray-100 pb-20"
    x-bind:class="{ 'w-30': !sideOpen, 'w-1/6': sideOpen }">
    <!-- Sidebar links -->
    <div class="flex flex-col h-full w-full">
        <!-- Toggle button for expanding/collapsing the sidebar -->
        <button @click="sideOpen = !sideOpen" class="p-2 focus:outline-none hover:bg-gray-200">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <x-sidebar-link href="{{route('dashboard')}}" icon="fas fa-tachometer-alt" label="Dashboard" />
        <x-sidebar-link href="{{ route('events.index') }}" icon="fas fa-calendar-alt" label="Events" />
        <x-sidebar-link href="" icon="fa-solid fa-users-gear" label="Clients" @click.prevent="clientsMenu=!clientsMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': clientsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                    fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="clientsMenu" x-transition class="pl-4">
                <x-sidebar-link href="{{ route('companies.index') }}" icon="fas fa-building" label="Companies" />
                <x-sidebar-link href="{{ route('brands.index') }}" icon="fa-brands fa-font-awesome" label="Brands" />
                <x-sidebar-link href="{{ route('clients.index') }}" icon="fa-sharp fa-solid fa-users" label="Persons" />
            </div>
        </x-sidebar-link>
        <!-- Space Management Menu -->
        <x-sidebar-link href="" icon="fas fa-th-large" label="Space Management"
            @click.prevent="spaceManagementMenu=!spaceManagementMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': spaceManagementMenu }"
                    class="ml-auto mr-2 transition-transform h-6 w-6" fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="spaceManagementMenu" x-transition class="pl-4">
                <x-sidebar-link href="{{ route('stands.index') }}" label="Stands" icon="fa fa-as fa-store" />
            </div>
        </x-sidebar-link>
        <x-sidebar-link href="" icon="fa-solid fa-hand-holding-dollar" label="Sponsorship Management"
            @click.prevent="sponsorshipManagementMenu=!sponsorshipManagementMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': sponsorshipManagementMenu }"
                    class="ml-auto mr-2 transition-transform h-6 w-6" fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="sponsorshipManagementMenu" x-transition class="pl-4">
                <x-sidebar-link href="{{ route('sponsor_packages.index') }}" label="Sponsorship Packages"
                    icon="fa-solid fa-cube" />
                <x-sidebar-link href="{{ route('sponsor_options.index') }}" label="Sponsorship Options"
                    icon="fa-solid fa-filter-circle-dollar" />
            </div>
        </x-sidebar-link>
        <x-sidebar-link href="" icon="fas fa-file-contract" label="Contracts"
            @click.prevent="contractsMenu=!contractsMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': contractsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                    fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="contractsMenu" x-transition class="pl-4">
                <x-sidebar-link href="{{ route('reports.index') }}" label="Contract Templates" icon="fas fa-file-contract" />
                <x-sidebar-link href="{{ route('contracts.index') }}" label="Contracts" icon="fas fa-file-contract" />
            </div>
        </x-sidebar-link>
        <x-sidebar-link href="" icon="fas fa-cog" label="Settings"
            @click.prevent="settingsMenu=!settingsMenu">
            <x-slot name="subIcon">
                <svg x-bind:class="{ 'rotate-180': settingsMenu }" class="ml-auto mr-2 transition-transform h-6 w-6"
                    fill="currentColor">
                    <path d="M6 15l6-6 6 6H6z" />
                </svg>
            </x-slot>
            <div x-show="settingsMenu" x-transition class="pl-4">
                {{-- <x-sidebar-link href="{{ route('payment_rates.index') }}" label="Payment Rates" icon="fa fa-pay" />
                <x-sidebar-link href="{{ route('bank_accounts.index') }}" label="Bank Accounts" icon="fa fa-pay" /> --}}
                <x-sidebar-link href="{{ route('categories.index') }}" label="Categories" icon="fa-solid fa-layer-group" />
                <x-sidebar-link href="{{ route('currencies.index') }}" label="Currencies" icon="fa-solid fa-coins" />
                <x-sidebar-link href="{{ route('prices.index') }}" label="Prices" icon="fa-solid fa-money-bill-1-wave" />
            </div>
        </x-sidebar-link>
    </div>
</div>
