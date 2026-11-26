<?php

return [
    'currency' => 'OMR',
    'announcement' => [
        'enabled' => env('STORE_ANNOUNCEMENT_ENABLED', true),
        'en' => env('STORE_ANNOUNCEMENT_EN', 'Complimentary delivery across Muscat on orders above OMR 40 · Discover your signature scent'),
        'ar' => env('STORE_ANNOUNCEMENT_AR', 'توصيل مجاني في مسقط للطلبات فوق ٤٠ ر.ع. · اكتشفوا عطركم المميّز'),
        'url_path' => '/shop',
    ],
    'free_delivery_threshold_omr' => (float) env('STORE_FREE_DELIVERY_OMR', 40),
    'support' => [
        'whatsapp_number' => env('STORE_WHATSAPP_NUMBER', '+96871826515'),
        'email' => env('STORE_SUPPORT_EMAIL', 'care@muscatfragrances.com'),
        'store_name_en' => 'Muscat Fragrances Boutique',
        'store_name_ar' => 'بوتيك مسقط للعطور',
        'store_address_en' => 'Al Qurum, Muscat, Sultanate of Oman',
        'store_address_ar' => 'القرم، مسقط، سلطنة عُمان',
        'store_hours_en' => 'Saturday – Thursday: 10:00 AM – 10:00 PM · Friday: 4:00 PM – 10:30 PM',
        'store_hours_ar' => 'السبت – الخميس: ١٠:٠٠ صباحاً – ١٠:٠٠ مساءً · الجمعة: ٤:٠٠ عصراً – ١٠:٣٠ مساءً',
    ],
    'social' => [
        'handle' => 'MuscatFragrances',
        'links' => [
            ['key' => 'facebook', 'label' => 'Facebook', 'url' => 'https://www.facebook.com/MuscatFragrances'],
            ['key' => 'instagram', 'label' => 'Instagram', 'url' => 'https://www.instagram.com/MuscatFragrances'],
            ['key' => 'tiktok', 'label' => 'TikTok', 'url' => 'https://www.tiktok.com/@MuscatFragrances'],
            ['key' => 'x', 'label' => 'X', 'url' => 'https://x.com/MuscatFragrances'],
            ['key' => 'youtube', 'label' => 'YouTube', 'url' => 'https://www.youtube.com/@MuscatFragrances'],
            ['key' => 'snapchat', 'label' => 'Snapchat', 'url' => 'https://www.snapchat.com/add/MuscatFragrances'],
        ],
    ],
    'seasonal_gifts' => [
        'eid' => (bool) env('STORE_GIFTS_EID', false),
        'ramadan' => (bool) env('STORE_GIFTS_RAMADAN', false),
        'wedding' => (bool) env('STORE_GIFTS_WEDDING', true),
    ],
    'budget_ranges' => [
        ['key' => 'under-10', 'min' => 0.0, 'max' => 9.999],
        ['key' => '10-25', 'min' => 10.0, 'max' => 25.0],
        ['key' => '25-50', 'min' => 25.001, 'max' => 50.0],
        ['key' => 'above-50', 'min' => 50.001, 'max' => null],
    ],
];
