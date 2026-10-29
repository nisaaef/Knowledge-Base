<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pendaftaran penulis
    |--------------------------------------------------------------------------
    | Atur KB_REGISTRATION_OPEN=false di .env untuk menutup halaman /daftar.
    | Admin tetap bisa menambah penulis secara manual.
    */
    'registration_open' => (bool) env('KB_REGISTRATION_OPEN', true),

];
