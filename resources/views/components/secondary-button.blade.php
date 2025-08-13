<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-church-300 rounded-lg font-semibold text-xs text-church-700 uppercase tracking-widest shadow-sm hover:bg-church-50 focus:outline-none focus:ring-2 focus:ring-church-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
