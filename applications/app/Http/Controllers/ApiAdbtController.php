<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Api;
use DB;


class ApiAdbtController extends Controller
{
    public function createKey()
    {
      $hash = str_random(35);

      $key = new Api();
      $key->api_key = Hash::make($hash);
      $key->deskripsi = 'Adikbangtaru';
      $key->save();

      $status = ['code' => 201, 'deskirpsi' => 'Sukses'];

      return response()->json(['status' => $status, 'key' => $key], 201);
    }

    public function getPegawai($key){

      $apikey = Api::where('api_key', '=', $key)->first();

      if(!$apikey){
        $status = ['code'=> 400, 'deskirpsi'=>'Invalid api key, tidak ada dalam database kami.'];

        return response()->json(['status' => $status,], 401) ;

      }

      $skpd = Skpd::where('nama', 'like', '%ruang%')->first();
      $pegawai = Pegawai::where('skpd_id', '=', $skpd->id)->where('status', 1)->select('nama', 'nip_sapk')->get();

      return response()->json(['skpd' => $skpd, 'pegawai' => $pegawai], 200) ;
    }


}
