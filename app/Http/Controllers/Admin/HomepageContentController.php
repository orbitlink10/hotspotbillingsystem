<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomepageContentController extends Controller
{
    private array $fields = [
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

    public function edit()
    {
        $content = collect($this->fields)->mapWithKeys(fn ($default, $key) => [
            $key => Setting::valueFor($key, $default),
        ]);

        return view('admin.homepage.edit', ['content' => $content]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'home_hero_title' => ['required', 'string', 'max:120'],
            'home_hero_description' => ['required', 'string', 'max:500'],
            'home_primary_cta' => ['required', 'string', 'max:80'],
            'home_provider_cta' => ['required', 'string', 'max:80'],
            'home_whatsapp_cta' => ['required', 'string', 'max:80'],
            'home_whatsapp_url' => ['required', 'string', 'max:255'],
            'home_services_title' => ['required', 'string', 'max:120'],
            'home_services_subtitle' => ['nullable', 'string', 'max:255'],
            'home_how_title_1' => ['required', 'string', 'max:80'],
            'home_how_text_1' => ['required', 'string', 'max:255'],
            'home_how_title_2' => ['required', 'string', 'max:80'],
            'home_how_text_2' => ['required', 'string', 'max:255'],
            'home_how_title_3' => ['required', 'string', 'max:80'],
            'home_how_text_3' => ['required', 'string', 'max:255'],
            'home_why_title' => ['required', 'string', 'max:120'],
            'home_why_text' => ['required', 'string', 'max:500'],
            'home_video_url' => ['nullable', 'string', 'max:500'],
            'home_areas_title' => ['required', 'string', 'max:120'],
            'home_areas_text' => ['required', 'string', 'max:500'],
            'home_testimonials_title' => ['required', 'string', 'max:120'],
            'home_empty_testimonials' => ['required', 'string', 'max:255'],
            'home_hero_image' => ['nullable', 'image', 'max:4096'],
            'home_hero_image_url' => ['nullable', 'string', 'max:500'],
        ]);

        foreach (array_keys($this->fields) as $key) {
            if ($key === 'home_hero_image') {
                continue;
            }

            Setting::putValue($key, $data[$key] ?? '');
        }

        if ($request->hasFile('home_hero_image')) {
            Setting::putValue('home_hero_image', $request->file('home_hero_image')->store('homepage', 'public'));
        } elseif (! empty($data['home_hero_image_url'])) {
            Setting::putValue('home_hero_image', $data['home_hero_image_url']);
        }

        return back()->with('success', 'Homepage content updated.');
    }
}
