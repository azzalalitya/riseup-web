<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
    }

    /**
     * Buat Snap token untuk 1 setoran U Save Up.
     * Return string token yang dipakai window.snap.pay() di frontend.
     */
    public function createSaveUpSnapToken(string $orderId, int $amount, array $customer, string $itemName): string
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $customer['name'] ?? 'RiseUp User',
                'email'      => $customer['email'] ?? 'user@riseup.test',
            ],
            'item_details' => [[
                'id'       => 'saveup',
                'price'    => $amount,
                'quantity' => 1,
                'name'     => $itemName,
            ]],
        ];

        // Batasi kanal pembayaran bila dikonfigurasi (default: QRIS-first).
        $enabled = config('midtrans.enabled_payments');
        if (!empty($enabled)) {
            $params['enabled_payments'] = array_values($enabled);
        }

        return Snap::getSnapToken($params);
    }

    /**
     * Verifikasi signature dari webhook Midtrans.
     * Signature = sha512(order_id + status_code + gross_amount + server_key)
     */
    public function verifySignature(array $payload): bool
    {
        $expected = hash(
            'sha512',
            ($payload['order_id']     ?? '')
          . ($payload['status_code']  ?? '')
          . ($payload['gross_amount'] ?? '')
          . config('midtrans.server_key')
        );

        return isset($payload['signature_key'])
            && hash_equals($expected, $payload['signature_key']);
    }

    /**
     * Terjemahkan payload webhook ke status internal aplikasi.
     */
    public function mapStatus(array $payload): string
    {
        $trxStatus = $payload['transaction_status'] ?? '';
        $fraud     = $payload['fraud_status']       ?? '';

        if (in_array($trxStatus, ['capture', 'settlement'])) {
            // Untuk credit card, capture perlu accept fraud
            if ($trxStatus === 'capture' && $fraud === 'challenge') {
                return 'pending';
            }
            return 'paid';
        }

        if (in_array($trxStatus, ['deny', 'expire', 'cancel'])) {
            return 'failed';
        }

        // pending / authorize / dsb
        return 'pending';
    }
}
