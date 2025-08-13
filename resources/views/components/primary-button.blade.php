<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-church-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-church-600 focus:bg-church-600 active:bg-church-700 focus:outline-none focus:ring-2 focus:ring-church-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
