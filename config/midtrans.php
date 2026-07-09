<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans (Snap)
    |--------------------------------------------------------------------------
    | Ambil credential dari .env. Untuk demo UAS pakai Sandbox
    | (gratis, tidak melibatkan uang sungguhan). Signup di:
    |   https://dashboard.sandbox.midtrans.com/
    | Server Key & Client Key ada di Menu Settings -> Access Keys.
    */

    'server_key'  => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'  => env('MIDTRANS_CLIENT_KEY', ''),

    // production=false -> otomatis pakai endpoint Sandbox
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    'is_sanitized' => true,
    'is_3ds'       => true,

    /*
    | Kanal pembayaran yang ditampilkan di popup Snap (opsional).
    | Kosongkan (null) untuk menampilkan SEMUA kanal aktif di akun Midtrans.
    | Default di bawah memprioritaskan QRIS + e-wallet + VA bank.
    | Daftar kode kanal: https://docs.midtrans.com/reference/snap-parameter
    */
    'enabled_payments' => array_filter(explode(',', env(
        'MIDTRANS_ENABLED_PAYMENTS',
        'qris,gopay,shopeepay,bank_transfer,echannel,permata_va,bca_va,bni_va,bri_va'
    ))),
];
