<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bot / crawler user-agent fragments
    |--------------------------------------------------------------------------
    | Requests whose User-Agent contains any of these fragments (case-insensitive)
    | are never tracked as visitors or visits. Empty user-agents are treated as bots.
    */
    'bot_user_agents' => [
        // search & social crawlers
        'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot', 'facebot',
        'facebookexternalhit', 'ia_archiver', 'WhatsApp', 'TelegramBot', 'Twitterbot', 'LinkedInBot',
        'Pinterestbot', 'Slackbot', 'Discordbot', 'Google-Structured-Data-Testing-Tool', 'CriteoBot',
        'Applebot', 'HeadlessChrome', 'UptimeRobot',
        // generic markers
        'bot', 'spider', 'crawler', 'crawling', 'scraper', 'HealthCheck', 'monitor',
        // http clients / scanners
        'curl/', 'wget', 'python', 'Go-http-client', 'Java/', 'libwww', 'okhttp', 'axios/',
        'node-fetch', 'Scrapy', 'HttpClient', 'PostmanRuntime', 'Nmap', 'masscan', 'zgrab',
    ],

    /*
    |--------------------------------------------------------------------------
    | Interaction targets accepted by POST /tracking/hit
    |--------------------------------------------------------------------------
    */
    'interactable_models' => [
        'Product' => \App\Models\Product::class,
        'Category' => \App\Models\Category::class,
        'Campaign' => \App\Models\Campaign::class,
    ],

    'interaction_types' => ['view', 'click', 'heartbeat'],

    // Seconds of inactivity after which a new visit (session) is opened.
    'session_minutes' => 30,

    // Upper bound for a single heartbeat's duration payload (client sends 10).
    'max_hit_duration' => 60,
];
