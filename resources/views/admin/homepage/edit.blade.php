@extends('layouts.dashboard')
@section('title', 'Homepage Content')
@section('dashboard-title', 'Homepage Content')
@section('dashboard-subtitle', 'Publish your homepage article and edit homepage text, links and images.')
@section('dashboard-actions')
<div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-primary" href="{{ route('home') }}" target="_blank">View Homepage</a>
    <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Back to Dashboard</a>
</div>
@endsection
@section('content')
@php
    $currentHeroImage = $content['home_hero_image'];
    $currentHeroImageUrl = str_starts_with($currentHeroImage, 'http') ? $currentHeroImage : asset('storage/'.$currentHeroImage);
@endphp
<form method="post" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="card">
    @csrf
    @method('put')
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12" id="homepage-article-editor">
                <h2 class="h5 fw-bold mb-2">Homepage SEO Article</h2>
                <p class="text-muted mb-3">Publish a guide about your services in a scrollable section above the homepage footer. Leave the article empty to hide the section.</p>
                <label class="form-label" for="home_seo_title">Article title</label>
                <input class="form-control mb-3" id="home_seo_title" name="home_seo_title" maxlength="200" value="{{ old('home_seo_title', $content['home_seo_title']) }}">
                @error('home_seo_title')<div class="text-danger mb-2">{{ $message }}</div>@enderror
                <label class="form-label" for="home_seo_body">Article content</label>
                <textarea class="form-control" id="home_seo_body" name="home_seo_body" rows="16" aria-describedby="home-seo-help">{{ old('home_seo_body', $content['home_seo_body']) }}</textarea>
                <div class="form-text" id="home-seo-help">Use headings, paragraphs, lists and links to help visitors understand your services. Saving publishes your changes on the homepage.</div>
                <div class="form-text" id="home-seo-fallback">If the formatting toolbar does not load, you can still enter text or basic HTML here.</div>
                @error('home_seo_body')<div class="text-danger mt-2">{{ $message }}</div>@enderror
                <div class="d-flex flex-wrap gap-2 mt-3 mb-4">
                    <button class="btn btn-brand" type="submit">Save Homepage Content</button>
                    <a class="btn btn-outline-primary" href="{{ route('home') }}#homepage-article" target="_blank" rel="noopener">View Published Article</a>
                </div>
                <hr>
            </div>
            <div class="col-12">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-0">Hero Section</h2>
            </div>
            <div class="col-md-7">
                <label class="form-label">Hero title</label>
                <input class="form-control" name="home_hero_title" value="{{ old('home_hero_title', $content['home_hero_title']) }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">Upload hero image</label>
                <input class="form-control" type="file" name="home_hero_image" accept="image/*">
            </div>
            <div class="col-12">
                <label class="form-label">Hero description</label>
                <textarea class="form-control" name="home_hero_description" rows="3" required>{{ old('home_hero_description', $content['home_hero_description']) }}</textarea>
            </div>
            <div class="col-md-7">
                <label class="form-label">Current hero image</label>
                <div class="border rounded p-2 bg-light">
                    <img src="{{ $currentHeroImageUrl }}" alt="Current hero image" class="img-fluid rounded" style="max-height:180px; object-fit:cover; width:100%;">
                </div>
            </div>
            <div class="col-md-5">
                <label class="form-label">Optional hero image URL</label>
                <input class="form-control" name="home_hero_image_url" value="{{ old('home_hero_image_url', str_starts_with($content['home_hero_image'], 'http') ? $content['home_hero_image'] : '') }}" placeholder="Use only if you are not uploading a file">
                <div class="form-text">Uploading an image replaces this URL and is the recommended option.</div>
                <label class="form-label mt-3">Hero video YouTube link</label>
                <input class="form-control" name="home_video_url" value="{{ old('home_video_url', $content['home_video_url']) }}" placeholder="YouTube URL, shorts URL, embed URL, or video ID">
                <div class="form-text">This video appears on the right side of the hero section.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Primary CTA label</label>
                <input class="form-control" name="home_primary_cta" value="{{ old('home_primary_cta', $content['home_primary_cta']) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Provider CTA label</label>
                <input class="form-control" name="home_provider_cta" value="{{ old('home_provider_cta', $content['home_provider_cta']) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">WhatsApp CTA label</label>
                <input class="form-control" name="home_whatsapp_cta" value="{{ old('home_whatsapp_cta', $content['home_whatsapp_cta']) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">WhatsApp URL</label>
                <input class="form-control" name="home_whatsapp_url" value="{{ old('home_whatsapp_url', $content['home_whatsapp_url']) }}" required>
            </div>

            <div class="col-12 pt-3">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-0">Services Section</h2>
            </div>
            <div class="col-md-6">
                <label class="form-label">Services title</label>
                <input class="form-control" name="home_services_title" value="{{ old('home_services_title', $content['home_services_title']) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Services subtitle</label>
                <input class="form-control" name="home_services_subtitle" value="{{ old('home_services_subtitle', $content['home_services_subtitle']) }}">
            </div>

            <div class="col-12 pt-3">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-0">How It Works</h2>
            </div>
            @for($i = 1; $i <= 3; $i++)
                <div class="col-md-4">
                    <label class="form-label">Step {{ $i }} title</label>
                    <input class="form-control mb-2" name="home_how_title_{{ $i }}" value="{{ old('home_how_title_'.$i, $content['home_how_title_'.$i]) }}" required>
                    <label class="form-label">Step {{ $i }} text</label>
                    <textarea class="form-control" name="home_how_text_{{ $i }}" rows="3" required>{{ old('home_how_text_'.$i, $content['home_how_text_'.$i]) }}</textarea>
                </div>
            @endfor

            <div class="col-12 pt-3">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-0">Why Choose Us</h2>
            </div>
            <div class="col-md-4">
                <label class="form-label">Section title</label>
                <input class="form-control" name="home_why_title" value="{{ old('home_why_title', $content['home_why_title']) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Section text</label>
                <textarea class="form-control" name="home_why_text" rows="3" required>{{ old('home_why_text', $content['home_why_text']) }}</textarea>
            </div>

            <div class="col-12 pt-3">
                <h2 class="h6 fw-bold text-uppercase text-muted mb-0">Areas & Testimonials</h2>
            </div>
            <div class="col-md-4">
                <label class="form-label">Areas title</label>
                <input class="form-control" name="home_areas_title" value="{{ old('home_areas_title', $content['home_areas_title']) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Areas text</label>
                <input class="form-control" name="home_areas_text" value="{{ old('home_areas_text', $content['home_areas_text']) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Testimonials title</label>
                <input class="form-control" name="home_testimonials_title" value="{{ old('home_testimonials_title', $content['home_testimonials_title']) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Empty testimonials text</label>
                <input class="form-control" name="home_empty_testimonials" value="{{ old('home_empty_testimonials', $content['home_empty_testimonials']) }}" required>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white border-0 p-4 pt-0">
        <button class="btn btn-brand">Save Homepage Content</button>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.tinymce) return;

        tinymce.init({
            selector: '#home_seo_body',
            menubar: false,
            plugins: 'link lists code fullscreen wordcount',
            toolbar: 'undo redo | blocks | bold italic | bullist numlist blockquote | link removeformat | code fullscreen',
            block_formats: 'Paragraph=p;Heading=h3;Subheading=h4',
            valid_elements: 'p,h3,h4,h5,h6,strong/b,em/i,u,s,ul,ol,li,blockquote,a[href|title],br,hr',
            height: 480,
            branding: false,
            promotion: false,
            convert_urls: false,
            link_target_list: false,
            link_title: true,
            content_style: 'body { font-family: system-ui, sans-serif; font-size: 18px; line-height: 1.8; color: #506079; padding: 16px; } h3, h4 { color: #17457d; } a { color: #0b5ed7; }',
            setup: editor => {
                editor.on('init', () => { document.getElementById('home-seo-fallback').hidden = true; });
            }
        });
    });
</script>
@endpush
