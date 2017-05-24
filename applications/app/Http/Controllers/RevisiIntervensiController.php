<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\User;
use App\Models\ManajemenIntervensi;
use App\Models\Intervensi;
use App\Models\HariLibur;

use Validator;
use Auth;
use DB;
use Hash;
use DateTime;
use DatePeriod;
use DateIntercal;
use DateInterval;
use Carbon\Carbon;

class RevisiIntervensiController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
       $getrevisiintervensi = DB::table('preson_intervensis')->select('preson_intervensis.*',
                      'preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as nip_sapk_pegawai','preson_pegawais.nama')
                  ->leftJoin('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
                  ->orderby('preson_intervensis.created_at', 'desc')
                  ->where('preson_intervensis.id_intervensi', 9999)->get();
      // dd($getrevisiintervensi);
      return view('pages.revisiintervensi.index', compact('getrevisiintervensi'));
    }


    public function create()
    {
      $getskpd = Skpd::select('*')->get();
      $getcaripegawai = null;
      // dd($getcaripegawai);
      $skpd_id = "";
      return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai','skpd_id'));
    }


    public function caripegawai(Request $request)
      {
      // dd($request);
      $message = [
        'skpd.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'skpd' => 'required'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.create')->withErrors($validator)->withInput();
      }

      $getskpd = Skpd::select('*')->get();
      $skpd_id = $request->skpd;
      $getcaripegawai = Pegawai::select('*')->where('skpd_id', $request->skpd)->get();
      // dd($getcaripegawai);

        return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai','skpd_id'));
    }

    public function createStore(Request $request)
    {
      // dd($request);
       $message = [
        // 'skpd.required' => 'Wajib di isi',
        'tanggal_mulai.required' => 'Wajib di isi',
        'tanggal_akhir.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'upload_revisi' => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        // 'skpd' => 'required',
        'tanggal_mulai' => 'required',
        'tanggal_akhir' => 'required',
        'keterangan' => 'required',
        'upload_revisi'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.create')->withErrors($validator)->withInput();
      }

      //start menentukan tanggal kurang dari 3 hari
        // $datestart = Carbon::createFromFormat('Y-m-d',  $request->tanggal_mulai);
        // $dateend = Carbon::createFromFormat('Y-m-d',  $request->tanggal_akhir);
        // $datenow = Carbon::today();

        // $interval = date_diff($datenow, $datestart);
        // $getvalcount =  $interval->format('%R%a');
        // // total hari
        // $days = $getvalcount;

        // // create an iterateable period of date (P1D equates to 1 day)
        // $period = new DatePeriod($datestart, new DateInterval('P1D'), $datenow);

        // foreach($period as $dt) {
        //     $curr = $dt->format('D');

        //     // substract if Saturday or Sunday
        //     if ($curr == 'Sat' || $curr == 'Sun') {
        //         if($days > 0)
        //           {
        //             $days--;
        //           }
        //           else if($days < 0)
        //           {
        //             $days++;
        //           }
        //           else
        //           {
        //             $days--;
        //           }
        //     }

        // }
        // if($days >= -2)
        // {

        // } else {
        //   return redirect()->route('revisiintervensi.create')->with('gagaltgl',' Tanggal yang pilih lebih dari 3 hari sebelum hari ini.')->withInput();
        // }
      //end menentukan tanggal kurang dari 3 hari


      // dd($request->idpegawai);
      if ($request->idpegawai != null) {

          //start set Jumlah Hari Intervensi
          $getcountharilibur = HariLibur::whereBetween('libur', [$request->tanggal_mulai,$request->tanggal_akhir])->count();
          $countjumlhari = $request->jumlah_hari - $getcountharilibur;
          //end set Jumlah Hari Intervensi

        foreach ($request->idpegawai as $key_pegawai) {

            // --- validasi ketersediaan tanggal intervensi
            $gettanggalintervensi = Intervensi::select('preson_intervensis.*', 'preson_pegawais.nip_sapk'
                                                , 'preson_pegawais.nama')
                                                ->leftJoin('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
                                                ->where('preson_intervensis.pegawai_id', $key_pegawai)
                                                ->get();

            $tanggalmulai = $request->tanggal_mulai;
            $tanggalakhir = $request->tanggal_akhir;

            $dateRange=array();
            $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2),     substr($tanggalmulai,8,2),substr($tanggalmulai,0,4));
            $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2),     substr($tanggalakhir,8,2),substr($tanggalakhir,0,4));

            if ($iDateTo>=$iDateFrom)
            {
                array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
                while ($iDateFrom<$iDateTo)
                {
                    $iDateFrom+=86400; // add 24 hours
                    array_push($dateRange,date('Y-m-d',$iDateFrom));
                }
            }
            $getnamapegawai = "";
            $flagtanggal = 0;
            foreach ($dateRange as $key) {
              foreach ($gettanggalintervensi as $keys) {
                $start_ts = strtotime($keys->tanggal_mulai);
                $end_ts = strtotime($keys->tanggal_akhir);
                $user_ts = strtotime($key);

                if (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) {
                  $flagtanggal=1;
                  $getnamapegawai = $keys->nama;
                  break;
                }
              }
              if ($flagtanggal==1) break;
            }
            // dd($getnamapegawai);
            if ($flagtanggal==1) {
              return redirect()->route('revisiintervensi.create')->with('gagal', $getnamapegawai.' Tanggal yang pilih telah tercatat pada database.')->withInput();
            }
            // --- end of validasi ketersediaan tanggal intervensi
        }

        // biar file yang diupload tidak ngeloop
        $file = $request->file('upload_revisi');
          if($file != null)
          {
              $photo_name = Auth::user()->nip_sapk.'-'.'RevisiIntervensi'.'-'.$request->tanggal_mulai.'-'.$request->tanggal_akhir.'-'.$request->upload_revisi->getClientOriginalName().'.' . $file->getClientOriginalExtension();
              $file->move('documents/', $photo_name);
            }else{
              $photo_name = "-";
          }
        // biar file yang diupload tidak ngeloop

        foreach ($request->idpegawai as $key_pegawai) {
          $valjamdtg="";
          if($request->status_jam_datang=="") {
            $valjamdtg=0;
          } else {
            $valjamdtg=1;
          }

          $valjamplg="";
          if($request->status_jam_pulang=="") {
            $valjamplg=0;
          } else {
            $valjamplg=1;
          }

            $set = new Intervensi;
            $set->pegawai_id = $key_pegawai;
            $set->id_intervensi = 9999;
            $getnamaintervensi = ManajemenIntervensi::find(9999);
            $set->jenis_intervensi = $getnamaintervensi->nama_intervensi;
            $set->jumlah_hari = $countjumlhari;
            $set->tanggal_mulai = $request->tanggal_mulai;
            $set->tanggal_akhir = $request->tanggal_akhir;
            $set->deskripsi = $request->keterangan;

            $set->berkas = $photo_name;
            $set->flag_status = 1;
            $set->status_jam_datang = $valjamdtg;
            $set->status_jam_pulang = $valjamplg;
            $set->actor = Auth::user()->pegawai_id;
            $set->save();
        }
        return redirect()->route('revisiintervensi.index')->with('berhasil', 'Pegawai Berhasil Intervensi');
      }else{
        return redirect()->route('revisiintervensi.create')->with('gagal', 'Pilih data pegawai tersebuh dahulu.');
      }
    }

    public function bind($id)
    {
      $get = Intervensi::where('preson_intervensis.id', $id)
                          ->leftJoin('preson_pegawais', 'preson_intervensis.pegawai_id', '=', 'preson_pegawais.id')
                          ->select('preson_intervensis.*', 'preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as nip_sapk_pegawai','preson_pegawais.nama')
                          ->first();
      return $get;
    }

    public function edit(Request $request)
    {
      // dd($request);
      $message = [
        'keterangan_edit.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'keterangan_edit' => 'required'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.index')->withErrors($validator)->withInput();
      }


      // dd($request);
      $valjamdtg="";
      if($request->status_jam_datang_edit=="") {
        $valjamdtg=0;
      } else {
        $valjamdtg=1;
      }

      $valjamplg="";
      if($request->status_jam_pulang_edit=="") {
        $valjamplg=0;
      } else {
        $valjamplg=1;
      }

      $set = Intervensi::find($request->id);
      $set->jumlah_hari = $request->jumlah_hari_edit;
      $set->tanggal_mulai = $request->tanggal_mulai_edit;
      $set->tanggal_akhir = $request->tanggal_akhir_edit;
      $set->deskripsi = $request->keterangan_edit;
      $set->status_jam_datang = $valjamdtg;
      $set->status_jam_pulang = $valjamplg;
      $file = $request->file('upload_revisi');
      if($file != null)
        {
          $photo_name = Auth::user()->nip_sapk.'-'.'RevisiIntervensi'.'-'.$request->tanggal_mulai.'-'.$request->tanggal_akhir.'-'.$request->upload_revisi->getClientOriginalName().'.' . $file->getClientOriginalExtension();
          $file->move('documents/', $photo_name);
          $set->berkas = $photo_name;
        }
      $set->actor = Auth::user()->pegawai_id;
      $set->update();

      return redirect()->route('revisiintervensi.index')->with('berhasil', 'Berhasil Mengubah Data Revisi Intervensis');
    }
}
