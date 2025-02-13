<div class="flex items-center space-x-2">
    <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" wire:model.live="{{ $model }}" value="{{ $value }}"
               class="sr-only peer" {{ $disabled ? 'disabled' : '' }}>
        <div class="w-11 h-6 bg-gray-300 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer
                    peer-checked:after:translate-x-full peer-checked:after:border-white
                    after:content-[''] after:absolute after:top-0.5 after:left-1 after:bg-white
                    after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                    after:transition-all peer-checked:bg-blue-600"></div>
    </label>
    <span class="text-gray-700 text-sm">{{ ucwords(str_replace('_', ' ', $value)) }}</span>
</div>
