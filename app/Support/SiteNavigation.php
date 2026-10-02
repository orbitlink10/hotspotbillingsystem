<?php

namespace App\Support;

use App\Models\Setting;

class SiteNavigation
{
    public const NAV_SETTING = 'home_nav_items';
    public const TOPBAR_SETTING = 'home_topbar_enabled';

    public static function itemDefinitions(): array
    {
        return [
            'home' => ['label' => 'Home', 'url' => route('home')],
            'features' => ['label' => 'Features', 'url' => route('pages.preview', 'services')],
            'how-it-works' => ['label' => 'How It Works', 'url' => route('pages.preview', 'how-it-works')],
            'why-choose-us' => ['label' => 'Why Choose Us', 'url' => route('pages.preview', 'why-choose-us')],
            'service-areas' => ['label' => "Who It's For", 'url' => route('home').'#service-areas'],
            'testimonials' => ['label' => 'Testimonials', 'url' => route('home').'#testimonials'],
            'get-started' => ['label' => 'Get Started', 'url' => 'https://tajira.co.ke/signup'],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::itemDefinitions());
    }

    public static function defaultKeys(): array
    {
        return self::keys();
    }

    public static function enabledKeys(): array
    {
        $stored = Setting::valueFor(self::NAV_SETTING, null);

        if ($stored === null) {
            return self::defaultKeys();
        }

        $keys = array_filter(array_map('trim', explode(',', (string) $stored)));

        return array_values(array_intersect(self::keys(), $keys));
    }

    public static function enabledItems(): array
    {
        $definitions = self::itemDefinitions();
        $items = [];

        foreach (self::enabledKeys() as $key) {
            if (isset($definitions[$key])) {
                $items[] = $definitions[$key];
            }
        }

        return $items;
    }

    public static function topbar(): ?array
    {
        if (Setting::valueFor(self::TOPBAR_SETTING, '1') !== '1') {
            return null;
        }

        return [
            'email' => Setting::valueFor('home_topbar_email', 'support@hotspotbillingsystem.co.ke'),
            'address' => Setting::valueFor('home_topbar_address', 'Nairobi, Kenya'),
            'phone' => Setting::valueFor('home_topbar_phone', '+254 711 000 000'),
            'link_label' => Setting::valueFor('home_topbar_link_label', 'Shop Equipment'),
            'link_url' => Setting::valueFor('home_topbar_link_url', '/services'),
        ];
    }
}
