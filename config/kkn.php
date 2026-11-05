<?php

return [
    'admin' => [
        'name' => env('KKN_ADMIN_NAME'),
        'username' => env('KKN_ADMIN_USERNAME'),
        'password' => env('KKN_ADMIN_PASSWORD'),
    ],

    // Nilai awal untuk tabel settings. Setelah di-seed, admin mengubahnya lewat database/halaman pengaturan.
    'posko' => [
        'latitude' => env('KKN_POSKO_LATITUDE'),
        'longitude' => env('KKN_POSKO_LONGITUDE'),
        'radius_m' => 30,
        'max_gps_accuracy_m' => 50,
    ],
];
