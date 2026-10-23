<?php

/*
 * Branding configs for your application
 */

return [
    'logo' => [
        'type' => 'image',
        'image_src' => '/storage/auth/logo.png',
        'svg_string' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 164 32" fill="none"><rect width="32" height="32" rx="8" fill="#1A3D8E"/><path fill="#fff" d="M7 7h8v8H7zm2 2v4h4V9zm8-2h8v8h-8zm2 2v4h4V9zM7 17h8v8H7zm2 2v4h4v-4zm8-2h3v3h-3zm5 0h3v3h-3zm-5 5h3v3h-3zm5 0h3v3h-3z"/><text x="42" y="23" fill="#1C2942" font-family="Arial,sans-serif" font-size="19" font-weight="700">Eventicks</text></svg>',
        'height' => '32',
    ],
    'background' => [
        'color' => '#1A3D8E',
        'image' => '',
        'image_overlay_color' => '#000000',
        'image_overlay_opacity' => '0.5',
    ],
    'color' => [
        'text' => '#212936',
        'button' => '#1A3D8E',
        'button_text' => '#ffffff',
        'input_text' => '',
        'input_border' => '#D7E0EC',
    ],
    'alignment' => [
        'heading' => 'center',
        'container' => 'center',
    ],
    'favicon' => [
        'light' => '/favicon-96x96.png',
        'dark' => '/favicon-96x96.png',
    ],
];
