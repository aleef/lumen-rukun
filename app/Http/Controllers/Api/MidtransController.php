<?php

namespace App\Http\Controllers\Api;

use App\Billing;
use App\GenerateOrder;
use App\GenerateSubscribeOrder;
use App\Http\Controllers\Controller;
use App\Invoice;
use App\Keuangan;
use App\Notifikasi;
use App\PaketLangganan;
use App\Periodetagihan;
use App\VoucherWil;
use App\Warga;
use App\Wilayah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;


class MidtransController extends Controller
{
    public function callback(Request $request) {
        //set konfigurasi midtrans
        Config::$serverKey = "SB-Mid-server-qO2srUbYjI0ctqS-3Gtapwn6";
        Config::$clientKey = "SB-Mid-client-gzjMcm9_nrOdRaLF";
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        //Buat instance midtrans notification
        $notification = new Notification();

        //Asign ke variable untuk memudahkan coding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud;
        $order_id = $notification->order_id;

        if(substr($order_id,0,2) == 'NS') { //New Subscriber
            self::callback_newsubsciber($order_id, $status, $type, $fraud);
        }else if(substr($order_id,0,2) == 'ES') { //Extend Subscription
            self::callback_extend_subscription($order_id, $status, $type, $fraud);
        }else if(substr($order_id,0,2) == 'US') { //Upgrade Subscription
            self::callback_upgrade_subscription($order_id, $status, $type, $fraud);
        } else { //tagihan warga
                //Cari transaksi berdasarkan ID
            $invoiceOrder = GenerateOrder::find($order_id);
            $tagIds = $invoiceOrder->tag_ids;
            $arrIds = explode(",",$tagIds);

            foreach($arrIds as $tag_id) {

                //cek Ids
                $tagihan = Invoice::findOrFail((int)$tag_id);

                $dataPeriodeTagihan = Periodetagihan::find($tagihan->pt_id);
                $dataWarga = Warga::find($tagihan->warga_id);

                //Handle notifikasi status midtrans
                if($status == 'capture') {
                    if($type == 'credit_card') {
                        if ($fraud == 'challenge') {
                            $tagihan->tag_status = null;
                            $tagihan->tag_catatan_bayar = 'Pembayaran via '.$type.' in challenge';
                            $tagihan->save();
                        }
                        else {
                            $tagihan->tag_status = '1'; //success
                            $tagihan->tag_cara_bayar = '2'; //Midtrans Payment
                            $tagihan->tag_catatan_bayar = 'Pembayaran via '.$type.' berhasil';
                            $tagihan->tag_tgl_bayar = Carbon::now();
                            $tagihan->tag_jumlah_bayar = $tagihan->tag_total;
                            $tagihan->save();

                            Keuangan::create([
                                'tag_id' => $tagihan->tag_id,
                                'keu_tgl' => Carbon::now(),
                                'keu_tgl_short' => date('Y-m-d'),
                                'keu_status' => 1,
                                'keu_sumbertujuan' => 'WARGA',
                                'keu_deskripsi' => 'Pembayaran tagihan via '.$type.'  periode '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).'/'.$dataPeriodeTagihan->pt_tahun.' oleh : '.$dataWarga->warga_nama_depan.' '.$dataWarga->warga_nama_belakang,
                                'keu_nominal' => $tagihan->tag_total,
                                'wil_id' => $dataWarga->wil_id,
                                'created_at' => Carbon::now()
                            ]);

                            $warga = new Warga;
                            $itemWarga = $warga->get_detail($tagihan->warga_id);

                            $endpoint = "https://fcm.googleapis.com/fcm/send";
                            $client = new \GuzzleHttp\Client();

                            $fcm_token = $itemWarga->fcm_token;
                            $title = 'Halo, '.$itemWarga->warga_nama_depan;
                            $body = 'Lunas!. Tagihan '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).' '.$dataPeriodeTagihan->pt_tahun.' telah dibayar.';

                            Notifikasi::create([
                                'warga_id' => $itemWarga->warga_id,
                                'notif_title' => substr($title,0,100),
                                'notif_body' => substr($body,0,255),
                                'notif_page' => 'tagihan_lunas',
                                'page_id' => $tag_id,
                                'page_sts' => null,
                                'notif_date' => Carbon::now()
                            ]);

                            //create json data
                            $data_json = [
                                'notification' => [
                                    'title' => $title,
                                    'body' => $body,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    'sound'	=> 'alarm.mp3'
                                ],
                                'data' => [
                                    'id' => $tag_id,
                                    'panic_tgl' => '',
                                    'panic_jam' => '',
                                    'panic_sts' => '',
                                    'page' => 'tagihan_lunas'
                                ],
                                'to' => ''.$fcm_token.'',
                                'collapse_key' => 'type_a',
                            ];

                            $requestAPI = $client->post($endpoint, [
                                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                                'body' => json_encode($data_json)
                            ]);

                        }
                    }
                }else if($status == 'settlement') {
                    $tagihan->tag_status = '1'; //success
                    $tagihan->tag_cara_bayar = '2'; //Midtrans Payment
                    $tagihan->tag_catatan_bayar = 'Pembayaran via '.$type.' berhasil';
                    $tagihan->tag_tgl_bayar = Carbon::now();
                    $tagihan->tag_jumlah_bayar = $tagihan->tag_total;
                    $tagihan->save();

                    Keuangan::create([
                        'tag_id' => $tagihan->tag_id,
                        'keu_tgl' => Carbon::now(),
                        'keu_tgl_short' => date('Y-m-d'),
                        'keu_status' => 1,
                        'keu_sumbertujuan' => 'WARGA',
                        'keu_deskripsi' => 'Pembayaran tagihan via '.$type.' periode '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).'/'.$dataPeriodeTagihan->pt_tahun.' oleh : '.$dataWarga->warga_nama_depan.' '.$dataWarga->warga_nama_belakang,
                        'keu_nominal' => $tagihan->tag_total,
                        'wil_id' => $dataWarga->wil_id,
                        'created_at' => Carbon::now()
                    ]);

                    $warga = new Warga;
                    $itemWarga = $warga->get_detail($tagihan->warga_id);

                    $endpoint = "https://fcm.googleapis.com/fcm/send";
                    $client = new \GuzzleHttp\Client();

                    $fcm_token = $itemWarga->fcm_token;
                    $title = 'Halo, '.$itemWarga->warga_nama_depan;
                    $body = 'Lunas!. Tagihan '.Periodetagihan::getMonthName($dataPeriodeTagihan->pt_bulan).' '.$dataPeriodeTagihan->pt_tahun.' telah dibayar.';

                    Notifikasi::create([
                        'warga_id' => $itemWarga->warga_id,
                        'notif_title' => substr($title,0,100),
                        'notif_body' => substr($body,0,255),
                        'notif_page' => 'tagihan_lunas',
                        'page_id' => $tag_id,
                        'page_sts' => null,
                        'notif_date' => Carbon::now()
                    ]);

                    //create json data
                    $data_json = [
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound'	=> 'alarm.mp3'
                        ],
                        'data' => [
                            'id' => $tag_id,
                            'panic_tgl' => '',
                            'panic_jam' => '',
                            'panic_sts' => '',
                            'page' => 'tagihan_lunas'
                        ],
                        'to' => ''.$fcm_token.'',
                        'collapse_key' => 'type_a',
                    ];

