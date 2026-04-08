@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-amber-300 text-start text-base font-medium text-white bg-white/5 focus:outline-none focus:text-white focus:bg-white/10 focus:border-amber-200 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-stone-400 hover:text-stone-100 hover:bg-white/5 hover:border-white/20 focus:outline-none focus:text-stone-100 focus:bg-white/5 focus:border-white/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
