<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
Route::get('contoh', 'ContohController@index');

Route::get('contoh/{id}', 'ContohController@show');

Route::post('contoh/save', 'ContohController@store');

Route::post('contoh/{id}/update', 'ContohController@update');

Route::post('contoh/{id}/delete', 'ContohController@destroy');


Route::group(['middleware' => 'ValidateKey'], function () {

    // Country
    Route::get('prov', 'Api\CountryController@prov_list');
    Route::get('kab/{prop_id}', 'Api\CountryController@kab_list');
    Route::get('kec/{kabkot_id}', 'Api\CountryController@kec_list');
    Route::get('kel/{kec_id}', 'Api\CountryController@kel_list');
    //
    Route::post('provinsi', 'Api\CountryController@provinsi_list');
    Route::post('kabupaten', 'Api\CountryController@kabupaten_list');
    Route::post('kecamatan', 'Api\CountryController@kecamatan_list');
    Route::post('kelurahan', 'Api\CountryController@kelurahan_list');
    //
    Route::get('kel/kel_nama/{kel_id}', 'Api\CountryController@kel_nama');

    //tarik dari kelurahan > kec > kab > prov
    Route::get('prov_kab/{kab_id}', 'Api\CountryController@prov_kab');
    Route::get('kab_kec/{kec_id}', 'Api\CountryController@kab_kec');
    Route::get('kec_kel/{kel_id}', 'Api\CountryController@kec_kel');

    //
    Route::get('convert/prov', 'Api\CountryController@prov');
    Route::get('convert/kab', 'Api\CountryController@kab');
    Route::get('convert/kec', 'Api\CountryController@kec');
    Route::get('convert/kel', 'Api\CountryController@kel');

    // Wilayah
    Route::get('wilayah/list', 'Api\WilayahController@list');
    //Route::get('wilayah/detailcrm/{wil_id}', 'Api\WilayahController@detailcrm');
    Route::post('wilayah/detail', 'Api\WilayahController@detail');
    //Route::get('wilayah/detailadmin/{wil_id}', 'Api\WilayahController@detailadmin');
    Route::post('wilayah/update-foto', 'Api\WilayahController@update_foto');
    Route::post('wilayah/update-logo', 'Api\WilayahController@update_logo');
    Route::post('wilayah/update-rekening', 'Api\WilayahController@update_rekening');
    Route::post('wilayah/update', 'Api\WilayahController@update');
    //Route::post('wilayah/update_crm', 'Api\WilayahController@update_crm');
    Route::post('wilayah/h_5_trial', 'Api\WilayahController@h_5_trial');
    Route::post('wilayah/get_status', 'Api\WilayahController@get_status');
    Route::post('wilayah/generate_code', 'Api\WilayahController@generate_code');


    //Wilayah Statistik
    Route::post('wilayah_statistik/detail', 'Api\WilayahStatistikController@detail');
    Route::post('wilayah_statistik/reminder_warga_unregistered', 'Api\WilayahStatistikController@reminder_warga_unregistered');

    //Kategori bangunan
    Route::post('kategoribangunan/list', 'Api\KategoriBangunanController@list');
    Route::post('kategoribangunan/add', 'Api\KategoriBangunanController@add');
    Route::post('kategoribangunan/update', 'Api\KategoriBangunanController@update');
    Route::post('kategoribangunan/delete', 'Api\KategoriBangunanController@delete');
    Route::post('kategoribangunan/wilayah/list', 'Api\KategoriBangunanController@list_by_wilayah');


    //Anggota Keluarga
    Route::post('anggota_keluarga/list', 'Api\AnggotaKeluargaController@list');
    Route::post('anggota_keluarga/add', 'Api\AnggotaKeluargaController@add');
    Route::post('anggota_keluarga/update', 'Api\AnggotaKeluargaController@update');
    Route::post('anggota_keluarga/delete', 'Api\AnggotaKeluargaController@delete');


    // Warga
    Route::post('warga/list', 'Api\WargaController@list');
    Route::post('warga/list_registered', 'Api\WargaController@list_registered');
    Route::post('warga/list_unregistered', 'Api\WargaController@list_unregistered');
    //Route::get('warga/{wil_id}/list_crm', 'Api\WargaController@list_crm');
    Route::get('warga/detail/{warga_id}', 'Api\WargaController@detail');
    //Route::get('warga/detailcrm/{warga_id}', 'Api\WargaController@detailcrm');
    Route::post('warga/detail_info', 'Api\WargaController@detail_info');
    //Route::post('warga/update_crm', 'Api\WargaController@update_crm');
    //Route::post('warga/tambah_crm', 'Api\WargaController@tambah_crm');
    Route::post('warga/signup', 'Api\WargaController@signup');
    Route::post('warga/create_mk', 'Api\WargaController@create_mk');
    Route::post('warga/create_pengurus', 'Api\WargaController@create_pengurus');
    Route::post('warga/create_kb', 'Api\WargaController@create_kb');
    Route::post('warga/create_kb_portal', 'Api\WargaController@create_kb_portal');
    Route::post('warga/signin', 'Api\WargaController@signin');
    Route::post('warga/check_email', 'Api\WargaController@check_email');
    Route::post('warga/check_hp', 'Api\WargaController@check_hp');
    Route::post('warga/update_token', 'Api\WargaController@update_token');
    //
    Route::post('warga/delete_wil', 'Api\WargaController@sample_delete_wil');

    //
    Route::post('warga/undang/hp', 'Api\WargaController@undang_hp');
    Route::post('warga/undang/email', 'Api\WargaController@undang_email');
    Route::post('warga/update', 'Api\WargaController@update');
    Route::post('warga/lupapassword', 'Api\WargaController@lupa_password');
    Route::post('warga/update-password', 'Api\WargaController@update_password');
    //
    Route::post('warga/update-admin', 'Api\WargaController@update_admin');
    Route::post('warga/update-foto', 'Api\WargaController@update_foto');
    //
    Route::get('warga/reset/{warga_email}', 'Api\WargaController@reset');
    Route::get('warga/email-verify', 'Api\WargaController@email_veirfy');
    Route::get('warga/hp-verify', 'Api\WargaController@hp_veirfy');
    //
    Route::post('warga/register-warga', 'Api\WargaController@register_warga');
    Route::post('warga/setujui', 'Api\WargaController@setujui');

    // Pengurus
    Route::post('pengurus/list', 'Api\PengurusController@list');
    Route::get('pengurus/{pengurus_id}', 'Api\PengurusController@detail');
    Route::get('pengurus/warga/list/{wil_id}/{mk_id}', 'Api\PengurusController@list_warga');
    Route::post('pengurus/add', 'Api\PengurusController@signup');
    Route::post('pengurus/update', 'Api\PengurusController@update');
    Route::post('pengurus/delete', 'Api\PengurusController@delete');
    Route::post('pengurus/sk', 'Api\PengurusController@sk');

    //Admin
    Route::post('pengurus/active/list', 'Api\PengurusController@list_pengurus_active');
    Route::post('pengurus/admin/list', 'Api\PengurusController@list_admin');
    Route::post('pengurus/admin/add', 'Api\PengurusController@add_admin');
    Route::post('pengurus/admin/delete', 'Api\PengurusController@delete_admin');

    //MK
    Route::post('mk/list', 'Api\MkController@list');
    Route::get('mk/active/{wil_id}', 'Api\MkController@active');
    Route::get('mk/{mk_id}', 'Api\MkController@detail');
    Route::post('mk/add', 'Api\MkController@add');
    Route::post('mk/update', 'Api\MkController@update');
    Route::post('mk/delete', 'Api\MkController@delete');

    //Informasi
    Route::post('info/list', 'Api\InfoController@list');
    Route::post('info/list_limited', 'Api\InfoController@list_limited');
    Route::post('info/list_undangan', 'Api\InfoController@list_undangan');
    Route::get('info/detail/{info_id}', 'Api\InfoController@detail');
    Route::post('info/add', 'Api\InfoController@add');
    Route::post('info/update', 'Api\InfoController@update');
    Route::post('info/delete', 'Api\InfoController@delete');

    //Peraturan
    Route::post('peraturan/list', 'Api\PeraturanController@list');
    Route::post('peraturan/list_limited', 'Api\PeraturanController@list_limited');
    Route::post('peraturan/detail/', 'Api\PeraturanController@detail');
    Route::post('peraturan/add', 'Api\PeraturanController@add');
    Route::post('peraturan/update', 'Api\PeraturanController@update');
    Route::post('peraturan/delete', 'Api\PeraturanController@delete');

    //Komplain
    Route::post('komplain/add', 'Api\KomplainController@addWithImage');
    Route::post('komplain/update', 'Api\KomplainController@updateWithImage');
    Route::post('komplain/update_status', 'Api\KomplainController@update_status');
    Route::post('komplain/update_status_pp', 'Api\KomplainController@update_status_pp');

    Route::post('komplain/delete', 'Api\KomplainController@delete');
    Route::post('komplain/list', 'Api\KomplainController@list');
    Route::post('komplain/list_limited', 'Api\KomplainController@list_limited');
    Route::get('komplain/detail/{komp_id}', 'Api\KomplainController@detail');


    //Komen Komplain
    Route::post('kk/add', 'Api\KkController@add');
    Route::post('kk/update', 'Api\KkController@update');
    Route::post('kk/delete', 'Api\KkController@delete');
    Route::get('kk/list-komplain/{komp_id}', 'Api\KkController@list_komplain');
    Route::get('kk/detail/{kk_id}', 'Api\KkController@detail');

    //Komen Progres
    Route::post('kprogres/add', 'Api\KprogresController@add');
    Route::post('kprogres/update', 'Api\KprogresController@update');
    Route::post('kprogres/list', 'Api\KprogresController@list');
    Route::get('kprogres/detail/{kp_id}', 'Api\KprogresController@detail');

    //Panic
    Route::post('panic/add', 'Api\PanicController@add');
    Route::post('panic/update', 'Api\PanicController@update');
    Route::post('panic/list', 'Api\PanicController@list');
    Route::post('panic/list_limited', 'Api\PanicController@list_limited');
    Route::get('panic/detail/{panic_id}', 'Api\PanicController@detail');

    //Kategori Panic
    Route::post('kategori-panic/list', 'Api\KategoripanicController@list');
    Route::post('kategori-panic/list_button', 'Api\KategoripanicController@list_button');
    Route::post('kategori-panic/add', 'Api\KategoripanicController@add');
    Route::post('kategori-panic/update', 'Api\KategoripanicController@update');
    Route::get('kategori-panic/detail/{kb_id}', 'Api\KategoripanicController@detail');
    Route::post('kategori-panic/delete', 'Api\KategoripanicController@delete');

    //Penerima Panic
    Route::post('penerima-panic/list', 'Api\PenerimapanicController@list');
    Route::post('penerima-panic/list_pengurus_aktif', 'Api\PenerimapanicController@list_pengurus_aktif');
    Route::post('penerima-panic/list_kategori', 'Api\PenerimapanicController@list_kategori');
    Route::post('penerima-panic/list_pengurus_panic', 'Api\PenerimapanicController@list_pengurus_panic');

    Route::post('penerima-panic/saveall', 'Api\PenerimapanicController@saveall');
    Route::post('penerima-panic/add', 'Api\PenerimapanicController@add');
    Route::post('penerima-panic/update', 'Api\PenerimapanicController@update');
    Route::get('penerima-panic/detail/{pp_id}', 'Api\PenerimapanicController@detail');
    Route::post('penerima-panic/delete', 'Api\PenerimapanicController@delete');

    //Phone Book
    Route::post('phonebook/add', 'Api\PhonebookController@add');
    Route::post('phonebook/add_bulk', 'Api\PhonebookController@add_bulk');
    Route::post('phonebook/update', 'Api\PhonebookController@update');
    Route::post('phonebook/list', 'Api\PhonebookController@list');
    Route::post('phonebook/list_limited', 'Api\PhonebookController@list_limited');
    Route::post('phonebook/detail', 'Api\PhonebookController@detail');
    Route::post('phonebook/delete', 'Api\PhonebookController@delete');

    //Tarif
    Route::post('tarif/list', 'Api\TarifController@list');
    Route::post('tarif/update', 'Api\TarifController@update');
    Route::post('tarif/get_nilai', 'Api\TarifController@getNilai');


    //Periode Tagihan
    Route::post('periode_tagihan/add', 'Api\PeriodetagihanController@add');
    Route::post('periode_tagihan/list', 'Api\PeriodetagihanController@list');
    Route::post('periode_tagihan/delete', 'Api\PeriodetagihanController@delete');
    Route::post('periode_tagihan/riwayat_pembayaran', 'Api\PeriodetagihanController@riwayat_pembayaran');

    /**
     * Tagihan Modul Pengurus
     */
    Route::post('invoice/list', 'Api\InvoiceController@list');
    Route::post('invoice/add', 'Api\InvoiceController@add');
    Route::post('invoice/update', 'Api\InvoiceController@update');
    Route::post('invoice/delete', 'Api\InvoiceController@delete');
    Route::post('invoice/cek_status', 'Api\InvoiceController@cek_status');
    Route::get('invoice/detail/{pt_id}', 'Api\InvoiceController@detail');
    Route::post('invoice/kirim_tagihan', 'Api\InvoiceController@kirimTagihan');
    Route::post('invoice/detail', 'Api\InvoiceController@detail');
    Route::post('invoice/list_warga', 'Api\InvoiceController@list_warga');
    Route::post('invoice/search_tunggakan', 'Api\InvoiceController@search_tunggakan');

    //catat pembayaran manual oleh pengurus
    Route::post('invoice/list_pembayaran', 'Api\InvoiceController@list_pembayaran');
    Route::post('invoice/catat_pembayaran_manual', 'Api\InvoiceController@catat_pembayaran_manual');
    Route::post('invoice/catat_pembayaran_manual_bulk', 'Api\InvoiceController@catat_pembayaran_manual_bulk');

    //tunggakan
    Route::post('invoice/list_tunggakan_warga', 'Api\InvoiceController@list_tunggakan_warga');
    Route::post('invoice/list_tunggakan_periode_warga', 'Api\InvoiceController@list_tunggakan_periode_warga');
    Route::post('invoice/list_tunggakan_warga_per_periode', 'Api\InvoiceController@list_tunggakan_warga_per_periode');

    //Konfirmasi Pembayaran Manual
    Route::post('invoice/accept_payment_confirmation', 'Api\InvoiceController@accept_payment_confirmation');
    Route::post('invoice/reject_payment_confirmation', 'Api\InvoiceController@reject_payment_confirmation');

    /**
     * Pengurus & Warga
     */
    //Konfirmasi Pembayaran Manual
    Route::post('invoice/list_bukti_bayar', 'Api\InvoiceController@list_bukti_bayar');
    Route::post('invoice/list_bukti_bayar_limited', 'Api\InvoiceController@list_bukti_bayar_limited');
    Route::post('invoice/list_konfirmasi_periode_validasi', 'Api\InvoiceController@list_konfirmasi_periode_validasi');


    /**
     * Tagihan Modul Warga
     */
    //Tagihan Modul Warga
    Route::post('invoice/list_riwayat_periode_tagihan', 'Api\InvoiceController@list_riwayat_periode_tagihan');
    Route::post('invoice/info_tunggakan', 'Api\InvoiceController@info_tunggakan');
    Route::post('invoice/list_tunggakan_periode', 'Api\InvoiceController@list_tunggakan_periode_warga');
    Route::post('invoice/pembayaran_all_warga', 'Api\InvoiceController@pembayaran_all_warga');
    Route::post('invoice/generate_payment_all_url', 'Api\InvoiceController@generate_payment_all_url');
    Route::post('invoice/kirim_email', 'Api\InvoiceController@kirim_email');

    //Konfirmasi Pembayaran Manual
    Route::post('invoice/list_konfirmasi_pembayaran_warga', 'Api\InvoiceController@list_konfirmasi_pembayaran_warga');
    Route::post('invoice/konfirmasi_pembayaran_warga_per_periode', 'Api\InvoiceController@konfirmasi_pembayaran_warga_per_periode');
    Route::post('invoice/konfirmasi_pembayaran_warga_semua_periode', 'Api\InvoiceController@konfirmasi_pembayaran_warga_semua_periode');

    //Notifikasi
    Route::post('notifikasi/list', 'Api\NotifikasiController@list');
    Route::post('notifikasi/doRead', 'Api\NotifikasiController@doRead');
    Route::post('notifikasi/hasRead', 'Api\NotifikasiController@doRead');
    Route::post('notifikasi/hasNotif', 'Api\NotifikasiController@hasNotif');


    /**
     * Pengurus
     */
    Route::post('paket_langganan/list', 'Api\PaketLanggananController@list');
    Route::post('paket_langganan/generate_payment_url', 'Api\PaketLanggananController@generate_payment_url');
    Route::post('paket_langganan/generate_payment_url_with_voucher', 'Api\PaketLanggananController@generate_payment_url_with_voucher');
    Route::post('paket_langganan/rincian_pembayaran', 'Api\PaketLanggananController@rincian_pembayaran');


    Route::post('paket_langganan/upgrade_list', 'Api\PaketLanggananController@upgrade_list');
    Route::post('paket_langganan/upgrade_langganan_payment_url', 'Api\PaketLanggananController@upgrade_langganan_payment_url');
    Route::post('paket_langganan/upgrade_langganan_payment_url_with_voucher', 'Api\PaketLanggananController@upgrade_langganan_payment_url_with_voucher');

    /**
     * Billing - Pengurus
     */
    Route::post('billing/list', 'Api\BillingController@list');
    Route::post('billing/list_pembayaran', 'Api\BillingController@list_pembayaran');
    Route::post('billing/list_pembayaran_limited', 'Api\BillingController@list_pembayaran_limited');
    Route::post('billing/total_tagihan', 'Api\BillingController@total_tagihan');
    Route::post('billing/generate_payment_url_renewal', 'Api\BillingController@generate_payment_url_renewal');
    Route::post('billing/generate_payment_url_renewal_with_voucher', 'Api\BillingController@generate_payment_url_renewal_with_voucher');
    Route::post('billing/recent_billing', 'Api\BillingController@recent_billing');
    Route::post('billing/kirim_email', 'Api\BillingController@kirim_email_pdf');
    Route::post('billing/kirim_email_pdf', 'Api\BillingController@kirim_email_pdf');
    Route::post('billing/berhenti_berlangganan', 'Api\BillingController@berhenti_berlangganan');
    Route::post('billing/rincian_pembayaran', 'Api\BillingController@rincian_pembayaran');


    //Tagihan
    /*Route::post('tagihan/add', 'Api\TagihanController@add_tagihan');
    Route::post('tagihan/update', 'Api\TagihanController@update_tagihan');
    Route::post('tagihan/update/periode', 'Api\TagihanController@update_tagihan_periode');
    Route::post('tagihan/list', 'Api\TagihanController@list');
    Route::post('tagihan/list/count', 'Api\TagihanController@list_count');
    Route::get('tagihan/detail/{tag_id}', 'Api\TagihanController@detail');
    Route::get('tagihan/detail-jt/{tag_id}', 'Api\TagihanController@detail_jt');
    Route::get('tagihan/detail-jti/{tag_id}', 'Api\TagihanController@detail_jti');*/

    //jenis tagihan
    /*Route::post('tagihan/jenis/add', 'Api\TagihanController@add_jenis');
    Route::post('tagihan/jenis/update', 'Api\TagihanController@update_jenis');
    Route::get('tagihan/jenis/list/{wil_id}', 'Api\TagihanController@list_jenis');*/

    //jenis insidental
    /*Route::post('tagihan/jenis_insidental/add', 'Api\TagihanController@add_jenis_insidental');
    Route::post('tagihan/jenis_insidental/update', 'Api\TagihanController@update_jenis_insidental');
    Route::get('tagihan/jenis_insidental/list/{wil_id}', 'Api\TagihanController@list_jenis_insidental');*/

    //detil tagihan
    /*Route::post('tagihan/detil/jenis/update', 'Api\TagihanController@update_detil_jenis');
    Route::post('tagihan/detil/jenis_insidental/update', 'Api\TagihanController@update_detil_jenis_insidental');*/

    //Keuangan Masuk
    Route::post('keuangan/add_masuk_keluar', 'Api\KeuanganController@add_masuk_keluar');
    Route::post('keuangan/edit_masuk_keluar', 'Api\KeuanganController@edit_masuk_keluar');
    Route::post('keuangan/detail', 'Api\KeuanganController@detail');
    Route::post('keuangan/delete', 'Api\KeuanganController@delete');
    Route::post('keuangan/list_daily', 'Api\KeuanganController@list_daily');
    Route::post('keuangan/list_monthly', 'Api\KeuanganController@list_monthly');
    Route::post('keuangan/list_yearly', 'Api\KeuanganController@list_yearly');
    Route::post('keuangan/range_report', 'Api\KeuanganController@range_report');
    Route::post('keuangan/search_list', 'Api\KeuanganController@search_list');


    Route::post('keuangan/add', 'Api\KeuanganController@add');
    Route::post('keuangan/update', 'Api\KeuanganController@update');
    Route::post('keuangan/list', 'Api\KeuanganController@list');
    Route::post('keuangan/total', 'Api\KeuanganController@total');
    Route::post('keuangan/laporan/list', 'Api\KeuanganController@list_lk');

    //Percakapan
    Route::post('percakapan/add', 'Api\PercakapanController@add');
    Route::post('percakapan/send', 'Api\PercakapanController@send');
    Route::post('percakapan/update', 'Api\PercakapanController@update');
    Route::post('percakapan/list', 'Api\PercakapanController@list');
    Route::post('percakapan/delete', 'Api\PercakapanController@delete');
    Route::post('percakapan/pesan/list', 'Api\PercakapanController@list_pesan');


    //E-Commerce
    Route::post('jenis_usaha/list', 'Api\JenisUsahaController@list');

    Route::post('jadwal_buka/list', 'Api\JadwalBukaController@list');
    Route::post('jadwal_buka/edit', 'Api\JadwalBukaController@edit');


    Route::post('usaha/list', 'Api\UsahaController@list');
    Route::post('usaha/list_limited', 'Api\UsahaController@list_limited');
    Route::post('usaha/add', 'Api\UsahaController@add');
    Route::post('usaha/edit', 'Api\UsahaController@edit');
    Route::post('usaha/detail', 'Api\UsahaController@detail');
    Route::post('usaha/profil', 'Api\UsahaController@profil');

    Route::post('produk/list', 'Api\ProdukController@list');
    Route::post('produk/satuan', 'Api\ProdukController@satuan');
    Route::post('produk/detail', 'Api\ProdukController@detail');
    Route::post('produk/add', 'Api\ProdukController@add');
    Route::post('produk/edit', 'Api\ProdukController@edit');
    Route::post('produk/delete', 'Api\ProdukController@delete');

    //Approval
    Route::post('approval_warga/list', 'Api\ApprovalWargaController@list');
    Route::post('approval_warga/approve', 'Api\ApprovalWargaController@approve');
    Route::post('approval_warga/approve_all', 'Api\ApprovalWargaController@approve_all');
    Route::post('approval_warga/reject', 'Api\ApprovalWargaController@reject');

    //Voucher Wil
    Route::post('voucher_wil/list', 'Api\VoucherWilController@list');

    //Request Paket
    Route::post('request_paket/has_processing_request', 'Api\RequestPaketController@has_processing_request');
    Route::post('request_paket/add', 'Api\RequestPaketController@add');
});

