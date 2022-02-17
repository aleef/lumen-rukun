<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Midtrans\Config;
use Midtrans\Snap;


class Payment extends Model
{
    public static function getPaymentUrl($order_id, $total_amount, $name, $email)
    {

        Config::$serverKey = "SB-Mid-server-qO2srUbYjI0ctqS-3Gtapwn6";
        Config::$clientKey = "SB-Mid-client-gzjMcm9_nrOdRaLF";
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        //Membuat transaksi Midtrans
        //https://snap-docs.midtrans.com/#request-body-json-parameter
        $midtrans = [
            'transaction_details' => [
                'order_id' =>$order_id,
                'gross_amount' => (int)$total_amount
            ],
            'customer_details' => [
                'first_name' => $name,
                'email' => $email,
            ],
            'enabled_payments' => ['gopay','bank_transfer'],
            'vtweb' => []
        ];

        //Ambil halaman payment Midtrans
        $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

        return $paymentUrl;
    }

}
