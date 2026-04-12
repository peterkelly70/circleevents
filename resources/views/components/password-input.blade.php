<div x-data="{ show: false }" class="relative">
    <input 
        :type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-white/5 text-stone-100 placeholder:text-stone-500 shadow-sm focus:border-amber-300 focus:ring-amber-300 w-full pr-10']) }}
    >
    <button 
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-200 text-sm"
        @click="show = !show"
    >
        <span x-show="!show">👁️</span>
        <span x-show="show">🔒</span>
    </button>
</div>