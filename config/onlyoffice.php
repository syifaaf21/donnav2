<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ONLYOFFICE DocSpace Configuration
    |--------------------------------------------------------------------------
    */

    // URL DocSpace kamu
    'docspace_url' => env('ONLYOFFICE_DOCSPACE_URL', 'https://docspace-2zlw5p.onlyoffice.com'),

    // Kredensial admin DocSpace untuk API
    'docspace_email'    => env('ONLYOFFICE_DOCSPACE_EMAIL'),
    'docspace_password' => env('ONLYOFFICE_DOCSPACE_PASSWORD'),

    // ID folder di DocSpace tempat file di-upload
    // Buka DocSpace → masuk ke folder yang diinginkan → lihat URL-nya
    // Contoh: https://docspace.../rooms/shared/23 → folder_id = 23
    'docspace_folder_id' => env('ONLYOFFICE_DOCSPACE_FOLDER_ID'),

    // Timeout request API ke DocSpace (detik)
    'request_timeout' => (int) env('ONLYOFFICE_REQUEST_TIMEOUT', 120),

    // Koneksi awal ke DocSpace (detik)
    'connect_timeout' => (int) env('ONLYOFFICE_CONNECT_TIMEOUT', 20),

    // Retry saat network unstable
    'retry_times' => (int) env('ONLYOFFICE_RETRY_TIMES', 3),

    // Jeda antar retry (millisecond)
    'retry_sleep_ms' => (int) env('ONLYOFFICE_RETRY_SLEEP_MS', 1000),
];
