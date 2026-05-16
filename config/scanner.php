<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scanner limits
    |--------------------------------------------------------------------------
    |
    | Keep list pages predictable. Large events can easily contain thousands of
    | orderlines, so the UI intentionally loads a small page and searches server
    | side instead of rendering every ticket at once.
    |
    */

    'manual_list_limit' => (int) env('SCANNER_MANUAL_LIST_LIMIT', 100),
    'manual_search_limit' => (int) env('SCANNER_MANUAL_SEARCH_LIMIT', 75),

    /*
    |--------------------------------------------------------------------------
    | Scan feedback
    |--------------------------------------------------------------------------
    */

    'scan_feedback_ms' => (int) env('SCANNER_SCAN_FEEDBACK_MS', 1400),

    /*
    |--------------------------------------------------------------------------
    | Orderline columns
    |--------------------------------------------------------------------------
    |
    | The scanner reads from the existing Eventicks ticket database. These lists
    | allow the app to support slightly different historical schemas without
    | doing expensive schema checks on every request.
    |
    */

    'orderline_qr_columns' => [
        'uuid',
        'orderline_uuid',
        'unique_qr_code',
        'unique_qr_id',
    ],

    'orderline_search_columns' => [
        'name',
        'email',
        'order_reference',
    ],
];
