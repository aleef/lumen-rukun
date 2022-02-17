<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Produk;
use App\Usaha;
use App\Wilayah;
use Illuminate\Http\Request;
use Response;
use Intervention\Image\ImageManagerStatic as Image;
use File;


class ProdukController extends Controller
{

    public function list(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $usaha_id = $request->usaha_id;
        $keyword = $request->keyword;

        $produk = new Produk;
        $list = $produk->getList($usaha_id, $keyword);

        $i = 0;
        $data = array();
        $listSatuan = self::listSatuan();

        foreach($list as $item) {
            $data[$i] = json_decode(json_encode($item), true);
            $data[$i]['foto_url'] =  URL('public/img/ecommerce/produk/'.$item->produk_foto);
            $data[$i]['satuan'] = $listSatuan[$item->produk_satuan];
            $i++;
        }

        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }

    public static function listSatuan() {
        return [
            "" => " - Pilih Satuan -",
            "1" => "Unit",
            "2" => "Bungkus",
            "3" => "Pack",
            "4" => "Dus",
            "5" => "Paket",
            "6" => "Buah",
            "7" => "Kilogram",
            "8" => "Pcs",
            "9" => "Botol",
        ];
    }

    public function satuan(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $listSatuan = self::listSatuan();
        $data = array();
        $i = 0;
        foreach($listSatuan as $id => $item) {
            $data[$i]['id'] = $id;
            $data[$i]['nama'] = $item;
            $i++;
        }

        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $data;

		// return json response
		return response()->json($response);
    }


    public function detail(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $produk_id = $request->produk_id;

        $listSatuan = self::listSatuan();
        $produk = Produk::find($produk_id);
        $produk->foto_url =  URL('public/img/ecommerce/produk/'.$produk->produk_foto);
        $produk->satuan = $listSatuan[$produk->produk_satuan];

        $satuan = array();
        $i = 0;
        foreach($listSatuan as $id => $item) {
            $satuan[$i]['id'] = $id;
            $satuan[$i]['nama'] = $item;
            $i++;
        }
        // response
        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $produk;
        $response['satuan'] = $satuan;

		// return json response
		return response()->json($response);
    }



    public function add(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $usaha_id = $request->usaha_id;

        $usahaItem = Usaha::find($usaha_id);
        $wil_id = $usahaItem->wil_id;

        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        $produk_nama = $request->produk_nama;
        $produk_harga = $request->produk_harga;
        $produk_foto = $request->file('produk_foto');
        $produk_satuan = $request->produk_satuan;
        $produk_deskripsi = $request->produk_deskripsi;

        $produk = new Produk;

        if(!empty($produk_foto)) {
            $destination_path = public_path('img/ecommerce/produk/');
			$img = $produk_foto;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/ecommerce/produk/$md5_name.$ext"));
			$img->fit(500);
			$img->save(public_path("img/ecommerce/produk/$md5_name.$ext"));

			// set data
			$produk->produk_foto = $img_file;
        }else {
            $produk->produk_foto = "default.png";
        }

        $produk->usaha_id = $usaha_id;
        $produk->produk_nama = $produk_nama;
        $produk->produk_harga = $produk_harga;
        $produk->produk_satuan = $produk_satuan;
        $produk->produk_deskripsi = $produk_deskripsi;
        $produk->save();

        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $produk;

		// return json response
		return response()->json($response);
    }

    public function edit(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $produk_id = $request->produk_id;

        $produk_nama = $request->produk_nama;
        $produk_harga = $request->produk_harga;
        $produk_foto = $request->file('produk_foto');
        $produk_satuan = $request->produk_satuan;
        $produk_deskripsi = $request->produk_deskripsi;


        $produk = Produk::find($produk_id);

        $usahaItem = Usaha::find($produk->usaha_id);
        $wil_id = $usahaItem->wil_id;

        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        if(!empty($produk_foto)) {
            $destination_path = public_path('img/ecommerce/produk/');
			$img = $produk_foto;

			// upload
			$md5_name = uniqid()."_".md5_file($img->getRealPath());
			$ext = $img->getClientOriginalExtension();
			$img->move($destination_path,"$md5_name.$ext");
			$img_file = "$md5_name.$ext";

			// resize photo
			$img = Image::make(URL("public/img/ecommerce/produk/$md5_name.$ext"));
			$img->fit(500);
			$img->save(public_path("img/ecommerce/produk/$md5_name.$ext"));

            if($produk->produk_foto != 'default.png')
                File::delete(public_path('img/ecommerce/produk/').$produk->produk_foto);

			// set data
			$produk->produk_foto = $img_file;
        }

        $produk->produk_id = $produk_id;
        $produk->produk_nama = $produk_nama;
        $produk->produk_harga = $produk_harga;
        $produk->produk_satuan = $produk_satuan;
        $produk->produk_deskripsi = $produk_deskripsi;
        $produk->save();

        $response['status'] = "success";
		$response['message'] = "OK";
        $response['results'] = $produk;

		// return json response
		return response()->json($response);
    }

    public function delete(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $produk_id = $request->produk_id;
        $produk = Produk::find($produk_id);

        $usahaItem = Usaha::find($produk->usaha_id);
        $wil_id = $usahaItem->wil_id;

        if(!empty(Wilayah::validateSubscription($wil_id))) {
            $message = Wilayah::validateSubscription($wil_id);
            $response['message'] = $message;

            return response()->json($response);
        }

        if($produk->produk_foto != 'default.png')
            File::delete(public_path('img/ecommerce/produk/').$produk->produk_foto);

        $produk->delete();

        $response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);
    }
}
