@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-white/5 text-stone-100 placeholder:text-stone-500 shadow-sm focus:border-amber-300 focus:ring-amber-300']) }}>
