@extends('layouts.app')
@section('title', 'Hotspot Billing System Kenya | ISP Billing Software')
@section('content')
@php
    $heroImage = $content['home_hero_image'];
    $heroImageUrl = str_starts_with($heroImage, 'http') ? $heroImage : asset('storage/'.$heroImage);
@endphp
<section class="hero section" style="--hero-image:url('{{ $heroImageUrl }}')">
    <div class="container py-4">
        <div class="row align-items-center g-4">
            <div class="{{ $homeVideoEmbedUrl ? 'col-lg-7' : 'col-lg-8' }}">
                <h1 class="display-4 fw-bold">{{ $content['home_hero_title'] }}</h1>
                <p class="lead mb-4">{{ $content['home_hero_description'] }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-brand btn-lg" href="{{ route('bookings.create') }}">{{ $content['home_primary_cta'] }}</a>
                    <a class="btn btn-light btn-lg" href="{{ route('register') }}">{{ $content['home_provider_cta'] }}</a>
                    <a class="btn btn-outline-light btn-lg" href="{{ $content['home_whatsapp_url'] }}">{{ $content['home_whatsapp_cta'] }}</a>
                </div>
            </div>
            @if($homeVideoEmbedUrl)
                <div class="col-lg-5">
                    <div class="hero-video">
                        <div class="ratio ratio-16x9 rounded overflow-hidden">
                            <iframe src="{{ $homeVideoEmbedUrl }}" title="Hotspot Billing System video" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
<section class="section services-kingdom" id="services">
    <div class="container">
        <h2 class="section-title">{{ $content['home_services_title'] }}</h2>
        @if($content['home_services_subtitle'])
            <p class="text-center text-muted lead mb-5">{{ $content['home_services_subtitle'] }}</p>
        @endif
        <a class="kingdom-arrow left" href="#services" aria-label="Previous services">&lsaquo;</a>
        <a class="kingdom-arrow right" href="#services" aria-label="Next services">&rsaquo;</a>
        <div class="row g-4 justify-content-center">
            @foreach($popularServices as $service)
                <div class="col-md-6 col-lg-4">
                    <article class="kingdom-service-card h-100">
                        <div class="kingdom-check">✓</div>
                        <h5>{{ $service->name }}</h5>
                        <p>{{ $service->description }}</p>
                        <div class="kingdom-price">{{ $service->unit_type }}</div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
<section class="section bg-white" id="how-it-works">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4"><h3>{{ $content['home_how_title_1'] }}</h3><p>{{ $content['home_how_text_1'] }}</p></div>
            <div class="col-md-4"><h3>{{ $content['home_how_title_2'] }}</h3><p>{{ $content['home_how_text_2'] }}</p></div>
            <div class="col-md-4"><h3>{{ $content['home_how_title_3'] }}</h3><p>{{ $content['home_how_text_3'] }}</p></div>
        </div>
    </div>
</section>
<section class="section" id="why-choose-us">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6"><h2 class="fw-bold">{{ $content['home_why_title'] }}</h2><p>{{ $content['home_why_text'] }}</p></div>
            <div class="col-lg-6">
                <div class="row g-2">@foreach($categories as $category)<div class="col-6"><div class="p-3 bg-white rounded shadow-sm">{{ $category->name }}</div></div>@endforeach</div>
            </div>
        </div>
    </div>
</section>
<section class="section bg-white" id="service-areas">
    <div class="container">
        <h2 class="fw-bold">{{ $content['home_areas_title'] }}</h2>
        <p class="lead">{{ $content['home_areas_text'] }}</p>
    </div>
</section>
<section class="section bg-white pt-0" id="testimonials">
    <div class="container">
        <h2 class="fw-bold">{{ $content['home_testimonials_title'] }}</h2>
        <div class="row g-3 mt-2">
            @forelse($testimonials as $review)
                <div class="col-md-4"><div class="card h-100"><div class="card-body"><strong>{{ $review->customer->name }}</strong><p class="mb-1">{{ $review->comment }}</p><span class="text-brand">{{ str_repeat('*', $review->rating) }}</span></div></div></div>
            @empty
                <div class="col"><p class="text-muted">{{ $content['home_empty_testimonials'] }}</p></div>
            @endforelse
        </div>
    </div>
</section>
@if($content['home_seo_body'] !== '')
    <style>
        .home-article-section { padding:56px 0 72px; background:#f4f7fc; }
        .home-article-section > .container { width:calc(100% - 48px); max-width:1772px; padding:0; }
        .home-article-frame { position:relative; padding:0 40px 0 50px; border-radius:48px; background:linear-gradient(135deg, #f7fafd, #edf4fc); }
        .home-article-frame::before { content:""; position:absolute; left:0; top:26px; bottom:26px; width:7px; border-radius:5px; background:linear-gradient(#17457d, #2879ff); }
        .home-article-card { --article-padding:clamp(28px, 3.5vw, 64px); --article-gutter:24px; position:relative; padding:var(--article-padding); border:1px solid #e3ebf6; border-radius:42px; background:#fff; box-shadow:0 24px 64px rgba(30, 64, 110, .08); }
        .home-article-card::after { content:""; position:absolute; left:var(--article-padding); right:calc(var(--article-padding) + var(--article-gutter)); bottom:var(--article-padding); height:112px; pointer-events:none; background:linear-gradient(transparent, #fff); opacity:0; }
        .home-article-card.has-more::after { opacity:1; }
        .home-article-scroll { max-height:532px; overflow-y:auto; padding-right:var(--article-gutter); scrollbar-width:thin; scrollbar-color:#c7d4e5 transparent; overflow-wrap:anywhere; }
        .home-article-scroll:focus-visible { outline:3px solid #2879ff; outline-offset:8px; border-radius:4px; }
        .home-article-title { margin:0 0 40px; color:#17457d; font-size:clamp(1.65rem, 2.6vw, 3rem); line-height:1.3; font-weight:750; }
        .home-article-body { color:#506079; font-size:clamp(1.0625rem, 1.3vw, 1.5rem); line-height:1.95; }
        .home-article-body h3, .home-article-body h4, .home-article-body h5, .home-article-body h6 { color:#17457d; font-weight:700; line-height:1.4; margin:1.5em 0 .65em; }
        .home-article-body h3 { font-size:clamp(1.5rem, 2.6vw, 3rem); }
        .home-article-body h4 { font-size:clamp(1.25rem, 1.8vw, 2rem); }
        .home-article-body > :first-child { margin-top:0; }
        .home-article-body > :last-child { margin-bottom:0; }
        .home-article-body p { margin-bottom:1.2em; }
        .home-article-body ul, .home-article-body ol { padding-left:1.5em; margin-bottom:1.2em; }
        .home-article-body li { margin-bottom:.4em; }
        .home-article-body a { color:#0b5ed7; text-decoration:underline; text-underline-offset:3px; }
        .home-article-body blockquote { border-left:3px solid #2879ff; margin:1.5em 0; padding:8px 20px; background:#f4f7fc; }
        @media (max-width:991.98px) {
            .home-article-frame { padding:0 0 0 24px; }
            .home-article-frame::before { width:5px; }
        }
        @media (max-width:575.98px) {
            .home-article-section { padding:32px 0 40px; }
            .home-article-section > .container { width:calc(100% - 24px); }
            .home-article-frame { padding-left:14px; }
            .home-article-frame::before { width:4px; }
            .home-article-card { --article-padding:24px; --article-gutter:12px; border-radius:22px; }
            .home-article-card::after { height:72px; }
            .home-article-scroll { max-height:65vh; }
            .home-article-title { margin-bottom:28px; }
            .home-article-body { font-size:1rem; line-height:1.8; }
        }
        @media print {
            .home-article-scroll { max-height:none; overflow:visible; }
            .home-article-card::after { display:none; }
        }
    </style>
    <section class="home-article-section" id="homepage-article" aria-labelledby="homepage-article-title">
        <div class="container">
            <div class="home-article-frame">
                <article class="home-article-card">
                    <div class="home-article-scroll" tabindex="0" role="region" aria-labelledby="homepage-article-title">
                        <h2 class="home-article-title" id="homepage-article-title">{{ $content['home_seo_title'] }}</h2>
                        <div class="home-article-body">{!! $content['home_seo_body'] !!}</div>
                    </div>
                </article>
            </div>
        </div>
    </section>
    @push('scripts')
    <script>
        (() => {
            const card = document.querySelector('#homepage-article .home-article-card');
            const scroller = card.querySelector('.home-article-scroll');
            const updateFade = () => {
                const hasMore = scroller.scrollHeight - scroller.clientHeight - scroller.scrollTop > 2;
                card.classList.toggle('has-more', hasMore && !scroller.contains(document.activeElement));
            };

            scroller.addEventListener('scroll', updateFade, { passive: true });
            window.addEventListener('resize', updateFade);
            window.addEventListener('load', updateFade);
            // Keep the final lines and focused links readable at the end of the article.
            scroller.addEventListener('focusin', updateFade);
            scroller.addEventListener('focusout', updateFade);
            updateFade();
        })();
    </script>
    @endpush
@endif
@endsection
