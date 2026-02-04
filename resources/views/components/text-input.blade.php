@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-cream-300 text-bark-800 placeholder:text-bark-400 focus:border-forest-400 focus:ring-2 focus:ring-forest-100 rounded-xl shadow-inner-soft transition-all duration-200 hover:border-sage-300']) }}>