                    $requestAPI = $client->post($endpoint, [
                        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                        'body' => json_encode($data_json)
                    ]);

                }else if($status == 'pending') {
                    $tagihan->tag_status = null;
                    $tagihan->tag_catatan_bayar = 'Pembayaran via midtrans pending';
                    $tagihan->save();
                }else if($status == 'deny') {
                    $tagihan->tag_status = null;
                    $tagihan->tag_catatan_bayar = 'Pembayaran via midtrans batal';
                    $tagihan->save();
                }else if($status == 'expire') {
                    $tagihan->tag_status = null;
                    $tagihan->tag_catatan_bayar = 'Pembayaran via midtrans expire';
                    $tagihan->save();
                }else if($status == 'cancel') {
                    $tagihan->tag_status = null;
                    $tagihan->tag_catatan_bayar = 'Pembayaran via midtrans batal';
                    $tagihan->save();
                }
            }

            //Simpan transaksi
        }

    }

    public static function callback_newsubsciber($order_no, $status, $type, $fraud) {
        if($status == 'capture') {
            if($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    //do nothing
                }
                else {

                    $orderData = GenerateSubscribeOrder::find($order_no);
                    $wilayah = Wilayah::find($orderData->wil_id);
                    $paketLangganan = PaketLangganan::find($orderData->pl_id);

                    $billing = new Billing;
                    $billing->pl_id = $orderData->pl_id;
                    $billing->wil_id = $orderData->wil_id;
                    $billing->wil_nama = $wilayah->wil_nama;
                    $billing->bil_date = Carbon::now();
                    $billing->bil_mulai = Carbon::now();
                    $billing->bil_akhir = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                    $billing->bil_no = Billing::generateBillNo($orderData->wil_id);
                    $billing->bil_jumlah = $paketLangganan->pl_harga;
                    $billing->bil_status = '1';
                    $billing->bil_tgl_bayar = Carbon::now();
                    $billing->bil_cara_bayar = $type;
                    $billing->bil_jml_bayar = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                    $billing->order_no = $order_no;
                    $billing->bil_catatan = 'New subscription via '.$type;
                    $billing->save();

                    $wilayah->wil_id = $orderData->wil_id;
                    $wilayah->pl_id = $paketLangganan->pl_id;
                    $wilayah->wil_mulai_langganan = Carbon::now();
                    $wilayah->wil_expire = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                    $wilayah->wil_status = '4'; //Berlangganan
                    $wilayah->wil_jml_warga = $paketLangganan->pl_maks_warga;
                    $wilayah->save();


                    $orderData->order_no =  $order_no;
                    $orderData->bil_id = $billing->bil_id;
                    $orderData->total_pembayaran = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                    $orderData->tgl_pembayaran = Carbon::now();
                    $orderData->save();

                    //Cek Voucher Wilayah
                    $vw_id = $orderData->vw_id;
                    if(!empty($vw_id)) {
                        $voucherWil = VoucherWil::find($vw_id);
                        if(!empty($voucherWil)) {
                            $voucherWil->vw_status = '1';
                            $voucherWil->vw_tgl_pakai = Carbon::now();
                            $voucherWil->save();

                            $billing = Billing::find($billing->bil_id);
                            $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                            $billing->save();

                            Keuangan::create([
                                'keu_tgl' => Carbon::now(),
                                'keu_tgl_short' => date('Y-m-d'),
                                'keu_status' => 0,
                                'keu_sumbertujuan' => 'WILAYAH',
                                'keu_deskripsi' => 'New subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.'),
                                'keu_nominal' => ($paketLangganan->pl_harga - $orderData->nominal_discount),
                                'wil_id' => $orderData->wil_id,
                                'created_at' => Carbon::now()
                            ]);
                        }
                    }else {
                        Keuangan::create([
                            'keu_tgl' => Carbon::now(),
                            'keu_tgl_short' => date('Y-m-d'),
                            'keu_status' => 0,
                            'keu_sumbertujuan' => 'WILAYAH',
                            'keu_deskripsi' => 'New subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                            'keu_nominal' => $paketLangganan->pl_harga,
                            'wil_id' => $orderData->wil_id,
                            'created_at' => Carbon::now()
                        ]);
                    }
                    //end cek voucher wilayah

                    $warga = new Warga;
                    $dataPengurus = $warga->get_pengurus_with_token($orderData->wil_id);

                    foreach($dataPengurus as $pengurus) {
                        //send to user warga
                        $endpoint = "https://fcm.googleapis.com/fcm/send";
                        $client = new \GuzzleHttp\Client();

                        $fcm_token = $pengurus->fcm_token;
                        $title = 'Selamat Berlangganan!';
                        $body = 'Terima kasih karena Anda telah berlangganan Rukun dengan Paket '.$paketLangganan->pl_nama;

                        Notifikasi::create([
                            'warga_id' => $pengurus->warga_id,
                            'notif_title' => substr($title,0,100),
                            'notif_body' => substr($body,0,255),
                            'notif_page' => 'new_subscriber',
                            'page_id' => null,
                            'page_sts' => null,
                            'notif_date' => Carbon::now()
                        ]);

                        //create json data
                        $data_json = [
                                'notification' => [
                                    'title' => $title,
                                    'body' => $body,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    'sound'	=> 'alarm.mp3'
                                ],
                                'data' => [
                                    'id' => $billing->bil_id,
                                    'panic_tgl' => '',
                                    'panic_jam' => '',
                                    'panic_sts' => '',
                                    'page' => 'new_subscriber'
                                ],
                                'to' => ''.$fcm_token.'',
                                'collapse_key' => 'type_a',
                            ];

                        $requestAPI = $client->post($endpoint, [
                            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                            'body' => json_encode($data_json)
                        ]);
                    }

                }
            }
        }else if($status == 'settlement') {
            $orderData = GenerateSubscribeOrder::find($order_no);
            $wilayah = Wilayah::find($orderData->wil_id);
            $paketLangganan = PaketLangganan::find($orderData->pl_id);

            $billing = new Billing;
            $billing->pl_id = $orderData->pl_id;
            $billing->wil_id = $orderData->wil_id;
            $billing->wil_nama = $wilayah->wil_nama;
            $billing->bil_date = Carbon::now();
            $billing->bil_mulai = Carbon::now();
            $billing->bil_akhir = Carbon::now()->addMonths($paketLangganan->pl_bulan);
            $billing->bil_no = Billing::generateBillNo($orderData->wil_id);
            $billing->bil_jumlah = $paketLangganan->pl_harga;
            $billing->bil_status = '1';
            $billing->bil_tgl_bayar = Carbon::now();
            $billing->bil_cara_bayar = $type;
            $billing->bil_jml_bayar = ($paketLangganan->pl_harga - $orderData->nominal_discount);
            $billing->order_no = $order_no;
            $billing->bil_catatan = 'New subscription via '.$type;
            $billing->save();

            $wilayah->wil_id = $orderData->wil_id;
            $wilayah->pl_id = $paketLangganan->pl_id;
            $wilayah->wil_mulai_langganan = Carbon::now();
            $wilayah->wil_expire = Carbon::now()->addMonths($paketLangganan->pl_bulan);
            $wilayah->wil_status = '4'; //Berlangganan
            $wilayah->wil_jml_warga = $paketLangganan->pl_maks_warga;
            $wilayah->save();

            $orderData->order_no =  $order_no;
            $orderData->bil_id = $billing->bil_id;
            $orderData->total_pembayaran = ($paketLangganan->pl_harga - $orderData->nominal_discount);
            $orderData->tgl_pembayaran = Carbon::now();
            $orderData->save();

            //Cek Voucher Wilayah
            $vw_id = $orderData->vw_id;
            if(!empty($vw_id)) {
                $voucherWil = VoucherWil::find($vw_id);
                if(!empty($voucherWil)) {
                    $voucherWil->vw_status = '1';
                    $voucherWil->vw_tgl_pakai = Carbon::now();
                    $voucherWil->save();

                    $billing = Billing::find($billing->bil_id);
                    $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                    $billing->save();

                    Keuangan::create([
                        'keu_tgl' => Carbon::now(),
                        'keu_tgl_short' => date('Y-m-d'),
                        'keu_status' => 0,
                        'keu_sumbertujuan' => 'WILAYAH',
                        'keu_deskripsi' => 'New subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.'),
                        'keu_nominal' => ($paketLangganan->pl_harga - $orderData->nominal_discount),
                        'wil_id' => $orderData->wil_id,
                        'created_at' => Carbon::now()
                    ]);

                }
            }else {
                Keuangan::create([
                    'keu_tgl' => Carbon::now(),
                    'keu_tgl_short' => date('Y-m-d'),
                    'keu_status' => 0,
                    'keu_sumbertujuan' => 'WILAYAH',
                    'keu_deskripsi' => 'New subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                    'keu_nominal' => $paketLangganan->pl_harga,
                    'wil_id' => $orderData->wil_id,
                    'created_at' => Carbon::now()
                ]);
            }
            //end cek voucher wilayah

            $warga = new Warga;
            $dataPengurus = $warga->get_pengurus_with_token($orderData->wil_id);

            foreach($dataPengurus as $pengurus) {
                //send to user warga
                $endpoint = "https://fcm.googleapis.com/fcm/send";
                $client = new \GuzzleHttp\Client();

                $fcm_token = $pengurus->fcm_token;
                $title = 'Selamat Berlangganan!';
                $body = 'Terima kasih karena Anda telah berlangganan Rukun dengan Paket '.$paketLangganan->pl_nama;

                Notifikasi::create([
                    'warga_id' => $pengurus->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => substr($body,0,255),
                    'notif_page' => 'new_subscriber',
                    'page_id' => null,
                    'page_sts' => null,
                    'notif_date' => Carbon::now()
                ]);

                //create json data
                $data_json = [
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound'	=> 'alarm.mp3'
                        ],
                        'data' => [
                            'id' => $billing->bil_id,
                            'panic_tgl' => '',
                            'panic_jam' => '',
                            'panic_sts' => '',
                            'page' => 'new_subscriber'
                        ],
                        'to' => ''.$fcm_token.'',
                        'collapse_key' => 'type_a',
                    ];

                $requestAPI = $client->post($endpoint, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                    'body' => json_encode($data_json)
                ]);
            }

        }else if($status == 'pending') {

        }else if($status == 'deny') {

        }else if($status == 'expire') {

        }else if($status == 'cancel') {

        }
    }

    public static function callback_extend_subscription($order_no, $status, $type, $fraud) {
        if($status == 'capture') {
            if($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    //do nothing
                }
                else {

                    $orderData = GenerateSubscribeOrder::find($order_no);
                    $wilayah = Wilayah::find($orderData->wil_id);
                    $paketLangganan = PaketLangganan::find($orderData->pl_id);

                    $billing = Billing::find($orderData->bil_id);
                    $billing->bil_id = $orderData->bil_id;
                    $billing->bil_status = '1';
                    $billing->bil_tgl_bayar = Carbon::now();
                    $billing->bil_cara_bayar = $type;
                    $billing->bil_jml_bayar = ($billing->bil_jumlah - $orderData->nominal_discount);
                    $billing->order_no = $order_no;
                    $billing->bil_catatan = 'Pembayaran tagihan langganan via '.$type;
                    $billing->save();

                    $wilayah->wil_id = $orderData->wil_id;
                    $wilayah->pl_id = $paketLangganan->pl_id;
                    //$wilayah->wil_mulai_langganan = $billing->bil_mulai;
                    //$wilayah->wil_expire = Carbon::parse($billing->bil_mulai)->addMonths($paketLangganan->pl_bulan);
                    //$wilayah->wil_mulai_langganan = Carbon::now();
                    //$wilayah->wil_expire = Carbon::now()->addMonths($paketLangganan->pl_bulan);

                    $wilayah->wil_mulai_langganan = Carbon::now();
                    $wilayah->wil_expire = Carbon::parse($billing->bil_mulai)->addMonths($paketLangganan->pl_bulan);

                    $wilayah->wil_status = '4'; //Berlangganan
                    $wilayah->wil_jml_warga = $paketLangganan->pl_maks_warga;
                    $wilayah->save();

                    $orderData->order_no =  $order_no;
                    $orderData->bil_id = $billing->bil_id;
                    $orderData->total_pembayaran = ($billing->bil_jumlah - $orderData->nominal_discount);
                    $orderData->tgl_pembayaran = Carbon::now();
                    $orderData->save();

                    //Cek Voucher Wilayah
                    $vw_id = $orderData->vw_id;
                    if(!empty($vw_id)) {
                        $voucherWil = VoucherWil::find($vw_id);
                        if(!empty($voucherWil)) {
                            $voucherWil->vw_status = '1';
                            $voucherWil->vw_tgl_pakai = Carbon::now();
                            $voucherWil->save();

                            $billing = Billing::find($billing->bil_id);
                            $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                            $billing->save();

                            Keuangan::create([
                                'keu_tgl' => Carbon::now(),
                                'keu_tgl_short' => date('Y-m-d'),
                                'keu_status' => 0,
                                'keu_sumbertujuan' => 'WILAYAH',
                                'keu_deskripsi' => 'Pembayaran tagihan langganan via '.$type.' untuk paket '.$paketLangganan->pl_nama.' with Voucher Discount Rp.'.number_format($orderData->nominal_discount,0,',','.'),
                                'keu_nominal' => ($billing->bil_jumlah - $orderData->nominal_discount),
                                'wil_id' => $orderData->wil_id,
                                'created_at' => Carbon::now()
                            ]);

                        }
                    }else {
                        Keuangan::create([
                            'keu_tgl' => Carbon::now(),
                            'keu_tgl_short' => date('Y-m-d'),
                            'keu_status' => 0,
                            'keu_sumbertujuan' => 'WILAYAH',
                            'keu_deskripsi' => 'Pembayaran tagihan langganan via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                            'keu_nominal' => $billing->bil_jumlah,
                            'wil_id' => $orderData->wil_id,
                            'created_at' => Carbon::now()
                        ]);
                    }
                    //end cek voucher wilayah

                    $warga = new Warga;
                    $dataPengurus = $warga->get_pengurus_with_token($orderData->wil_id);

                    foreach($dataPengurus as $pengurus) {
                        //send to user warga
                        $endpoint = "https://fcm.googleapis.com/fcm/send";
                        $client = new \GuzzleHttp\Client();

                        $fcm_token = $pengurus->fcm_token;
                        $title = 'Perpanjang Langganan Berhasil';
                        $body = 'Terima kasih karena Anda telah berlangganan Rukun dengan Paket '.$paketLangganan->pl_nama;

                        Notifikasi::create([
                            'warga_id' => $pengurus->warga_id,
                            'notif_title' => substr($title,0,100),
                            'notif_body' => substr($body,0,255),
                            'notif_page' => 'extend_subscription',
                            'page_id' => null,
                            'page_sts' => null,
                            'notif_date' => Carbon::now()
                        ]);

                        //create json data
                        $data_json = [
                                'notification' => [
                                    'title' => $title,
                                    'body' => $body,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    'sound'	=> 'alarm.mp3'
                                ],
                                'data' => [
                                    'id' => $billing->bil_id,
                                    'panic_tgl' => '',
                                    'panic_jam' => '',
                                    'panic_sts' => '',
                                    'page' => 'extend_subscription'
                                ],
                                'to' => ''.$fcm_token.'',
                                'collapse_key' => 'type_a',
                            ];

                        $requestAPI = $client->post($endpoint, [
                            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                            'body' => json_encode($data_json)
                        ]);
                    }

                }
            }
        }else if($status == 'settlement') {
            $orderData = GenerateSubscribeOrder::find($order_no);
            $wilayah = Wilayah::find($orderData->wil_id);
            $paketLangganan = PaketLangganan::find($orderData->pl_id);

            $billing = Billing::find($orderData->bil_id);
            $billing->bil_id = $orderData->bil_id;
            $billing->bil_status = '1';
            $billing->bil_tgl_bayar = Carbon::now();
            $billing->bil_cara_bayar = $type;
            $billing->bil_jml_bayar = ($billing->bil_jumlah - $orderData->nominal_discount);
            $billing->order_no = $order_no;
            $billing->bil_catatan = 'Pembayaran tagihan langganan via '.$type;
            $billing->save();

            $wilayah->wil_id = $orderData->wil_id;
            $wilayah->pl_id = $paketLangganan->pl_id;
            //$wilayah->wil_mulai_langganan = $billing->bil_mulai;
            //$wilayah->wil_expire = Carbon::parse($billing->bil_mulai)->addMonths($paketLangganan->pl_bulan);
            //$wilayah->wil_expire = Carbon::now()->addMonths($paketLangganan->pl_bulan);

            $wilayah->wil_mulai_langganan = Carbon::now();
            $wilayah->wil_expire = Carbon::parse($billing->bil_mulai)->addMonths($paketLangganan->pl_bulan);

            $wilayah->wil_status = '4'; //Berlangganan
            $wilayah->wil_jml_warga = $paketLangganan->pl_maks_warga;
            $wilayah->save();

            $orderData->order_no =  $order_no;
            $orderData->bil_id = $billing->bil_id;
            $orderData->total_pembayaran = ($billing->bil_jumlah - $orderData->nominal_discount);
            $orderData->tgl_pembayaran = Carbon::now();
            $orderData->save();

            //Cek Voucher Wilayah
            $vw_id = $orderData->vw_id;
            if(!empty($vw_id)) {
                $voucherWil = VoucherWil::find($vw_id);
                if(!empty($voucherWil)) {
                    $voucherWil->vw_status = '1';
                    $voucherWil->vw_tgl_pakai = Carbon::now();
                    $voucherWil->save();

                    $billing = Billing::find($billing->bil_id);
                    $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                    $billing->save();

                    Keuangan::create([
                        'keu_tgl' => Carbon::now(),
                        'keu_tgl_short' => date('Y-m-d'),
                        'keu_status' => 0,
                        'keu_sumbertujuan' => 'WILAYAH',
                        'keu_deskripsi' => 'Pembayaran tagihan langganan via '.$type.' untuk paket '.$paketLangganan->pl_nama.'. with Voucher Discount Rp.'.number_format($orderData->nominal_discount,0,',','.'),
                        'keu_nominal' => ($billing->bil_jumlah - $orderData->nominal_discount),
                        'wil_id' => $orderData->wil_id,
                        'created_at' => Carbon::now()
                    ]);

                }
            }else {
                Keuangan::create([
                    'keu_tgl' => Carbon::now(),
                    'keu_tgl_short' => date('Y-m-d'),
                    'keu_status' => 0,
                    'keu_sumbertujuan' => 'WILAYAH',
                    'keu_deskripsi' => 'Pembayaran tagihan langganan via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                    'keu_nominal' => $billing->bil_jumlah,
                    'wil_id' => $orderData->wil_id,
                    'created_at' => Carbon::now()
                ]);
            }
            //end cek voucher wilayah

            $warga = new Warga;
            $dataPengurus = $warga->get_pengurus_with_token($orderData->wil_id);

            foreach($dataPengurus as $pengurus) {
                //send to user warga
                $endpoint = "https://fcm.googleapis.com/fcm/send";
                $client = new \GuzzleHttp\Client();

                $fcm_token = $pengurus->fcm_token;
                $title = 'Perpanjang Langganan Berhasil';
                $body = 'Terima kasih karena Anda telah berlangganan Rukun dengan Paket '.$paketLangganan->pl_nama;

                Notifikasi::create([
                    'warga_id' => $pengurus->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => substr($body,0,255),
                    'notif_page' => 'extend_subscription',
                    'page_id' => null,
                    'page_sts' => null,
                    'notif_date' => Carbon::now()
                ]);

                //create json data
                $data_json = [
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound'	=> 'alarm.mp3'
                        ],
                        'data' => [
                            'id' => $billing->bil_id,
                            'panic_tgl' => '',
                            'panic_jam' => '',
                            'panic_sts' => '',
                            'page' => 'extend_subscription'
                        ],
                        'to' => ''.$fcm_token.'',
                        'collapse_key' => 'type_a',
                    ];

                $requestAPI = $client->post($endpoint, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                    'body' => json_encode($data_json)
                ]);
            }

        }else if($status == 'pending') {

        }else if($status == 'deny') {

        }else if($status == 'expire') {

        }else if($status == 'cancel') {

        }
    }

    public static function callback_upgrade_subscription($order_no, $status, $type, $fraud) {
        if($status == 'capture') {
            if($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    //do nothing
                }
                else {

                    $orderData = GenerateSubscribeOrder::find($order_no);
                    $wilayah = Wilayah::find($orderData->wil_id);
                    $paketLangganan = PaketLangganan::find($orderData->pl_id);

                    $billing = new Billing;
                    $recentBilling = $billing->getRecentBilling($orderData->wil_id);
                    if(empty($recentBilling)) { //kondisi jika tidak ada tagihan di table billing
                        $billing->pl_id = $orderData->pl_id;
                        $billing->wil_id = $orderData->wil_id;
                        $billing->wil_nama = $wilayah->wil_nama;
                        $billing->bil_date = Carbon::now();
                        $billing->bil_due = Carbon::now();
                        if($wilayah->wil_status == '4') {
                            //$billing->bil_mulai = Carbon::parse($wilayah->wil_expire);
                            $billing->bil_mulai = Carbon::now();
                            $billing->bil_akhir = Carbon::parse($wilayah->wil_expire)->addMonths($paketLangganan->pl_bulan);
                        }else {
                            $billing->bil_mulai = Carbon::now();
                            $billing->bil_akhir = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                        }
                        $billing->bil_no = Billing::generateBillNo($orderData->wil_id);
                        $billing->bil_jumlah = $paketLangganan->pl_harga;
                        $billing->bil_status = '1';
                        $billing->bil_tgl_bayar = Carbon::now();
                        $billing->bil_cara_bayar = $type;
                        //$billing->bil_jml_bayar = $paketLangganan->pl_harga;
                        $billing->bil_jml_bayar = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                        $billing->order_no = $order_no;
                        $billing->bil_catatan = 'Upgrade Paket. Pembayaran langganan via '.$type;
                        $billing->save();

                        $orderData->order_no =  $order_no;
                        $orderData->bil_id = $billing->bil_id;
                        $orderData->total_pembayaran = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                        $orderData->tgl_pembayaran = Carbon::now();
                        $orderData->save();


                        //cek voucher
                        $vw_id = $orderData->vw_id;
                        if(!empty($vw_id)) {
                            $voucherWil = VoucherWil::find($vw_id);
                            if(!empty($voucherWil)) {
                                $voucherWil->vw_status = '1';
                                $voucherWil->vw_tgl_pakai = Carbon::now();
                                $voucherWil->save();

                                $billing = Billing::find($billing->bil_id);
                                $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                                $billing->save();

                                Keuangan::create([
                                    'keu_tgl' => Carbon::now(),
                                    'keu_tgl_short' => date('Y-m-d'),
                                    'keu_status' => 0,
                                    'keu_sumbertujuan' => 'WILAYAH',
                                    'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.'),
                                    'keu_nominal' => ($paketLangganan->pl_harga - $orderData->nominal_discount),
                                    'wil_id' => $orderData->wil_id,
                                    'created_at' => Carbon::now()
                                ]);

                            }
                        }else {
                            Keuangan::create([
                                'keu_tgl' => Carbon::now(),
                                'keu_tgl_short' => date('Y-m-d'),
                                'keu_status' => 0,
                                'keu_sumbertujuan' => 'WILAYAH',
                                'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                                'keu_nominal' => $paketLangganan->pl_harga,
                                'wil_id' => $orderData->wil_id,
                                'created_at' => Carbon::now()
                            ]);
                        }

                    }else { //kondisi jika ada tagihan
                        $billing->bil_id = $recentBilling->bil_id;
                        $billing->bil_status = '2'; //Cancel Billing
                        $billing->bil_catatan = 'Billing dibatalkan karena upgrade paket';
                        $billing->order_no = $order_no;
                        $billing->save();

                        $billingNew = new Billing;
                        $billingNew->pl_id = $orderData->pl_id;
                        $billingNew->wil_id = $orderData->wil_id;
                        $billingNew->wil_nama = $wilayah->wil_nama;
                        $billingNew->bil_date = Carbon::now();
                        $billingNew->bil_due = Carbon::now();
                        if($wilayah->wil_status == '4') {
                            //$billingNew->bil_mulai = Carbon::parse($wilayah->wil_expire);
                            $billingNew->bil_mulai = Carbon::now();
                            $billingNew->bil_akhir = Carbon::parse($wilayah->wil_expire)->addMonths($paketLangganan->pl_bulan);
                        }else {
                            $billingNew->bil_mulai = Carbon::now();
                            $billingNew->bil_akhir = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                        }
                        $billingNew->bil_no = Billing::generateBillNo($orderData->wil_id);
                        $billingNew->bil_jumlah = $paketLangganan->pl_harga;
                        $billingNew->bil_status = '1';
                        $billingNew->bil_tgl_bayar = Carbon::now();
                        $billingNew->bil_cara_bayar = $type;
                        //$billingNew->bil_jml_bayar = $paketLangganan->pl_harga;
                        $billingNew->bil_jml_bayar = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                        $billingNew->order_no = $order_no;
                        $billingNew->bil_catatan = 'Upgrade Paket (Tagihan sebelumnya dibatalkan). Pembayaran langganan via '.$type;
                        $billingNew->save();

                        $orderData->order_no =  $order_no;
                        $orderData->bil_id = $billingNew->bil_id;
                        $orderData->total_pembayaran = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                        $orderData->tgl_pembayaran = Carbon::now();
                        $orderData->save();

                        //cek voucher
                        $vw_id = $orderData->vw_id;
                        if(!empty($vw_id)) {
                            $voucherWil = VoucherWil::find($vw_id);
                            if(!empty($voucherWil)) {
                                $voucherWil->vw_status = '1';
                                $voucherWil->vw_tgl_pakai = Carbon::now();
                                $voucherWil->save();

                                $billing = Billing::find($billing->bil_id);
                                $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                                $billing->save();

                                Keuangan::create([
                                    'keu_tgl' => Carbon::now(),
                                    'keu_tgl_short' => date('Y-m-d'),
                                    'keu_status' => 0,
                                    'keu_sumbertujuan' => 'WILAYAH',
                                    'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.'),
                                    'keu_nominal' => ($paketLangganan->pl_harga - $orderData->nominal_discount),
                                    'wil_id' => $orderData->wil_id,
                                    'created_at' => Carbon::now()
                                ]);

                            }
                        }else {
                            Keuangan::create([
                                'keu_tgl' => Carbon::now(),
                                'keu_tgl_short' => date('Y-m-d'),
                                'keu_status' => 0,
                                'keu_sumbertujuan' => 'WILAYAH',
                                'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                                'keu_nominal' => $paketLangganan->pl_harga,
                                'wil_id' => $orderData->wil_id,
                                'created_at' => Carbon::now()
                            ]);
                        }

                    }

                    $wilayah->wil_id = $orderData->wil_id;
                    $wilayah->pl_id = $paketLangganan->pl_id;

                    if($wilayah->wil_status == '4') {
                        //$wilayah->wil_mulai_langganan = Carbon::parse($wilayah->wil_expire);
                        $wilayah->wil_mulai_langganan = Carbon::now();
                        $wilayah->wil_expire = Carbon::parse($wilayah->wil_expire)->addMonths($paketLangganan->pl_bulan);
                    }else {
                        $wilayah->wil_mulai_langganan = Carbon::now();
                        $wilayah->wil_expire = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                    }

                    $wilayah->wil_status = '4'; //Berlangganan
                    $wilayah->wil_jml_warga = $paketLangganan->pl_maks_warga;
                    $wilayah->save();

                    // Keuangan::create([
                    //     'keu_tgl' => Carbon::now(),
                    //     'keu_tgl_short' => date('Y-m-d'),
                    //     'keu_status' => 0,
                    //     'keu_sumbertujuan' => 'WILAYAH',
                    //     'keu_deskripsi' => 'Upgrade langganan via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                    //     'keu_nominal' => $paketLangganan->pl_harga,
                    //     'wil_id' => $orderData->wil_id,
                    //     'created_at' => Carbon::now()
                    // ]);


                    $warga = new Warga;
                    $dataPengurus = $warga->get_pengurus_with_token($orderData->wil_id);

                    foreach($dataPengurus as $pengurus) {
                        //send to user warga
                        $endpoint = "https://fcm.googleapis.com/fcm/send";
                        $client = new \GuzzleHttp\Client();

                        $fcm_token = $pengurus->fcm_token;
                        $title = 'Paket Telah Diupgrade!';
                        $body = 'Anda telah berlangganan Rukun dengan Paket '.$paketLangganan->pl_nama;

                        Notifikasi::create([
                            'warga_id' => $pengurus->warga_id,
                            'notif_title' => substr($title,0,100),
                            'notif_body' => substr($body,0,255),
                            'notif_page' => 'upgrade_subscription',
                            'page_id' => null,
                            'page_sts' => null,
                            'notif_date' => Carbon::now()
                        ]);

                        //create json data
                        $data_json = [
                                'notification' => [
                                    'title' => $title,
                                    'body' => $body,
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    'sound'	=> 'alarm.mp3'
                                ],
                                'data' => [
                                    'id' => $billing->bil_id,
                                    'panic_tgl' => '',
                                    'panic_jam' => '',
                                    'panic_sts' => '',
                                    'page' => 'upgrade_subscription'
                                ],
                                'to' => ''.$fcm_token.'',
                                'collapse_key' => 'type_a',
                            ];

                        $requestAPI = $client->post($endpoint, [
                            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                            'body' => json_encode($data_json)
                        ]);
                    }

                }
            }
        }else if($status == 'settlement') {
            $orderData = GenerateSubscribeOrder::find($order_no);
            $wilayah = Wilayah::find($orderData->wil_id);
            $paketLangganan = PaketLangganan::find($orderData->pl_id);

            $billing = new Billing;
            $recentBilling = $billing->getRecentBilling($orderData->wil_id);
            if(empty($recentBilling)) {
                $billing->pl_id = $orderData->pl_id;
                $billing->wil_id = $orderData->wil_id;
                $billing->wil_nama = $wilayah->wil_nama;
                $billing->bil_date = Carbon::now();
                if($wilayah->wil_status == '4') {
                    //$billing->bil_mulai = Carbon::parse($wilayah->wil_expire);
                    $billing->bil_mulai = Carbon::now();
                    $billing->bil_akhir = Carbon::parse($wilayah->wil_expire)->addMonths($paketLangganan->pl_bulan);
                }else {
                    $billing->bil_mulai = Carbon::now();
                    $billing->bil_akhir = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                }
                $billing->bil_no = Billing::generateBillNo($orderData->wil_id);
                $billing->bil_jumlah = $paketLangganan->pl_harga;
                $billing->bil_status = '1';
                $billing->bil_tgl_bayar = Carbon::now();
                $billing->bil_cara_bayar = $type;
                //$billing->bil_jml_bayar = $paketLangganan->pl_harga;
                $billing->bil_jml_bayar = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                $billing->order_no = $order_no;
                $billing->bil_catatan = 'Upgrade Paket. Pembayaran langganan via '.$type;
                $billing->save();

                $orderData->order_no =  $order_no;
                $orderData->bil_id = $billing->bil_id;
                $orderData->total_pembayaran = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                $orderData->tgl_pembayaran = Carbon::now();
                $orderData->save();

                //cek voucher
                $vw_id = $orderData->vw_id;
                if(!empty($vw_id)) {
                    $voucherWil = VoucherWil::find($vw_id);
                    if(!empty($voucherWil)) {
                        $voucherWil->vw_status = '1';
                        $voucherWil->vw_tgl_pakai = Carbon::now();
                        $voucherWil->save();

                        $billing = Billing::find($billing->bil_id);
                        $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                        $billing->save();

                        Keuangan::create([
                            'keu_tgl' => Carbon::now(),
                            'keu_tgl_short' => date('Y-m-d'),
                            'keu_status' => 0,
                            'keu_sumbertujuan' => 'WILAYAH',
                            'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.'),
                            'keu_nominal' => ($paketLangganan->pl_harga - $orderData->nominal_discount),
                            'wil_id' => $orderData->wil_id,
                            'created_at' => Carbon::now()
                        ]);

                    }
                }else {
                    Keuangan::create([
                        'keu_tgl' => Carbon::now(),
                        'keu_tgl_short' => date('Y-m-d'),
                        'keu_status' => 0,
                        'keu_sumbertujuan' => 'WILAYAH',
                        'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                        'keu_nominal' => $paketLangganan->pl_harga,
                        'wil_id' => $orderData->wil_id,
                        'created_at' => Carbon::now()
                    ]);
                }
            }else {
                $billing->bil_id = $recentBilling->bil_id;
                $billing->bil_status = '2'; //Cancel Billing
                $billing->bil_catatan = 'Billing dibatalkan karena upgrade paket';
                $billing->order_no = $order_no;
                $billing->save();

                $billingNew = new Billing;
                $billingNew->pl_id = $orderData->pl_id;
                $billingNew->wil_id = $orderData->wil_id;
                $billingNew->wil_nama = $wilayah->wil_nama;
                $billingNew->bil_date = Carbon::now();
                if($wilayah->wil_status == '4') {
                    //$billingNew->bil_mulai = Carbon::parse($wilayah->wil_expire);
                    $billingNew->bil_mulai = Carbon::now();
                    $billingNew->bil_akhir = Carbon::parse($wilayah->wil_expire)->addMonths($paketLangganan->pl_bulan);
                }else {
                    $billingNew->bil_mulai = Carbon::now();
                    $billingNew->bil_akhir = Carbon::now()->addMonths($paketLangganan->pl_bulan);
                }
                $billingNew->bil_no = Billing::generateBillNo($orderData->wil_id);
                $billingNew->bil_jumlah = $paketLangganan->pl_harga;
                $billingNew->bil_status = '1';
                $billingNew->bil_tgl_bayar = Carbon::now();
                $billingNew->bil_cara_bayar = $type;
                //$billingNew->bil_jml_bayar = $paketLangganan->pl_harga;
                $billing->bil_jml_bayar = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                $billingNew->order_no = $order_no;
                $billingNew->bil_catatan = 'Upgrade Paket (Tagihan sebelumnya dibatalkan). Pembayaran langganan via '.$type;
                $billingNew->save();

                $orderData->order_no =  $order_no;
                $orderData->bil_id = $billingNew->bil_id;
                $orderData->total_pembayaran = ($paketLangganan->pl_harga - $orderData->nominal_discount);
                $orderData->tgl_pembayaran = Carbon::now();
                $orderData->save();

                //cek voucher
                $vw_id = $orderData->vw_id;
                if(!empty($vw_id)) {
                    $voucherWil = VoucherWil::find($vw_id);
                    if(!empty($voucherWil)) {
                        $voucherWil->vw_status = '1';
                        $voucherWil->vw_tgl_pakai = Carbon::now();
                        $voucherWil->save();

                        $billing = Billing::find($billing->bil_id);
                        $billing->bil_catatan = $billing->bil_catatan." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.');
                        $billing->save();

                        Keuangan::create([
                            'keu_tgl' => Carbon::now(),
                            'keu_tgl_short' => date('Y-m-d'),
                            'keu_status' => 0,
                            'keu_sumbertujuan' => 'WILAYAH',
                            'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama." with Voucher Discount Rp.".number_format($orderData->nominal_discount,0,',','.'),
                            'keu_nominal' => ($paketLangganan->pl_harga - $orderData->nominal_discount),
                            'wil_id' => $orderData->wil_id,
                            'created_at' => Carbon::now()
                        ]);

                    }
                }else {
                    Keuangan::create([
                        'keu_tgl' => Carbon::now(),
                        'keu_tgl_short' => date('Y-m-d'),
                        'keu_status' => 0,
                        'keu_sumbertujuan' => 'WILAYAH',
                        'keu_deskripsi' => 'Upgrade subscription via '.$type.' untuk paket '.$paketLangganan->pl_nama,
                        'keu_nominal' => $paketLangganan->pl_harga,
                        'wil_id' => $orderData->wil_id,
                        'created_at' => Carbon::now()
                    ]);
                }
            }

            $wilayah->wil_id = $orderData->wil_id;
            $wilayah->pl_id = $paketLangganan->pl_id;

            if($wilayah->wil_status == '4') {
                //$wilayah->wil_mulai_langganan = Carbon::parse($wilayah->wil_expire);
                $wilayah->wil_mulai_langganan = Carbon::now();
                $wilayah->wil_expire = Carbon::parse($wilayah->wil_expire)->addMonths($paketLangganan->pl_bulan);
            }else {
                $wilayah->wil_mulai_langganan = Carbon::now();
                $wilayah->wil_expire = Carbon::now()->addMonths($paketLangganan->pl_bulan);
            }

            $wilayah->wil_status = '4'; //Berlangganan
            $wilayah->wil_jml_warga = $paketLangganan->pl_maks_warga;
            $wilayah->save();

            // Keuangan::create([
            //     'keu_tgl' => Carbon::now(),
            //     'keu_tgl_short' => date('Y-m-d'),
            //     'keu_status' => 0,
            //     'keu_sumbertujuan' => 'WILAYAH',
            //     'keu_deskripsi' => 'Upgrade langganan via '.$type.' untuk paket '.$paketLangganan->pl_nama,
            //     'keu_nominal' => $paketLangganan->pl_harga,
            //     'wil_id' => $orderData->wil_id,
            //     'created_at' => Carbon::now()
            // ]);


            $warga = new Warga;
            $dataPengurus = $warga->get_pengurus_with_token($orderData->wil_id);

            foreach($dataPengurus as $pengurus) {
                //send to user warga
                $endpoint = "https://fcm.googleapis.com/fcm/send";
                $client = new \GuzzleHttp\Client();

                $fcm_token = $pengurus->fcm_token;
                $title = 'Paket Telah Diupgrade!';
                $body = 'Terima kasih, Anda telah berlangganan Rukun dengan Paket '.$paketLangganan->pl_nama;

                Notifikasi::create([
                    'warga_id' => $pengurus->warga_id,
                    'notif_title' => substr($title,0,100),
                    'notif_body' => substr($body,0,255),
                    'notif_page' => 'upgrade_subscription',
                    'page_id' => null,
                    'page_sts' => null,
                    'notif_date' => Carbon::now()
                ]);

                //create json data
                $data_json = [
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'sound'	=> 'alarm.mp3'
                        ],
                        'data' => [
                            'id' => $billing->bil_id,
                            'panic_tgl' => '',
                            'panic_jam' => '',
                            'panic_sts' => '',
                            'page' => 'upgrade_subscription'
                        ],
                        'to' => ''.$fcm_token.'',
                        'collapse_key' => 'type_a',
                    ];

                $requestAPI = $client->post($endpoint, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'key=AAAAqSEAz7w:APA91bHh59SNZMXJQ4nNOysrsHF28KN4b07D4lHTROLIDn-QMYDuvFfSnpCoi7ExNYdVv8OT3wyZSUbQSdK2uHKYQ6q9R6Q1dqFGYX8WGfLwBEwJkHOFDHxpREOdGdmvGqHEAiewDq6_'],
                    'body' => json_encode($data_json)
                ]);
            }

        }else if($status == 'pending') {

        }else if($status == 'deny') {

        }else if($status == 'expire') {

        }else if($status == 'cancel') {

        }
    }

    public function success() {
        return view('midtrans.success');
    }

    public function unfinish() {
        return view('midtrans.unfinish');
    }

    public function error() {
        return view('midtrans.error');
    }
}
