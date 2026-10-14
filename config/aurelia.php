<?php

return [
    'demo_content' => (bool) env('AURELIA_DEMO_CONTENT', true),
    'notifications' => [
        'staff_email' => env('AURELIA_STAFF_EMAIL'),
        'from_email' => env('AURELIA_FROM_EMAIL', 'hello@aureliadental.example'),
        'from_name' => env('AURELIA_FROM_NAME', 'Aurelia Dental Clinic'),
    ],
];
