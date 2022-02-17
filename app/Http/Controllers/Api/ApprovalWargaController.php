<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Kb;
use App\User;
use App\Warga;
use App\WargaTemp;
use App\Wilayah;
use Illuminate\Http\Request;
use Response;
use Mail;

class ApprovalWargaController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        if(empty($wil_id)) {
            $response['message'] = "Wilayah ID tidak boleh kosong";
            return response()->json($response);
        }

        $list = WargaTemp::where('wil_id',$wil_id)
                ->orderBy('wt_id','asc')
                ->get();


        $data = array();
        $i = 0;

        foreach($list as $item) {

            $kb = Kb::find($item->kb_id);

            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['wt_nama'] = $item->wt_nama_depan." ".$item->wt_nama_belakang;
            $data[$i]['kb_keterangan'] = $kb->kb_keterangan;
            $data[$i]['wt_status'] = empty($item->wt_status) ? "-" : $item->wt_status;
            $data[$i]['wt_status_rumah'] = empty($item->wt_status_rumah) ? "-" : $item->wt_status_rumah;
            $data[$i]['wt_tgl_lahir'] = empty($item->wt_tgl_lahir) ? "-" : $item->wt_tgl_lahir;
            $data[$i]['wt_nama_belakang'] = empty($item->wt_nama_belakang) ? " " : $item->wt_nama_belakang;
            $i++;
        }

        $wil_foto = '';
        $wilayah = Wilayah::find($wil_id);
        if($wilayah->wil_foto!='')
            $wil_foto = URL('public/img/wilayah/'.$wilayah->wil_foto);
        else
            $wil_foto = URL('public/img/wilayah/default.jpg');
        $wilayah->wil_foto = $wil_foto;

		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;
        $response['wilayah'] = $wilayah;

		// return json response
		return response()->json($response);
    }


    public function approve(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wt_id = $request->wt_id;
        if(empty($wt_id)) {
            $response['message'] = "ID Warga tidak boleh kosong";
            return response()->json($response);
        }

        try{
            $wt = WargaTemp::find($wt_id);

            //cek subscription
            $wil_id = $wt->wil_id;
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }

            //cek ketersediaan wil_jml_warga
            $wilayah = Wilayah::find($wil_id);
            $max_jml_warga = $wilayah->wil_jml_warga;

            //cek jumlah warga sekarang
            $total_warga = Warga::where('wil_id',$wil_id)->count();

            if(($total_warga + 1) > $max_jml_warga) {
                $response['message'] = 'Batas jumlah warga telah mencapai maksimal '.$max_jml_warga.' orang. Silahkan upgrade paket jika ingin menambah batas jumlah warga. Terima kasih.';
                return response()->json($response);
            }

            //1. find Warga Temp by wt_id
            //2. find User by user_temp_id
            //3. Insert warga_temp to warga
            //4. Update warga_id ke user_ref_id dan active_status jadikan 1
            //5. delete warga_temp

            $user = User::where('user_temp_id',$wt->wt_id)->first();

            $warga = new Warga;
            $warga->warga_nama_depan = $wt->wt_nama_depan;
            $warga->warga_hp = $wt->wt_hp;
            $warga->warga_email = $wt->wt_email;
            $warga->warga_alamat = $wt->wt_alamat;
            $warga->warga_no_rumah = $wt->wt_no_rumah;
            $warga->wil_id = $wt->wil_id;
            $warga->warga_status = $wt->wt_status;
            $warga->warga_status_rumah = $wt->wt_status_rumah;
            $warga->kb_id = $wt->kb_id;
            $warga->warga_nama_belakang = $wt->wt_nama_belakang;
            $warga->warga_tgl_lahir = $wt->wt_tgl_lahir;
            $warga->save();

            $user->user_ref_id = $warga->warga_id;
            $user->active_status = 1;
            $user->save();

            $wilayah = Wilayah::find($wt->wil_id);

            $to_name = $wt->wt_nama_depan;
            $to_email = $wt->wt_email;

            $wt->delete();

            $data = array(
                'nama_wilayah' => $wilayah->wil_nama,
                'is_approve' => true,
            );

            Mail::send('emails.approval-mail', $data, function($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)
                        ->subject('Selamat Bergabung di Rukun');
                $message->from('rukun.id.99@gmail.com','Rukun');
            });

            // response
            $response['status'] = "success";
            $response['message'] = "Approve Berhasil";

            // return json response
            return response()->json($response);
         }
         catch(\Exception $e){
             // response
             $response['status'] = "failed";
             $response['message'] = "Data tidak ditemukan";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
         }

    }


    public function reject(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wt_id = $request->wt_id;
        if(empty($wt_id)) {
            $response['message'] = "ID Warga tidak boleh kosong";
            return response()->json($response);
        }

        try{
            //1. delete user
            //2. delete warga_temp

            $wt = WargaTemp::find($wt_id);
            $wil_id = $wt->wil_id;
            if(!empty(Wilayah::validateSubscription($wil_id))) {
                $message = Wilayah::validateSubscription($wil_id);
                $response['message'] = $message;

                return response()->json($response);
            }


            $user = User::where('user_temp_id',$wt->wt_id)->first();

            $wilayah = Wilayah::find($wt->wil_id);
            $to_name = $wt->wt_nama_depan;
            $to_email = $wt->wt_email;

            $user->delete();
            $wt->delete();

            $data = array(
                'nama_wilayah' => $wilayah->wil_nama,
                'is_approve' => false,
            );

            Mail::send('emails.approval-mail', $data, function($message) use ($to_name, $to_email) {
                $message->to($to_email, $to_name)
                        ->subject('Registrasi Anda ditolak');
                $message->from('rukun.id.99@gmail.com','Rukun');
            });

            // response
            $response['status'] = "success";
            $response['message'] = "Registrasi Ditolak";

            // return json response
            return response()->json($response);
         }
         catch(\Exception $e){
             // response
             $response['status'] = "failed";
             $response['message'] = "Data tidak ditemukan";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
         }

    }


    public function approve_all(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;
        if(empty($wil_id)) {
            $response['message'] = "ID Wilayah tidak boleh kosong";
            return response()->json($response);
        }

        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        try{
            //1. find Warga Temp by wt_id
            //2. find User by user_temp_id
            //3. Insert warga_temp to warga
            //4. Update warga_id ke user_ref_id dan active_status jadikan 1
            //5. delete warga_temp

            $listWargaTemp = WargaTemp::where('wil_id',$wil_id)->get();
            if(count($listWargaTemp) == 0) {
                $response['message'] = 'Tidak ada data untuk diapprove';
                return response()->json($response);
            }

            //cek ketersediaan wil_jml_warga
            $wilayah = Wilayah::find($wil_id);
            $max_jml_warga = $wilayah->wil_jml_warga;

            //cek jumlah warga sekarang
            $total_warga = Warga::where('wil_id',$wil_id)->count();
            $count_temp = WargaTemp::where('wil_id',$wil_id)->count();

            if(($total_warga + $count_temp) > $max_jml_warga) {
                $response['message'] = 'Batas jumlah warga telah mencapai maksimal '.$max_jml_warga.' orang. Silahkan upgrade paket jika ingin menambah batas jumlah warga. Terima kasih.';
                return response()->json($response);
            }

            foreach($listWargaTemp as $wt) {

                $user = User::where('user_temp_id',$wt->wt_id)->first();

                $warga = new Warga;
                $warga->warga_nama_depan = $wt->wt_nama_depan;
                $warga->warga_hp = $wt->wt_hp;
                $warga->warga_email = $wt->wt_email;
                $warga->warga_alamat = $wt->wt_alamat;
                $warga->warga_no_rumah = $wt->wt_no_rumah;
                $warga->wil_id = $wt->wil_id;
                $warga->warga_status = $wt->wt_status;
                $warga->warga_status_rumah = $wt->wt_status_rumah;
                $warga->kb_id = $wt->kb_id;
                $warga->warga_nama_belakang = $wt->wt_nama_belakang;
                $warga->warga_tgl_lahir = $wt->wt_tgl_lahir;
                $warga->save();

                $user->user_ref_id = $warga->warga_id;
                $user->active_status = 1;
                $user->save();

                $wilayah = Wilayah::find($wt->wil_id);

                $to_name = $wt->wt_nama_depan;
                $to_email = $wt->wt_email;

                $wt->delete();

                $data = array(
                    'nama_wilayah' => $wilayah->wil_nama,
                    'is_approve' => true,
                );

                Mail::send('emails.approval-mail', $data, function($message) use ($to_name, $to_email) {
                    $message->to($to_email, $to_name)
                            ->subject('Selamat Bergabung di Rukun');
                    $message->from('rukun.id.99@gmail.com','Rukun');
                });

            }

            // response
            $response['status'] = "success";
            $response['message'] = "Approve Berhasil";

            // return json response
            return response()->json($response);
         }
         catch(\Exception $e){
             // response
             $response['status'] = "failed";
             $response['message'] = "Data tidak ditemukan";
             $response['results'] =  $e->getMessage();
             // return json response
             return response()->json($response);
         }

    }

}
