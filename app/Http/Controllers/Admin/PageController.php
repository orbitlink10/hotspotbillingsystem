<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    private string $store = 'pages.json';

    public function index(Request $request)
    {
        $pages = collect($this->pages())
            // Keep legacy articles without publication dates in newest-ID-first order.
            ->sortByDesc(fn ($page) => [
                strtotime($page['published_at'] ?? '') ?: 0,
                (int) ($page['id'] ?? 0),
            ])
            ->values();
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        return view('admin.pages.index', [
            'posts' => new LengthAwarePaginator(
                $pages->forPage($currentPage, $perPage)->values(),
                $pages->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ],
            ),
        ]);
    }

    public function create()
    {
        return view('admin.pages.form', [
            'page' => null,
            'action' => route('admin.pages.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $pages = $this->pages(false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pages', 'public');
        }

        $data['id'] = empty($pages) ? 1 : ((int) collect($pages)->max('id') + 1);
        $data['slug'] = $this->uniqueSlug($data['title'], $pages);
        $data['alt'] = $data['alt'] ?: $data['title'];
        $data['published_at'] = now()->toIso8601String();

        $pages[] = $data;
        $this->save($pages);

        return redirect()->route('admin.pages')->with('success', 'Page saved and ready to preview.');
    }

    public function edit(int $id)
    {
        $page = collect($this->pages())->firstWhere('id', $id);
        abort_if(! $page, 404);

        return view('admin.pages.form', [
            'page' => $page,
            'action' => route('admin.pages.update', $id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request);
        $pages = $this->pages(false);
        $index = collect($pages)->search(fn ($page) => (int) ($page['id'] ?? 0) === $id);

        abort_if($index === false, 404);

        $existing = $pages[$index];
        $otherPages = collect($pages)->reject(fn ($page) => (int) ($page['id'] ?? 0) === $id)->values()->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pages', 'public');
        } else {
            $data['image'] = $existing['image'] ?? null;
        }

        $data['id'] = $id;
        $data['slug'] = $this->uniqueSlug($data['title'], $otherPages);
        $data['alt'] = $data['alt'] ?: $data['title'];
        $data['published_at'] = $existing['published_at'] ?? null;

        $pages[$index] = $data;
        $this->save(array_values($pages));

        return redirect()->route('admin.pages')->with('success', 'Page updated.');
    }

    public function destroy(int $id)
    {
        $pages = collect($this->pages(false))
            ->reject(fn ($page) => (int) ($page['id'] ?? 0) === $id)
            ->values()
            ->all();

        $this->save($pages);

        return redirect()->route('admin.pages')->with('success', 'Page deleted.');
    }

    public function preview(string $slug)
    {
        $page = collect($this->pages())->firstWhere('slug', $slug);
        abort_if(! $page, 404);

        return view('pages.show', [
            'page' => $page,
            'image' => $this->imageUrl($page['image'] ?? null),
        ]);
    }

    public function image(string $path)
    {
        $path = ltrim(rawurldecode($path), '/');

        abort_if($path === '' || str_contains($path, '..') || ! Storage::disk('public')->exists($path), 404);

        return response(Storage::disk('public')->get($path), 200)
            ->header('Content-Type', Storage::disk('public')->mimeType($path) ?: 'application/octet-stream')
            ->header('Cache-Control', 'public, max-age=604800');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'meta_title' => ['required', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:180'],
            'alt' => ['nullable', 'string', 'max:160'],
            'heading_2' => ['nullable', 'string', 'max:180'],
            'type' => ['required', 'in:Post,Page'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function pages(bool $seed = true): array
    {
        $pages = [];

        if (Storage::disk('local')->exists($this->store)) {
            $pages = json_decode(Storage::disk('local')->get($this->store), true) ?? [];
        }

        if ($seed) {
            $pages = $this->mergeDefaults($pages);
        }

        $pages = collect($pages)->map(function ($page) {
            $page['slug'] = $page['slug'] ?? Str::slug($page['title'] ?? 'page');
            $page['alt'] = $page['alt'] ?? ($page['title'] ?? '');
            $page['type'] = $page['type'] ?? 'Post';

            return $page;
        })->values()->all();

        if ($seed) {
            $this->save($pages);
        }

        return $pages;
    }

    private function mergeDefaults(array $pages): array
    {
        $pages = array_values($pages);
        $existingSlugs = collect($pages)->pluck('slug')->filter()->all();
        $nextId = empty($pages) ? 0 : (int) collect($pages)->max('id');

        foreach ($this->defaultPages() as $default) {
            if (in_array($default['slug'], $existingSlugs, true)) {
                continue;
            }

            $default['id'] = ++$nextId;
            $pages[] = $default;
            $existingSlugs[] = $default['slug'];
        }

        return $pages;
    }

    private function defaultPages(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'About HotspotBillingSystem.co.ke',
                'slug' => 'about-us',
                'alt' => 'About HotspotBillingSystem.co.ke - hotspot billing and ISP management software in Kenya',
                'heading_2' => 'About HotspotBillingSystem.co.ke',
                'type' => 'Page',
                'description' => '<h2>Who We Are</h2><p>HotspotBillingSystem.co.ke is a hotspot billing and ISP management platform built for internet providers in Kenya. We help ISPs, WISPs, hotspot operators, apartment WiFi providers, hotels, schools and cyber cafés manage customers, internet packages, billing and payments from one centralized dashboard.</p><p>Running an internet business involves more than providing a connection. Providers must register customers, set up packages, track payments, manage access and keep subscriber records organized. Our platform brings those everyday tasks into one system so a provider can focus on growing the business instead of chasing paperwork and scattered records.</p><h2>Our Mission</h2><p>Our mission is to make customer, package, billing and payment management simpler and more dependable for internet providers across Kenya. Whether you operate a small hotspot, a growing ISP or a WiFi network for apartments and estates, the goal is the same: clear records, reliable billing and less manual work.</p><h2>What We Do</h2><p>We give internet providers the tools to run a paid connectivity business from one place. The platform covers the full customer journey from registration and package assignment through to billing, M-Pesa payment collection and reporting.</p><ul><li>Register and organize hotspot users and ISP subscribers</li><li>Create internet packages with clear pricing and access periods</li><li>Collect and track M-Pesa payments</li><li>Review revenue, customer and subscription activity in one dashboard</li></ul><h2>Built for Kenya</h2><p>The platform is designed around how internet businesses in Kenya actually work, including M-Pesa payments and the needs of small and growing providers serving homes, businesses, campuses and public WiFi locations.</p><h2>Our Values</h2><p>We believe in clarity, reliability and simplicity. Internet providers should not need complicated enterprise software or scattered spreadsheets to run a profitable WiFi business. We keep the platform practical so your team can get up and running quickly.</p><h2>Our Partners</h2><p>We work alongside the wider hotspot ecosystem in Kenya. For operators who want an end-to-end MikroTik hotspot billing experience with hardware, plan sales and M-Pesa collection, we recommend <a href="https://tajira.co.ke/">Tajira</a>, a Kenyan hotspot billing platform for MikroTik operators.</p>',
                'image' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'About Us | Hotspot Billing System Kenya | ISP Billing Software',
                'meta_description' => 'Learn about HotspotBillingSystem.co.ke, a hotspot billing and ISP management platform for internet providers in Kenya.',
            ],
            [
                'id' => 2,
                'title' => 'Hotspot Billing and ISP Management Features',
                'slug' => 'services',
                'alt' => 'Hotspot billing and ISP management features',
                'heading_2' => 'Hotspot Billing and ISP Management Features',
                'type' => 'Page',
                'description' => '<h2>Customer Management</h2><p>Register and manage hotspot users and ISP subscribers from one dashboard. Keep customer details, contact information and account status organized in a single place so your team always knows who is active, who is due for renewal and who needs attention.</p><h2>Internet Package Management</h2><p>Create and manage internet packages based on pricing, speed or duration, then assign them to your customers. Define your packages once and apply them consistently across your customer base.</p><h2>Billing and Payment Management</h2><p>Track customer payments and subscription status from one system. The platform supports M-Pesa STK Push payments so customers can pay conveniently from their phones, and your team can see every receipt in one place.</p><h2>Reporting and Insights</h2><p>Review customer, payment and subscription activity from one place to understand how your internet business is performing. Spot trends, identify overdue accounts and make informed decisions without exporting to spreadsheets.</p><h2>Customer Portal</h2><p>Give customers access to their own account so they can view their subscription status and payment history without contacting support. This reduces support load and keeps customers informed.</p><h2>MikroTik Hotspot Billing</h2><p>For operators running MikroTik routers, deeper integration is available through our partner. Tajira connects your MikroTik devices to plan sales, M-Pesa receipts and access rules, opening customer sessions automatically once payment is confirmed. Learn more at <a href="https://tajira.co.ke/">Tajira</a>.</p>',
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Features | Hotspot Billing System Kenya | ISP Billing Software',
                'meta_description' => 'Explore hotspot billing and ISP management features for customer, package, billing and payment management in Kenya.',
            ],
            [
                'id' => 3,
                'title' => 'How the Hotspot Billing System Works',
                'slug' => 'how-it-works',
                'alt' => 'How the hotspot billing system works',
                'heading_2' => 'How the Hotspot Billing System Works',
                'type' => 'Page',
                'description' => '<h2>Step 1: Create Your Account</h2><p>Set up your ISP or hotspot billing account. It only takes a few minutes to register and get started.</p><h2>Step 2: Add Your Packages</h2><p>Create internet packages with the pricing and access periods you offer, ready to assign to customers.</p><h2>Step 3: Register Customers</h2><p>Add hotspot users and ISP subscribers to your dashboard so you can manage them in one place.</p><h2>Step 4: Manage Billing and Payments</h2><p>Track payments and subscription status, and let customers pay through M-Pesa where supported.</p><h2>Step 5: Review and Grow</h2><p>Use reports to understand revenue, renewals and customer activity, then adjust your packages and operations to grow your internet business.</p><h2>Automated MikroTik Flow</h2><p>If you run MikroTik hardware, our partner Tajira automates the full loop: a customer buys a plan, M-Pesa confirms the payment, the MikroTik access rule is applied and the session opens automatically. See the flow at <a href="https://tajira.co.ke/">Tajira</a>.</p>',
                'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'How It Works | Hotspot Billing System Kenya',
                'meta_description' => 'See how to set up your account, add internet packages, register customers and manage billing with our hotspot billing software.',
            ],
            [
                'id' => 4,
                'title' => 'Why Choose HotspotBillingSystem.co.ke',
                'slug' => 'why-choose-us',
                'alt' => 'Why choose HotspotBillingSystem.co.ke',
                'heading_2' => 'Why Choose HotspotBillingSystem.co.ke',
                'type' => 'Page',
                'description' => '<h2>Centralized Customer Management</h2><p>Keep hotspot users and ISP subscribers in one dashboard instead of scattered spreadsheets or paper records. Every customer detail, subscription and payment lives in one place.</p><h2>Package and Pricing Management</h2><p>Define your internet packages and pricing once, then assign them to customers consistently. No more mismatched pricing or forgotten renewals.</p><h2>M-Pesa-Ready Payments</h2><p>Collect and track payments through M-Pesa STK Push, the payment method most Kenyan customers already use. Payments are recorded and matched automatically.</p><h2>Simple Administration</h2><p>A clean dashboard for managing customers, packages, billing and payments without unnecessary complexity. Your team can learn it fast and use it daily.</p><h2>Built for Kenyan Operators</h2><p>The platform understands how internet businesses in Kenya work, from M-Pesa payments to serving homes, apartments, campuses and public WiFi locations.</p><h2>An Ecosystem, Not an Island</h2><p>We connect you to the tools that make paid WiFi succeed. For MikroTik hardware, plan sales and automated M-Pesa access control, our recommended partner is <a href="https://tajira.co.ke/">Tajira</a>, a hotspot billing platform built in Kenya.</p>',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Why Choose Us | Hotspot Billing System Kenya',
                'meta_description' => 'See why internet providers choose our hotspot billing and ISP management software for customer, package and payment management.',
            ],
            [
                'id' => 5,
                'title' => 'Frequently Asked Questions',
                'slug' => 'faqs',
                'alt' => 'Frequently asked questions about hotspot billing systems',
                'heading_2' => 'Frequently Asked Questions',
                'type' => 'Page',
                'description' => '<h2>What is a hotspot billing system?</h2><p>A hotspot billing system is software that helps you manage WiFi hotspot users, access, packages and payments from one place instead of handling each customer manually.</p><h2>What is ISP billing software?</h2><p>ISP billing software helps internet service providers manage subscribers, internet packages, billing and payments in a single system.</p><h2>Who can use this platform?</h2><p>It is built for ISPs, WISPs, hotspot operators, apartment WiFi providers, hotels, schools, cyber cafés and other internet businesses in Kenya.</p><h2>Can I manage different internet packages?</h2><p>Yes. You can create and manage multiple internet packages with their own pricing and access periods, then assign them to customers.</p><h2>How do customers make payments?</h2><p>Customers can pay through M-Pesa STK Push. Payments are recorded and tracked in the billing dashboard.</p><h2>Can small ISPs use this software?</h2><p>Yes. The platform is designed to be simple enough for small and growing internet providers while still keeping customer, package and billing records organized.</p><h2>Does this work with MikroTik routers?</h2><p>For automatic MikroTik integration such as applying access rules and opening sessions after M-Pesa payment, we recommend our partner <a href="https://tajira.co.ke/">Tajira</a>, which connects MikroTik hardware to plan sales and M-Pesa collection.</p><h2>Where can I get a MikroTik router?</h2><p>You can find MikroTik routers and access points for hotspot billing at <a href="https://tajira.co.ke/">Tajira</a>, including options for apartments, estates and public WiFi locations.</p><h2>How long does it take to get started?</h2><p>You can create your account, add your first package and register your first customer in a matter of minutes. The dashboard is designed to be simple and fast to set up.</p>',
                'image' => 'https://images.unsplash.com/photo-1526925539332-aa3b66e35444?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'FAQs | Hotspot Billing System Kenya | ISP Billing Software',
                'meta_description' => 'Answers to common questions about our hotspot billing system and ISP billing software in Kenya.',
            ],
        ];
    }

    private function save(array $pages): void
    {
        Storage::disk('local')->put($this->store, json_encode($pages, JSON_PRETTY_PRINT));
    }

    private function uniqueSlug(string $title, array $pages): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $existing = collect($pages)->pluck('slug')->filter()->all();
        $counter = 1;

        while (in_array($slug, $existing, true)) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function imageUrl(?string $image): string
    {
        if (! $image) {
            return 'https://via.placeholder.com/900x460?text=No+Image';
        }

        if (Str::startsWith($image, ['http://', 'https://', '//'])) {
            return $image;
        }

        return route('pages.image', ['path' => ltrim($image, '/')]);
    }
}
