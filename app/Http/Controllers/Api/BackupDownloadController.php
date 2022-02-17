<?php
namespace App\Http\Controllers\Api;

use App\Exports\BackupDataWilayahExport;
use App\Exports\InvoiceExport;
use App\Exports\KeuanganExport;
use App\Exports\PhonebookExport;
use App\Exports\WargaExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;
use Maatwebsite\Excel\Facades\Excel;

class BackupDownloadController extends Controller
{

    public function export(Request $request) {
        $response = array('status' => 'failed', 'message' => 'request failed', 'results' => array());

        $wil_id = $request->wil_id;

        $keuangan = $request->keuangan;
        $tagihan = $request->tagihan;
        $warga = $request->warga;
        $telepon = $request->telepon;


        if(empty($wil_id)) {
            $response['message'] = 'ID Wilayah harus diisi';
            return response()->json($response);
        }

        $emptySheets = (empty($keuangan) && empty($tagihan) && empty($warga) && empty($telepon));
        if($emptySheets) {
            $response['message'] = 'Tidak ada data yang dipilih';
            return response()->json($response);
        }

        $sheets = array();
        if(!empty($keuangan))
            $sheets[] = new KeuanganExport($wil_id);
        if(!empty($tagihan))
            $sheets[] = new InvoiceExport($wil_id);
        if(!empty($warga))
            $sheets[] = new WargaExport($wil_id);
        if(!empty($telepon))
            $sheets[] = new PhonebookExport($wil_id);

        $fileName = 'Backup_Wilayah_'.$wil_id.'.xlsx';
        $filePath = 'download_excel/'.$fileName;

        Excel::store(new BackupDataWilayahExport($sheets), $filePath,'public');

        // response
        $response['fileURL'] = URL('public/storage/'.$filePath);
        $response['fileName'] = $fileName;
		$response['status'] = "success";
		$response['message'] = "OK";

		// return json response
		return response()->json($response);

    }

}
