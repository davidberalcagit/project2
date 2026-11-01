@props(['type' => 'text'])

<x-form-styles />

<input type="{{ $type }}" {{ $attributes->merge(['class' => 'form-control']) }}>
