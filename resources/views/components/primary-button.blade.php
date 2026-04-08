<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full bg-amber-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-stone-950 transition ease-in-out duration-150 hover:bg-amber-200 focus:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-stone-950 active:bg-amber-400']) }}>
    {{ $slot }}
</button>
