<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2.5 bg-white border border-cream-300 rounded-xl font-semibold text-sm text-bark-700 tracking-wide shadow-sm hover:bg-cream-50 hover:border-sage-300 focus:outline-none focus:ring-2 focus:ring-sage-400 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-200']) }}>
    {{ $slot }}
</button>
