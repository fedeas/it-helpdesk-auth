@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Computerland IT Services" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square items-center justify-center rounded-md overflow-hidden">
            <img src="{{ asset('images/logo_short.png') }}" width="100%" height="100%" alt="Computerland VD IT Services" class=" object-cover rounded-md">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Computerland IT Services" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square items-center justify-center rounded-md overflow-hidden">
            <img src="{{ asset('images/logo_short.png') }}" width="100%" height="100%" alt="Computerland VD IT Services" class="object-cover rounded-md">
        </x-slot>
    </flux:brand>
@endif
