<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;


class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'produk_id';
    public $timestamps = false;

    protected $fillable = [
        'produk_id',
        'usaha_id',
        'produk_nama',
        'produk_harga',
        'produk_satuan',
        'produk_foto',
        'produk_deskripsi',
    ];

    public function getList($usaha_id, $keyword) {
        $rs = DB::table("$this->table as a")
             ->select('a.*');

        if(!empty($usaha_id))
            $rs = $rs->where('a.usaha_id', $usaha_id);

        if(!empty($keyword)) {
            $rs = $rs->where(function($q) use ($keyword) {
                $q->where('a.produk_nama','ilike',"%$keyword%")
                ->orWhere('a.produk_deskripsi','ilike',"%$keyword%");
            });
        }

        $rs = $rs->orderBy('a.produk_nama','asc');

        $rs = $rs->get();

        return $rs;
    }
}
