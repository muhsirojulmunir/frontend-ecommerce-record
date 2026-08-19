<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center bg-danger hover:bg-accent-light text-white text-xs font-bold px-6 py-3 rounded-sm uppercase tracking-wide transition focus:outline-none focus:ring-2 focus:ring-danger focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
