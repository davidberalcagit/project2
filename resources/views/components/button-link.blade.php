@props(['href', 'variant' => 'primary'])

@once
    <style>
        .action-button {
            display: inline-block;
            padding: 6px 20px;
            border: 1px solid transparent;
            border-radius: 2px;
            color: #1b1b18;
            font-size: 14px;
            line-height: 20px;
            text-decoration: none;
            transition: background-color .15s, border-color .15s;
        }

        .action-button--primary {
            border-color: #1b1b18;
            background: #1b1b18;
            color: #fff;
        }

        .action-button--primary:hover {
            border-color: #000;
            background: #000;
        }

        .action-button--secondary:hover { border-color: #19140035; }

        .site-layout .action-button { color: #263b30; }
        .site-layout .action-button--primary {
            margin-top: 12px;
            padding: 12px 18px;
            border-color: #264b3c;
            border-radius: 6px;
            background: #264b3c;
            color: #fff;
        }

        .site-layout .action-button--primary:hover {
            border-color: #1d392e;
            background: #1d392e;
        }

        .site-layout .action-button--secondary:hover { border-color: #e1ddd3; }
    </style>
@endonce

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'action-button action-button--'.$variant]) }}>
    {{ $slot }}
</a>
