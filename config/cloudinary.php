<?php

return [
    'cloud_url' => env('CLOUDINARY_URL'),

    'cloud' => [
        'name' => env('CLOUDINARY_CLOUD_NAME'),
        'key'  => env('CLOUDINARY_API_KEY'),
        'secret' => env('CLOUDINARY_API_SECRET'),
    ],

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
];