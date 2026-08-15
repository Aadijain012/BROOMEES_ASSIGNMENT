<?php

return [
    'deletion' => [
        'max_reputation' => (float) env('REPUTATION_DELETION_THRESHOLD', 10),
    ],
    'tokens' => [
        'expiration_minutes' => (int) env('TOKEN_EXPIRATION', 1440),
    ],
    'rate_limits' => [
        'read_per_minute' => (int) env('READ_RATE_LIMIT', 120),
        'write_per_minute' => (int) env('WRITE_RATE_LIMIT', 30),
    ],
    'pagination' => [
        'default_per_page' => (int) env('DEFAULT_PER_PAGE', 20),
        'max_per_page' => (int) env('MAX_PER_PAGE', 100),
    ],
];
