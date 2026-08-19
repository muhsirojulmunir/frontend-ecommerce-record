<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center bg-white border border-border text-text text-xs font-bold px-6 py-3 rounded-sm uppercase tracking-wide hover:bg-bg-secondary transition focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
