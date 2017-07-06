<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\TaLog;
use App\Models\Api;
use DB;


class ApiLogController extends Controller
{
    public function createKey()
    {
      $hash = str_random(35);

      $key = new Api();
      $key->api_key = Hash::make($hash);
      $key->deskripsi = 'RSUD Balaraja';
      $key->save();

      $status = ['code' => 201, 'deskirpsi' => 'Sukses'];

      return response()->json(['status' => $status, 'key' => $key], 201);
    }

    public function Log(){

      $status = ['code'=> 400, 'deskirpsi'=>'Invalid api key, tidak ada dalam database kami.'];

      return response()->json(['status' => $status], 400) ;
    }

    public function postLog(Request $request){

      $api_keys  = Api::where('api_key', $request->api_key)->get();

      if((!$api_keys->isEmpty()) && ($request->Fid != null) && ($request->Tanggal_Log != null) && ($request->Jam_Log != null) && ($request->DateTime != null)){

        // Mach_id 801 Khusus RSUD Balaraja
        // $save = DB::select("INSERT INTO ta_log (Id, Mach_id, Fid, Tanggal_Log, Jam_Log, DateTime)
        //                     SELECT * FROM (SELECT '', '801', '$request->Fid', '$request->Tanggal_Log', '$request->Jam_Log', '$request->DateTime') AS tmp
        //                     WHERE NOT EXISTS (
        //                     	SELECT * FROM ta_log WHERE Mach_id = '801' AND Fid = '$request->Fid' AND Tanggal_Log = '$request->Tanggal_Log' AND Jam_Log = '$request->Jam_Log' AND DateTime = '$request->DateTime'
        //                     ) LIMIT 1;");
        $save = TaLog::where([
                                ['Mach_id', '=', '801'],
                                ['Fid', '=', $request->Fid],
                                ['Tanggal_Log', '=', $request->Tanggal_Log],
                                ['Jam_Log', '=', $request->Jam_Log],
                                ['DateTime', '=', $request->DateTime]
                            ])->first();
        if(!$save){
          $new = new TaLog;
          $new->Mach_id = '801';
          $new->Fid = $request->Fid;
          $new->Tanggal_Log = $request->Tanggal_Log;
          $new->Jam_Log = $request->Jam_Log;
          $new->DateTime = $request->DateTime;
          $new->save();
        }

        $data  = [ 'Fid'  => $request->input('Fid'),
                  'Tanggal_Log'  => $request->input('Tanggal_Log'),
                  'Jam_Log'  => $request->input('Jam_Log'),
                  'DateTime'  => $request->input('DateTime')
                ];

        $status = ['code' => 201, 'deskirpsi' => 'Sukses', 'data' => $data];

        return response()->json(['status'=> $status], 201);

      }else{

        $status = ['code'=> 400, 'deskirpsi'=>'Invalid api key, tidak ada dalam database kami.'];

        return response()->json(['status' => $status], 400);
      }


    }

}
