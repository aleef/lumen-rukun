<?php
namespace App\Http\Controllers\Api;

use App\AnggotaKeluarga;
use App\Http\Controllers\Controller;
use App\Warga;
use App\Wilayah;
use Illuminate\Http\Request;
use Response;

class AnggotaKeluargaController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        if(empty($warga_id)) {
            $response['message'] = "Warga ID tidak boleh kosong";
        }

        $list = AnggotaKeluarga::where('warga_id',$warga_id)
                ->orderBy('ak_id','asc')
                ->get();
        $data = array();

        $arrHubungan = ['01' => 'Kepala Keluarga',
                        '02' => 'Suami',
                        '03' => 'Istri',
                        '04' => 'Anak',
                        '05' => 'Menantu',
                        '06' => 'Cucu',
                        '07' => 'Orang Tua',
                        '08' => 'Mertua',
                        '09' => 'Famili Lain',
                        '10' => 'Pembantu',
                        '11' => 'Lainnya'];

        $jenisKelamin = ['L' => 'Laki-laki', 'P' => 'Perempuan'];

        $i = 0;
        foreach($list as $item) {
            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['jenis_kelamin'] = $jenisKelamin[$item->ak_jk];
            $data[$i]['hubungan_keluarga'] = $arrHubungan[$item->ak_hubungan];

            $i++;
        }

		$response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }

    public function add(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $warga_id = $request->warga_id;
        $ak_nama = $request->ak_nama;
        $ak_jk = $request->ak_jk;
        $ak_hubungan = $request->ak_hubungan;

        if(empty($warga_id)) {
            $response['message'] = "Warga ID tidak boleh kosong";
            return response()->json($response);
        }

        $warga = Warga::find($warga_id);
        $wil_id = $warga->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        AnggotaKeluarga::create([
            'warga_id' => $warga_id,
            'ak_nama' => $ak_nama,
            'ak_jk' => $ak_jk,
            'ak_hubungan' => $ak_hubungan
        ]);

        $response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }

    public function update(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $ak_id = $request->ak_id;
        $ak_nama = $request->ak_nama;
        $ak_jk = $request->ak_jk;
        $ak_hubungan = $request->ak_hubungan;

        if(empty($ak_id)) {
            $response['message'] = "Anggota Keluarga ID tidak boleh kosong";
            return response()->json($response);
        }

        $dataAK = AnggotaKeluarga::find($ak_id);

        //cek subscription
        $warga = Warga::find($dataAK->warga_id);
        $wil_id = $warga->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }


        $dataAK->ak_id = $ak_id;
        $dataAK->ak_nama = $ak_nama;
        $dataAK->ak_jk = $ak_jk;
        $dataAK->ak_hubungan = $ak_hubungan;
        $dataAK->save();

        $response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }

    public function delete(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $ak_id = $request->ak_id;

        if(empty($ak_id)) {
            $response['message'] = "Anggota Keluarga ID tidak boleh kosong";
            return response()->json($response);
        }


        $dataAK = AnggotaKeluarga::find($ak_id);

        //cek subsciption
        $warga = Warga::find($dataAK->warga_id);
        $wil_id = $warga->wil_id;
        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $dataAK->delete();


        $response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }

}
