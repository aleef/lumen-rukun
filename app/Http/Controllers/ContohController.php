<?php

namespace App\Http\Controllers;

use App\Contoh; //File Model

use Illuminate\Http\Request;

class ContohController extends Controller

{

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function __construct()

    {
    }

    public function index()

    {

        $data = Contoh::all();

        return response($data);
    }

    public function show($id)

    {

        $data = Contoh::where('ju_id', $id)->get();

        return response($data);
    }

    public function store(Request $request)

    {

        //$data = new Contoh();

        //$data->activity = $request->input('ju_nama');
        $author = Contoh::create($request->all());

        //$data->save();

        //return response('Berhasil Tambah Data');

        return response()->json($author,  201);
    }

    public function update(Request $request, $id)

    {

        $data = Contoh::where('ju_id', $id)->first();

        $data->ju_nama = $request->input('ju_nama');

        //$data->description = $request->input('description');

        $data->save();

        return response('Berhasil Merubah Data');
    }

    public function destroy($id)

    {

        $data = Contoh::where('ju_id', $id)->first();

        $data->delete();

        return response('Berhasil Menghapus Data');
    }
}
