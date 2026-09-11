<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Role;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'admin', 'label' => 'Admin'],
            ['name' => 'customer', 'label' => 'Customer'],
            ['name' => 'provider', 'label' => 'Service Provider'],
            ['name' => 'dispatcher', 'label' => 'Staff/Dispatcher'],
        ])->mapWithKeys(fn ($role) => [$role['name'] => Role::updateOrCreate(['name' => $role['name']], $role)]);

        $admin = User::updateOrCreate(['email' => 'admin@hotspotbillingsystem.co.ke'], [
            'role_id' => $roles['admin']->id,
            'name' => 'Hotspot Billing Admin',
            'phone' => '0711000000',
            'county' => 'Nairobi',
            'location' => 'Westlands',
            'password' => Hash::make('password'),
        ]);

        User::updateOrCreate(['email' => 'dispatcher@hotspotbillingsystem.co.ke'], [
            'role_id' => $roles['dispatcher']->id,
            'name' => 'Operations Dispatcher',
            'phone' => '0711000001',
            'county' => 'Nairobi',
            'location' => 'CBD',
            'password' => Hash::make('password'),
        ]);

        $customer = User::updateOrCreate(['email' => 'customer@example.com'], [
            'role_id' => $roles['customer']->id,
            'name' => 'Amina Otieno',
            'phone' => '0712345678',
            'county' => 'Nairobi',
            'location' => 'Kilimani',
            'password' => Hash::make('password'),
        ]);

        $providerUser = User::updateOrCreate(['email' => 'provider@example.com'], [
            'role_id' => $roles['provider']->id,
            'name' => 'Network Support Team',
            'phone' => '0722000000',
            'county' => 'Nairobi',
            'location' => 'Roysambu',
            'password' => Hash::make('password'),
        ]);

        $categoryNames = [
            'Hotspot Management', 'ISP Billing', 'Internet Packages', 'Payments',
            'Reporting', 'Customer Portal',
        ];

        $categories = collect($categoryNames)->mapWithKeys(function ($name) {
            return [$name => Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $name.' solutions for internet providers across Kenya.', 'is_active' => true]
            )];
        });

        $services = [
            ['Hotspot Management', 'Hotspot User Management', 0, 'Hotspot billing', 'Manage WiFi hotspot users, access periods and account status from one dashboard.'],
            ['ISP Billing', 'ISP Subscriber Management', 0, 'ISP billing', 'Keep track of ISP customers, internet plans and subscription status.'],
            ['Internet Packages', 'Internet Package Management', 0, 'Packages', 'Create and manage internet packages with pricing and access periods.'],
            ['Payments', 'Billing & Payment Management', 0, 'Payments', 'Track customer payments and subscription status from one system.'],
            ['Reporting', 'Reporting & Insights', 0, 'Reporting', 'Review customer, payment and subscription activity from one place.'],
            ['Customer Portal', 'Customer Portal', 0, 'Self-service', 'Give customers a simple account to view their status and history.'],
        ];

        $serviceModels = collect($services)->map(function ($service) use ($categories) {
            [$category, $name, $price, $unit, $description] = $service;

            return Service::updateOrCreate(['slug' => Str::slug($name)], [
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'description' => $description,
                'base_price' => $price,
                'unit_type' => $unit,
                'is_active' => true,
            ]);
        });

        $provider = Provider::updateOrCreate(['email' => 'provider@example.com'], [
            'user_id' => $providerUser->id,
            'category_id' => $categories['Hotspot Management']->id,
            'name' => 'Network Support Team',
            'phone' => '0722000000',
            'county' => 'Nairobi',
            'location' => 'Roysambu',
            'availability_status' => 'available',
            'rating' => 4.8,
            'verification_status' => 'approved',
        ]);
        $provider->services()->sync($serviceModels->take(4)->pluck('id'));

        $booking = Booking::updateOrCreate(['booking_number' => 'LAS-DEMO-001'], [
            'customer_id' => $customer->id,
            'category_id' => $categories['Hotspot Management']->id,
            'service_id' => $serviceModels->first()->id,
            'provider_id' => $provider->id,
            'location' => 'Kilimani, Nairobi',
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_time' => '09:00',
            'description' => 'Demo subscription request.',
            'urgency' => 'normal',
            'estimated_price' => 0,
            'status' => 'assigned',
            'provider_status' => 'accepted',
            'assigned_at' => now(),
        ]);

        Payment::updateOrCreate(['booking_id' => $booking->id], [
            'customer_id' => $customer->id,
            'phone_number' => '254712345678',
            'amount' => 0,
            'status' => 'pending',
        ]);

        Setting::updateOrCreate(['key' => 'support_phone'], ['value' => '+254 711 000 000']);
        Setting::updateOrCreate(['key' => 'support_whatsapp'], ['value' => '254711000000']);
        Setting::updateOrCreate(['key' => 'service_areas'], ['value' => 'Nairobi, Kiambu, Machakos, Kajiado, Mombasa, Nakuru, Kisumu, Eldoret']);
    }
}