Route::post('midtrans/callback', 'Api\MidtransController@callback');
Route::post('backupdownload/export', 'Api\BackupDownloadController@export');
Route::get('midtrans/test', 'Api\MidtransController@test');


//buat CRM menghindari override authorization di Safari
Route::group(['middleware' => 'ValidateKeyCRM'], function () {

    Route::post('usercrm/login', 'Api\UserCrmController@login');
    Route::post('usercrm/logincrm', 'Api\UserCrmController@logincrm');
    Route::get('userMan/index', 'Api\UserManController@index');
    Route::post('userMan/add', 'Api\UserManController@add');
    Route::post('userMan/update', 'Api\UserManController@update');
    Route::post('userMan/delete', 'Api\UserManController@delete');
    Route::get('userMan/detail/{id}', 'Api\UserManController@detail');

    Route::get('wilayah/listcrm', 'Api\WilayahController@list');
    Route::get('wilayah/stat_wilayah', 'Api\WilayahController@stat_wilayah');
    Route::get('wilayah/stat_status', 'Api\WilayahController@stat_status');
    Route::get('wilayah/stat_pwilayah', 'Api\WilayahController@stat_pwilayah');
    Route::get('wilayah/dash_wilayah', 'Api\WilayahController@dash_wilayah');
    Route::get('wilayah/detailcrm/{wil_id}', 'Api\WilayahController@detailcrm');
    Route::post('wilayah/update_status', 'Api\WilayahController@update_status');
    Route::post('wilayah/update_admin', 'Api\WilayahController@update_admin');
    Route::get('wilayah/detailadmin/{wil_id}', 'Api\WilayahController@detailadmin');
    Route::post('wilayah/update_crm', 'Api\WilayahController@update_crm');
    Route::post('wilayah/delete', 'Api\WilayahController@delete_crm');
    Route::post('wilayah/reset_billing', 'Api\WilayahController@reset_billing');

    Route::get('pengurus/detailcrm/{pengurus_id}', 'Api\PengurusController@detailcrm');
    Route::post('pengurus/list_warga', 'Api\WargaController@list');

    // prov > kab >kec >kel
    Route::get('prov_crm', 'Api\CountryController@prov_list');
    Route::get('kab_crm/{prop_id}', 'Api\CountryController@kab_list');
    Route::get('kec_crm/{kab_id}', 'Api\CountryController@kec_list');
    Route::get('kel_crm/{kec_id}', 'Api\CountryController@kel_list');

    //tarik dari kelurahan > kec > kab > prov
    Route::get('kel_crm/kel_nama/{kel_id}', 'Api\CountryController@kel_nama');
    Route::get('prov_kab_crm/{kab_id}', 'Api\CountryController@prov_kab');
    Route::get('kab_kec_crm/{kec_id}', 'Api\CountryController@kab_kec');
    Route::get('kec_kel_crm/{kel_id}', 'Api\CountryController@kec_kel');

    //warga
    Route::get('warga/{wil_id}/list_crm', 'Api\WargaController@list_crm');
    Route::get('warga/dash_user', 'Api\WargaController@dash_user');
    Route::get('warga/detailcrm/{warga_id}', 'Api\WargaController@detailcrm');
    Route::post('warga/update_crm', 'Api\WargaController@update_crm');
    Route::post('warga/tambah_crm', 'Api\WargaController@tambah_crm');
    Route::post('warga/signup_crm', 'Api\WargaController@signup');

    //pengurus /admin
    Route::get('wilayah/{wil_id}/list_pengurus', 'Api\WilayahController@list_pengurus');
    Route::get('wilayah/{pengurus_id}/detail_pengurus', 'Api\WilayahController@detail_pengurus');
    Route::post('wilayah/tambah_pengurus', 'Api\WilayahController@tambah_pengurus');
    Route::post('wilayah/update_pengurus', 'Api\WilayahController@update_pengurus');

    //req_demo
    Route::post('req_demo/signup', 'Api\ReqDemoController@signup');

    //data berlangganan wilayah
    Route::get('billing/{wil_id}/list_crm', 'Api\BillingController@list_crm');
    Route::get('billing/{wil_id}/riwayat_crm', 'Api\BillingController@riwayat_crm');
    Route::get('billing/{bil_id}/detail_inv', 'Api\BillingController@detail_inv');
    Route::post('billing/add', 'Api\BillingController@add');
    Route::post('billing/bayar', 'Api\BillingController@bayar');
    Route::post('billing/kirim_pdf', 'Api\BillingController@kirim_email_pdf');
    Route::post('paket_langganan/list_crm', 'Api\PaketLanggananController@list_crm');

    //sales
    Route::get('sales/list', 'Api\SalesController@list');
    Route::get('sales/detail/{sales_id}', 'Api\SalesController@detail');
    Route::get('sales/team', 'Api\SalesController@team');
    Route::get('sales/leadlist', 'Api\SalesController@leadlist');
    Route::post('sales/add', 'Api\SalesController@add');
    Route::post('sales/update', 'Api\SalesController@update');
    Route::post('sales/delete', 'Api\SalesController@delete');
    //marketing(penjualan)
    Route::get('marketing/list', 'Api\MarketingController@list');
    Route::get('marketing/detail/{mar_id}', 'Api\MarketingController@detail');
    Route::get('marketing/wil_list', 'Api\MarketingController@wilayah_list');
    Route::get('marketing/sales_list', 'Api\MarketingController@sales_list');
    Route::post('marketing/add', 'Api\MarketingController@add');
    Route::post('marketing/update', 'Api\MarketingController@update');
    Route::post('marketing/delete', 'Api\MarketingController@delete');
    //retensiTrial
    Route::get('retensiTrial/detail', 'Api\RetensiTrialController@detail');
    Route::post('retensiTrial/updateTrial', 'Api\RetensiTrialController@updateTrial');
    Route::post('retensiTrial/updateRetensi', 'Api\RetensiTrialController@updateRetensi');

    //jenis usaha crm
    Route::get('jenis_usaha/listcrm', 'Api\JenisUsahaController@listcrm');
    Route::get('jenis_usaha/detail/{id}', 'Api\JenisUsahaController@detail');
    Route::post('jenis_usaha/add', 'Api\JenisUsahaController@add');
    Route::post('jenis_usaha/update', 'Api\JenisUsahaController@update');
    Route::post('jenis_usaha/delete', 'Api\JenisUsahaController@delete');

    //rekap pembayaran berlangganan
    Route::get('rekapPembayaran', 'Api\RekapPembayaranController@list');
    Route::get('rekapPembayaran/total', 'Api\RekapPembayaranController@get_total');

    //penarikan dana
    Route::get('penarikanDana', 'Api\PenarikanDanaController@list');
    Route::get('penarikanDana/total_tarik', 'Api\PenarikanDanaController@get_total');
    Route::get('penarikanDana/total_rekap', 'Api\PenarikanDanaController@get_total_rekap');
    Route::get('penarikanDana/detail/{id}', 'Api\PenarikanDanaController@detail');
    Route::post('penarikanDana/add', 'Api\PenarikanDanaController@add');
    Route::post('penarikanDana/update', 'Api\PenarikanDanaController@update');
    Route::post('penarikanDana/delete', 'Api\PenarikanDanaController@delete');

    //paket langganan
    Route::get('paket_langganan/daftar_crm', 'Api\PaketLanggananController@daftar_paket');
    Route::get('paket_langganan/detail/{id}', 'Api\PaketLanggananController@detail');
    Route::post('paket_langganan/add', 'Api\PaketLanggananController@add');
    Route::post('paket_langganan/update', 'Api\PaketLanggananController@update');
    Route::post('paket_langganan/delete', 'Api\PaketLanggananController@delete');

    //req paket
    Route::post('request_paket/add_crm', 'Api\RequestPaketController@add_crm');
    Route::get('request_paket/daftar_crm', 'Api\RequestPaketController@daftar_crm');
    Route::get('request_paket/daftar_crm_mob', 'Api\RequestPaketController@daftar_crm_mob');
    Route::get('request_paket/detail/{id}', 'Api\RequestPaketController@detail');
    Route::get('request_paket/detail_mob/{id}', 'Api\RequestPaketController@detail_mob');
    Route::post('request_paket/update', 'Api\RequestPaketController@update');
    Route::post('request_paket/delete', 'Api\RequestPaketController@delete');
    Route::post('request_paket/add_bill', 'Api\RequestPaketController@add_bill');
    Route::post('request_paket/kirim_email', 'Api\RequestPaketController@kirim_email');
    Route::post('request_paket/kirim_email_mob', 'Api\RequestPaketController@kirim_email_mob');
    Route::get('request_paket/download_pdf/{id}', 'Api\RequestPaketController@download_pdf');
    Route::get('request_paket/download_pdf_mob/{id}', 'Api\RequestPaketController@download_pdf_mob');

    //hubungi kami
    Route::post('hubungi/post', 'Api\HubungiController@post');
    Route::get('hubungi/daftar_crm', 'Api\HubungiController@daftar_crm');
    Route::get('hubungi/detail/{id}', 'Api\HubungiController@detail');
    Route::post('hubungi/update', 'Api\HubungiController@update');
    Route::post('hubungi/delete', 'Api\HubungiController@delete');
});