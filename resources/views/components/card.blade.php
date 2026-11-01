@props(['variant' => 'default'])

@once
    <style>
        .content-card {
            margin: 16px 16px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e1ddd3;
            background: #fffefa;
        }

        .content-card--intro { background: #e8dfcd; }
        .book-grid .content-card { margin: 0; }
    </style>
@endonce

<div {{ $attributes->merge(['class' => 'content-card content-card--'.$variant]) }}>
    {{ $slot }}
</div>
