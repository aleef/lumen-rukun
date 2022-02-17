<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Keuangan;
use App\Komplain;
use App\PaketLangganan;
use App\Warga;
use App\Wilayah;
use App\Kel;
use App\Kec;
use App\Kab;
use App\SendPhoneMessage;
use App\WargaTemp;
use App\WargaUndang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;
use Bitly;
use Mail;

class WilayahStatistikController extends Controller
{

    public function reminder_warga_unregistered(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $idPengurus = $request->warga_id;

        if(empty($idPengurus)) {
            $response['message'] = 'ID Pengurus tidak boleh kosong';
            return response()->json($response);
        }

        $wilayah = Wilayah::find($wil_id);
        $pengurus = Warga::find($idPengurus);
        $namaPengurus = $pengurus->warga_nama_depan." ".$pengurus->warga_nama_belakang;

        $listWargaUndang = WargaUndang::where('wil_id',$wil_id)
                                        ->where('status','0')
                                        ->get();


        foreach($listWargaUndang as $wargaUndang) {
            $undang_id = $wargaUndang->undang_id;

            $appUrl = 'https://play.google.com/store/apps/details?id=com.rukun.app';
            $urlAplikasi = $appUrl;

            if(!empty($wargaUndang->undang_hp) && $wargaUndang->undang_hp != '-') {
                //send via hp

                $mainUrl = url('').'/warga/register-h/'.encrypt($undang_id).'/'.encrypt($wil_id);
                $urlRegistrasi = Bitly::getUrl($mainUrl);

                $no_hp = $wargaUndang->undang_hp;
                $message = "Halo Warga ". $wilayah->wil_nama.",";
                $message .= "\n\nAnda telah diundang oleh *".$namaPengurus."* untuk bergabung pada Aplikasi Rukun.";
                $message .= "\n\nSilahkan klik tautan Registrasi berikut untuk melakukan registrasi Aplikasi, kemudian install Aplikasi Rukun pada perangkat telepon pintar Anda.";
                $message .= "\n*_Undangan ini hanya berlaku bagi Anda dan tidak berlaku bagi orang lain._*";
                $message .= "\n\n".$urlRegistrasi;
                $message .= "\n\nAplikasi Rukun dapat Anda install melalui tautan berikut : ";
                $message .= "\n\n".$urlAplikasi;
                $message .= "\n\n_Pesan ini dikirim melalui akun Wilayah ".$wilayah->wil_nama." pada Aplikasi Rukun_";

                SendPhoneMessage::whatsAppMessaging($no_hp, $message);

            }else if(!empty($wargaUndang->undang_email)) {
                //send via email
                $mainUrl = url('').'/warga/register-m/'.encrypt($undang_id).'/'.encrypt($wil_id);

                $to_name = "Warga ".$wilayah->wil_nama;
                $to_email = $wargaUndang->undang_email;

                $data = array(
                    'nama_wilayah' => $wilayah->wil_nama,
                    'nama_pengurus' => $namaPengurus,
                    'url_registrasi' => $mainUrl,
                    'url_aplikasi' => $urlAplikasi,
                );

                Mail::send('emails.mail-new', $data, function($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)
                            ->subject('Undangan bergabung dengan Aplikasi Rukun');
                    $message->from('rukun.id.99@gmail.com','Rukun');
                });

            }else {
                //do nothing
            }
        }

