<?php

return [
    'backend' => [
        'api_url' => rtrim((string) env('BACKEND_API_URL', 'http://localhost:8000/api/v1'), '/'),
        'admin_url' => rtrim((string) env('BACKEND_ADMIN_URL', 'http://localhost:8000/admin'), '/'),
        'timeout' => (int) env('BACKEND_API_TIMEOUT', 8),
    ],
];
