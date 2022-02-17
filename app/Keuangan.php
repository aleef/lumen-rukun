<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Keuangan extends Model
{
    protected $table = 'keuangan';
    protected $primaryKey = 'keu_id';
    public $timestamps = false;

    protected $fillable = [
        'tag_id',
        'keu_tgl',
        'keu_status',
        'keu_sumbertujuan',
        'keu_deskripsi',
        'keu_nominal',
        'wil_id',
        'keu_tgl_short',
        'created_at'
    ];

    /* Get List */
     public function get_list($keyword='', $wil_id='', $status='')
     {
         $rs = DB::table("$this->table as a")
            ->select('a.keu_tgl_short')
            ->groupBy('a.keu_tgl_short');

         if($wil_id!='')
            $rs = $rs->where('a.wil_id',$wil_id);

         if($keyword!=''){
            $rs = $rs->where(function($q) use ($keyword) {
                    $q->where('a.keu_deskripsi','ilike',"%$keyword%")
                    ->orWhere('a.keu_sumbertujuan','ilike',"%$keyword%");
            });
        }

         if($status!='')
            $rs = $rs->where('a.keu_status',$status);

         $rs = $rs->get();

         if(!empty($rs)){

            $result = array();
            $i=0;
            foreach($rs as $row){

                //list by date
                $datelist = DB::table("$this->table as a")
                ->select('a.*')
                ->where('a.keu_tgl_short', "'".$row->keu_tgl_short."'")
                ->where('a.wil_id',$wil_id)
                ->orderBy('a.keu_sumbertujuan')
                ->get();
                if(empty($datelist)){
                    $date_list="";
                }
                else {
                    $result_ = array();
                    $in=0;
                    foreach($datelist as $row){
                        $result_[$in]['keu_status'] = $row->keu_status;
                        $result_[$in]['keu_sumbertujuan'] = $row->keu_sumbertujuan;
                        $result_[$in]['keu_deskripsi'] = $row->keu_deskripsi;
                        $result_[$in]['keu_nominal'] = 'Rp '.number_format($row->keu_nominal, 0);
                        $in++;
                    }
                    $date_list = $result_;
                }

                //total pemasukan
                $totalpemasukan = DB::table("$this->table as a")
                ->select(DB::raw('SUM(a.keu_nominal) as total'))
                ->where('a.keu_tgl_short', "'".$row->keu_tgl_short."'")
                ->where('a.keu_status', 1)
                ->where('a.wil_id',$wil_id)
                ->first();
                if(empty($totalpemasukan->total))
                    $totalpemasukan=0;
                else
                    $totalpemasukan = $totalpemasukan->total;

                //total pengeluaran
                $totalpengeluaran = DB::table("$this->table as a")
                ->select(DB::raw('SUM(a.keu_nominal) as total'))
                ->where('a.keu_tgl_short', "'".$row->keu_tgl_short."'")
                ->where('a.keu_status', 0)
                ->where('a.wil_id',$wil_id)
                ->first();
                if(empty($totalpengeluaran->total))
                    $totalpengeluaran=0;
                else
                    $totalpengeluaran = $totalpengeluaran->total;

                $dateCreate = \Carbon\Carbon::parse($row->keu_tgl_short)->format('d M Y');

                //convert day
                $day = \Carbon\Carbon::parse($row->keu_tgl_short)->format('l');
                switch ($day) {
                    case 'Monday':
                        $dayIna = 'Senin';
                        break;

                    case 'Tuesday':
                        $dayIna = 'Selasa';
                        break;

                    case 'Wednesday':
                        $dayIna = 'Rabu';
                        break;

                    case 'Thursday':
                        $dayIna = 'Kamis';
                        break;

                    case 'Friday':
                        $dayIna = 'Jumat';
                        break;

                    case 'Saturday':
                        $dayIna = 'Sabtu';
                        break;

                    case 'Sunday':
                        $dayIna = 'Minggu';
                        break;
                }

                $result[$i]['keu_tgl'] = $dayIna.", ".$dateCreate;
                $result[$i]['total_pemasukan'] = 'Rp '.number_format($totalpemasukan, 0);
                $result[$i]['total_pengeluaran'] = 'Rp '.number_format($totalpengeluaran, 0);
                $result[$i]['date_list'] = $date_list;
                $i++;
            }

            return $result;
         }
         else
             return "";
     }

     /* Get Total */
     public function get_total($wil_id='', $warga_id='')
     {
         $total_pemasukan = DB::table("$this->table as a")
            ->select(DB::raw('SUM(a.keu_nominal) as total'))
            ->where('a.keu_status','1')
            ->where('a.wil_id',$wil_id)
            ->first();

         $total_pengeluaran = DB::table("$this->table as a")
            ->select(DB::raw('SUM(a.keu_nominal) as total'))
            ->where('a.keu_status','0')
            ->where('a.wil_id',$wil_id)
            ->first();

         $__pemasukan = intval($total_pemasukan->total);
         $__pengeluaran = intval($total_pengeluaran->total);

         $total_ = $__pemasukan - $__pengeluaran;

         if(!empty($total_)) {
            $result['saldo'] = 'Rp '.number_format($total_, 0);
            $result['total_pemasukan'] = 'Rp '.number_format($__pemasukan, 0);
            $result['total_pengeluaran'] = 'Rp '.number_format($__pengeluaran, 0);
            return $result;
         }
         else
            return "";
     }

     /* Get List */
     public function get_list_lk($wil_id='', $dari='', $sampai='')
     {


            $total_pemasukan = DB::table("$this->table as a")
                ->select(DB::raw('SUM(a.keu_nominal) as total'))
                ->where('a.keu_status','1')
                ->where('a.wil_id',$wil_id)
                ->whereBetween('a.keu_tgl_short', ["'".$dari."'", "'".$sampai."'"])
                ->first();

            $pemasukan = DB::table("$this->table as a")
                ->select('a.keu_sumbertujuan','a.keu_nominal')
                ->where('a.keu_status','1')
                ->where('a.wil_id',$wil_id)
                ->whereBetween('a.keu_tgl_short', ["'".$dari."'", "'".$sampai."'"])
                ->get();

                if(empty($pemasukan)){
                    $pemasukan_list="";
                }
                else {
                    $result_ = array();
                    $in=0;
                    foreach($pemasukan as $row){
                        $result_[$in]['keu_sumbertujuan'] = $row->keu_sumbertujuan;
                        $result_[$in]['keu_nominal'] = 'Rp '.number_format($row->keu_nominal, 0);
                        $in++;
                    }
                    $pemasukan_list = $result_;
                }

            $total_pengeluaran = DB::table("$this->table as a")
                ->select(DB::raw('SUM(a.keu_nominal) as total'))
                ->where('a.keu_status','0')
                ->where('a.wil_id',$wil_id)
                ->whereBetween('a.keu_tgl_short', ["'".$dari."'", "'".$sampai."'"])
                ->first();

            $pengeluaran = DB::table("$this->table as a")
                ->select('a.keu_sumbertujuan','a.keu_nominal')
                ->where('a.keu_status','0')
                ->where('a.wil_id',$wil_id)
                ->whereBetween('a.keu_tgl_short', ["'".$dari."'", "'".$sampai."'"])
                ->get();

                if(empty($pengeluaran)){
                    $pengeluaran_list="";
                }
                else {
                    $result_ = array();
                    $in=0;
                    foreach($pengeluaran as $row){
                        $result_[$in]['keu_sumbertujuan'] = $row->keu_sumbertujuan;
                        $result_[$in]['keu_nominal'] = 'Rp '.number_format($row->keu_nominal, 0);
                        $in++;
                    }
                    $pengeluaran_list = $result_;
                }

            $__pemasukan = intval($total_pemasukan->total);
            $__pengeluaran = intval($total_pengeluaran->total);

            $total_ = $__pemasukan - $__pengeluaran;

        if(!empty($total_)) {
            $result['saldo'] = 'Rp '.number_format($total_, 0);
            $result['total_pemasukan'] = 'Rp '.number_format($total_pemasukan->total, 0);;
            $result['list_pemasukan'] = $pemasukan_list;
            $result['total_pengeluaran'] = 'Rp '.number_format($total_pengeluaran->total, 0);;
            $result['list_pengeluaran'] = $pengeluaran_list;
            return $result;
         }
         else
             return "";
     }

}
