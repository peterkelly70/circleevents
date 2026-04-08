@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-stone-300']) }}>
    {{ $value ?? $slot }}
</label>
