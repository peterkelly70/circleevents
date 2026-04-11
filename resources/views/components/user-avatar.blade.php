@props([
    'user',
    'size' => 'md',
    'shell' => 'border-white/10 bg-white/5 text-stone-100',
])

@php
    $sizeClass = [
        'sm' => 'h-9 w-9 text-xs',
        'md' => 'h-11 w-11 text-sm',
        'lg' => 'h-14 w-14 text-base',
    ][$size] ?? 'h-11 w-11 text-sm';
@endphp

<div {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center overflow-hidden rounded-full border font-bold {$sizeClass} {$shell}"]) }}>
    @if ($user->avatarUrl())
        <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }} avatar" class="h-full w-full object-cover">
    @else
        <span>{{ $user->avatarInitials() }}</span>
    @endif
</div>
