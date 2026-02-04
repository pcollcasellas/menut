@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-bark-700']) }}>
    {{ $value ?? $slot }}
</label>
