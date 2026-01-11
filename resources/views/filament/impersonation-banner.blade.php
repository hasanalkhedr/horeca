@if(session()->has('impersonator_id'))
    <div class="bg-amber-50 border-b border-amber-200 px-4 py-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-600 mr-2" />
                <span class="text-sm text-amber-800">
                    You are impersonating
                    <span class="font-semibold">{{ auth()->user()->name }}</span>
                </span>
            </div>
            <form action="{{ route('filament.admin.auth.logout') }}" method="POST">
                @csrf
                <input type="hidden" name="impersonator_logout" value="1">
                <x-filament::button
                    type="submit"
                    color="amber"
                    size="xs"
                    icon="heroicon-o-arrow-left"
                >
                    Return to Admin
                </x-filament::button>
            </form>
        </div>
    </div>
@endif
