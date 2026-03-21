<?php

return [
    'redis_key' => env('REQUEST_MONITOR_REDIS_KEY', 'monitor:requests'),
    'max_entries' => (int) env('REQUEST_MONITOR_MAX_ENTRIES', 1000),
    'page_limit' => (int) env('REQUEST_MONITOR_PAGE_LIMIT', 200),
    'exclude_paths' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('REQUEST_MONITOR_EXCLUDE_PATHS', ''))
    ))),
];
