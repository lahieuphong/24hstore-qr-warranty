<?php

return [
    // Frontend không gọi database. Giá trị null giúp phát hiện sớm nếu code vô tình truy vấn DB.
    'default' => env('DB_CONNECTION'),
    'connections' => [],
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
];
