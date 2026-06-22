<?php

// =============================================================================
// routes/console.php — أوامر Artisan المخصصة (CLI)
// =============================================================================
// تُسجَّل هنا أوامر مثل: php artisan inspire
// جدولة المهام (Schedule) تُعرَّف غالباً في routes/console.php أو bootstrap/app.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// أمر تجريبي: يطبع اقتباساً عشوائياً في الطرفية
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
