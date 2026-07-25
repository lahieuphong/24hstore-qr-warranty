<?php

return [
    'site_name' => env('ADMIN_SITE_NAME', '24hStore Administration'),
    'site_tagline' => env('ADMIN_SITE_TAGLINE', 'Quản trị QR bảo hành'),

    // Tài khoản được tạo bởi DatabaseSeeder.
    'name' => env('ADMIN_NAME', 'Quản trị hệ thống'),
    'email' => env('ADMIN_EMAIL', 'admin@gmail.com'),
    'password' => env('ADMIN_PASSWORD', 'Aa123456'),

    // Chỉ cho phép sửa tài khoản quản trị được đồng bộ từ Environment khi bật cờ.
    // Mặc định luôn khóa trên production, nhưng vẫn thuận tiện khi phát triển cục bộ.
    'environment_admin_editable' => filter_var(
        env('ADMIN_ENVIRONMENT_ADMIN_EDITABLE', env('APP_ENV', 'production') !== 'production'),
        FILTER_VALIDATE_BOOL,
    ),
];