        // response
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);

    }

    public function detail(Request $request) {

        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        $data = array();

        $dataWilayah = Wilayah::find($wil_id);
        $data = json_decode(json_encode($dataWilayah), true);

        $kelurahan = Kel::find($dataWilayah->kel_id);
        $kecamatan = Kec::find($kelurahan->kec_id);
        $kabkota = Kab::find($kecamatan->kabkota_id);

        $data['kabkota_nama'] = $kabkota->kabkota_nama;
        $data['kec_nama'] = $kecamatan->kec_nama;
        $data['kel_nama'] = $kelurahan->kel_nama;

        if(!empty($dataWilayah->wil_foto)) {
            $data['wil_foto'] = URL('public/img/wilayah/'.$dataWilayah->wil_foto);
        }else {
            $data['wil_foto'] = URL('public/img/wilayah/default.jpg');
        }

        if(!empty($dataWilayah->wil_logo)) {
            $data['wil_logo'] = URL('public/img/logo_wilayah/'.$dataWilayah->wil_logo);
        }else {
            $data['wil_logo'] = URL('public/img/logo_wilayah/default.png');
        }

        //cek trial
        $isTrial = $dataWilayah->wil_status == '1'
                    || $dataWilayah->wil_status == '2'
                    || $dataWilayah->wil_status == '3';

        $data['join_date'] = Carbon::parse($dataWilayah->wil_mulai_trial)->isoFormat('D MMMM Y');

        if($isTrial) {
            //jika masa trial
            $data['pl_nama'] = 'Trial';
            $data['masa_aktif'] = Carbon::parse($dataWilayah->wil_retensi_trial)->subDays(1)->isoFormat('D MMMM Y');
        }else {
            if(!empty($dataWilayah->pl_id)) {
                $paketLangganan = PaketLangganan::find($dataWilayah->pl_id);
                $data['pl_nama'] = $paketLangganan->pl_nama;
            }
            else {
                $data['pl_nama'] = 'Unknown';
            }
            $data['masa_aktif'] = Carbon::parse($dataWilayah->wil_expire)->isoFormat('D MMMM Y');
        }

        //Statistik Keuangan
        $data['stat_uang_masuk'] = Keuangan::where('keu_status', '1')
                                    ->where('wil_id',$wil_id)
                                    ->sum('keu_nominal');
        $data['stat_uang_keluar'] = Keuangan::where('keu_status', '0')
                                    ->where('wil_id',$wil_id)
                                    ->sum('keu_nominal');

        $data['stat_saldo'] = (double) $data['stat_uang_masuk'] - (double)$data['stat_uang_keluar'];


        //Statistik Warga
        $data['stat_warga_terdaftar'] = Warga::where('wil_id', $wil_id)->count();
        $data['stat_warga_belum_registrasi'] = WargaUndang::where('wil_id', $wil_id)
                                                ->where('status','0')
                                                ->count();

        $data['stat_warga_belum_approval'] = WargaTemp::where('wil_id', $wil_id)
                                                ->count();

        //Statistik Pengaduan
        $data['stat_total_pengaduan'] = Komplain::where('wil_id', $wil_id)->count();
        $data['stat_pengaduan_selesai'] = Komplain::where('wil_id',$wil_id)
                                           ->where('komp_status','2')
                                           ->count();
        $data['stat_pengaduan_onprogress'] = Komplain::where('wil_id',$wil_id)
                                            ->where('komp_status','0')
                                            ->count();

        $statusWilayah = ['Masa Trial','Masa Retensi Trial','Berhenti Trial','Berlangganan','Masa Retensi Berlangganan','Berhenti Berlangganan'];
        $data['status_wilayah'] = $statusWilayah[intval($dataWilayah->wil_status)-1];

        $data['wil_kode'] = empty($dataWilayah->wil_kode) ? "-" : $dataWilayah->wil_kode;
        $data['url_android_app'] = 'https://play.google.com/store/apps/details?id=com.rukun.app';

        $data['wil_rek_no'] = empty($dataWilayah->wil_rek_no) ? '-' : $dataWilayah->wil_rek_no;
        $data['wil_rek_bank_tujuan'] = empty($dataWilayah->wil_rek_bank_tujuan) ? '-' : $dataWilayah->wil_rek_bank_tujuan;
        $data['wil_rek_atas_nama'] = empty($dataWilayah->wil_rek_atas_nama) ? '-' : $dataWilayah->wil_rek_atas_nama;

        // response
		$response['status'] = "success";
		$response['message'] = "OK";
		$response['results'] = $data;

		// return json response
		return response()->json($response);
    }
}
