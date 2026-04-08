<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-full bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 hover:bg-rose-500 active:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 focus:ring-offset-stone-950']) }}>
    {{ $slot }}
</button>
