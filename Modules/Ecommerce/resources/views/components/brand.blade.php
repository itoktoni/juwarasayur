{{-- Brand Juwara Sayur: override warna tema (Tailwind v4 memakai CSS variables) --}}
<style>
    :root {
        --color-primary: #388e3c;            /* forest green logo */
        --color-on-primary: #ffffff;
        --color-primary-container: #2e7d32;  /* hijau gelap */
        --color-on-primary-container: #ffffff;
        --color-primary-fixed: #a5d6a7;
        --color-primary-fixed-dim: #81c784;
        --color-on-primary-fixed: #0f3d11;
        --color-on-primary-fixed-variant: #1b5e20;
        --color-secondary: #f5b301;          /* kuning emas mahkota */
        --color-on-secondary: #1f2a17;
        --color-secondary-container: #fdd835;
        --color-on-secondary-container: #26300b;
        --color-secondary-fixed: #ffecb3;
        --color-secondary-fixed-dim: #ffd54f;
        --color-on-secondary-fixed: #26300b;
        --color-on-secondary-fixed-variant: #5b4300;
        --color-surface-tint: #2e7d32;
        --color-inverse-primary: #81c784;
    }
</style>
<img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="hidden">
