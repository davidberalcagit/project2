<x-form-styles />

<label {{ $attributes->merge(['class' => 'form-label']) }}>
    {{ $slot }}
</label>
