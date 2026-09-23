<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Review;
use App\Models\Service;
use App\Models\Setting;
use App\Support\HomepageArticle;

class HomeController extends Controller
{
    public function __invoke()
    {
        $defaults = [
            'home_seo_title' => 'Hotspot Billing & ISP Management in Kenya',
            'home_seo_body' => '',
            'home_hero_title' => 'Hotspot Billing System & ISP Billing Software in Kenya',
            'home_hero_description' => 'Manage hotspot users, ISP subscribers, internet packages, billing and payments from one platform built for internet providers in Kenya.',
            'home_primary_cta' => 'Get Started',
            'home_provider_cta' => 'Create Account',
            'home_whatsapp_cta' => 'Talk to Our Team',
            'home_whatsapp_url' => 'https://wa.me/254711000000',
            'home_services_title' => 'Hotspot Billing & ISP Management Features',
            'home_services_subtitle' => 'Manage customers, packages, billing and payments from one dashboard.',
            'home_how_title_1' => '1. Create Your Account',
            'home_how_text_1' => 'Set up your ISP or hotspot billing account in minutes.',
            'home_how_title_2' => '2. Add Your Packages',
            'home_how_text_2' => 'Create internet packages with pricing and access periods.',
            'home_how_title_3' => '3. Manage & Collect Payments',
            'home_how_text_3' => 'Register customers, track subscriptions and collect M-Pesa payments.',
            'home_why_title' => 'Why Choose HotspotBillingSystem.co.ke',
            'home_why_text' => 'Manage customers, packages, billing and payments from one centralized platform built for internet providers in Kenya.',
            'home_video_url' => '',
            'home_areas_title' => 'Built for Internet Providers Across Kenya',
            'home_areas_text' => 'Supporting ISPs, WISPs, hotspot operators, apartment WiFi providers, hotels, schools and cyber cafés across Kenya.',
            'home_testimonials_title' => 'Customer Testimonials',
            'home_empty_testimonials' => 'Customer testimonials will appear here once verified reviews are available.',
            'home_hero_image' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1800&q=80',
        ];

        $content = collect($defaults)->mapWithKeys(fn ($default, $key) => [$key => Setting::valueFor($key, $default)]);
        $content['home_seo_body'] = HomepageArticle::clean($content['home_seo_body']);

        return view('home', [
            'categories' => Category::where('is_active', true)->with('services')->take(10)->get(),
            'popularServices' => Service::where('is_active', true)->with('category')->take(6)->get(),
            'testimonials' => Review::with(['customer', 'provider'])->latest()->take(3)->get(),
            'content' => $content,
            'homeVideoEmbedUrl' => $this->youtubeEmbedUrl($content['home_video_url']),
        ]);
    }

    private function youtubeEmbedUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) {
            return 'https://www.youtube.com/embed/'.$value;
        }

        $patterns = [
            '/youtu\.be\/([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/watch\?v=([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/embed\/([A-Za-z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches)) {
                return 'https://www.youtube.com/embed/'.$matches[1];
            }
        }

        return null;
    }
}
