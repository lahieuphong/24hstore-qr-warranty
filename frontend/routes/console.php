<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-frontend', function (): void {
    $this->info('24hStore Warranty Lookup frontend - no database connection.');
})->purpose('Hiển thị thông tin frontend');
