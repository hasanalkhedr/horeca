<!-- resources/views/components/sidebar-link.blade.php -->
@props(['href', 'icon', 'label'])

<a {{$attributes}} href="{{ $href }}" class="flex items-center p-1 m-1 hover:bg-gray-200 rounded-md
{{ request()->is(strtolower($label)) ? 'bg-gray-500 text-white hover:text-gray-400' : 'text-gray-600 hover:bg-gray-200' }}">
    <!-- Icon -->
    <span class="inline-flex items-center justify-center w-8 h-8 ">
        <i class="{{ $icon }}"></i>
    </span>
    <!-- Label (shown only when sidebar is expanded) -->
    <span x-show="showSidebar" class="ml-2 " x-cloak>{{ $label }}</span>
    @isset($subIcon) <!-- Check if the subIcon slot is set -->
            {{ $subIcon }}
    @endisset
</a>
{{$slot}}
