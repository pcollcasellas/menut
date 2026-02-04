<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-terracotta-600 border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide hover:bg-terracotta-700 active:bg-terracotta-800 focus:outline-none focus:ring-2 focus:ring-terracotta-500 focus:ring-offset-2 transition ease-in-out duration-200']) }}>
    {{ $slot }}
</button>
