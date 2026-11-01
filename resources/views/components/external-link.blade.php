@props(['href', 'target' => '_blank'])

<a href="{{ $href }}" target="{{ $target }}" {{ $attributes->merge(['rel' => 'noopener noreferrer', 'class' => 'inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433] ml-1']) }}>
    <span>{{ $slot }}</span>
    <svg
        width="10"
        height="11"
        viewBox="0 0 10 11"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="w-2.5 h-2.5"
        aria-hidden="true"
    >
        <path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square"/>
    </svg>
</a>
