<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-stone-200 shadow-sm transition ease-in-out duration-150 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
