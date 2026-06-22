<?php

// =============================================================================
// config/ml.php — إعدادات خدمة التعلم الآلي (تنبؤ السعر)
// =============================================================================
// Laravel لا يحمّل ملفات .pkl مباشرة؛ يرسل HTTP لخادم Flask منفصل.
// التشغيل المحلي: cd ml/pricing && pip install -r requirements.txt && python server.py

return [

    /*
    |--------------------------------------------------------------------------
    | خدمة تنبؤ السعر (Flask / scikit-learn)
    |--------------------------------------------------------------------------
    */
    'price_prediction' => [
        // عنوان الخادم — من .env أو localhost:5000
        'url' => rtrim(env('ML_PRICE_PREDICTION_URL', 'http://127.0.0.1:5000'), '/'),
        // أقصى انتظار لرد HTTP بالثواني
        'timeout_seconds' => (int) env('ML_PRICE_PREDICTION_TIMEOUT', 10),
        // مهلة الاتصال الأولى (قبل استلام الجسم)
        'connect_timeout_seconds' => (int) env('ML_PRICE_PREDICTION_CONNECT_TIMEOUT', 3),

        /**
         * location_field: ما يُرسل للنموذج كموقع —
         * city = اسم المدينة (place.city.name) | place = اسم الحي
         */
        'location_field' => env('ML_PRICE_PREDICTION_LOCATION_FIELD', 'city'),

        /** log_predictions: حفظ كل تنبؤ في جدول price_predictions للتحليل */
        'log_predictions' => (bool) env('ML_PRICE_PREDICTION_LOG', true),
    ],

];
