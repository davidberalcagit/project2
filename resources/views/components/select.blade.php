<x-form-styles />

<select {{ $attributes->merge(['class' => 'form-control form-select']) }}>
    {{ $slot }}
</select>
