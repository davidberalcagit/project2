@props(['href'])

@once
    <style>
        .site-nav-link {
            text-decoration: none;
        }

        .site-nav-link:hover { text-decoration: none; }
    </style>
@endonce

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'site-nav-link']) }}>
    {{ $slot }}
</a>
