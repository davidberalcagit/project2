<a {{ $attributes->merge(['href' => route('home'), 'class' => 'site-brand']) }}>
    <span class="site-logo" aria-hidden="true">
        <img src="{{ asset('storage/logos/'.rawurlencode('Fondo_beige_copy_2-removebg-preview.png')) }}" alt="">
    </span>
    <span>BibloBook</span>
</a>
