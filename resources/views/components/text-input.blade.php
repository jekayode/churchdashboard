@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-church-500 focus:ring-church-500 rounded-lg shadow-sm']) }}>
