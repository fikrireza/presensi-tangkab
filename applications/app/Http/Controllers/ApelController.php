<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Apel;
use App\Models\MesinApel;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Struktural;
use App\Models\User;
use App\Models\Intervensi;

use Auth;
use Validator;
use DB;
use PDF;

class ApelController extends Controller
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
      $getApel = apel::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_apel.actor')
                      ->select('preson_apel.*', 'preson_pegawais.nama as actor')
                      ->orderBy('tanggal_apel', 'desc')->get();

      return view('pages.apel.index', compact('getApel'));
    }

    public function store(Request $request)
    {
      $message = [
        'tanggal_apel.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'tanggal_apel' => 'required',
        'keterangan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('apel.index')->withErrors($validator)->withInput();
      }

      $set = new apel;
      $set->tanggal_apel = $request->tanggal_apel;
      $set->keterangan = $request->keterangan;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('apel.index')->with('berhasil', 'Berhasil Menambahkan Hari Apel');
    }

    public function bind($id)
    {

      $getApel = apel::find($id);

      return $getApel;
    }

    public function edit(Request $request)
    {
      $message = [
        'tanggal_apel_edit.required' => 'Wajib di isi',
        'keterangan_edit.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'tanggal_apel_edit' => 'required',
        'keterangan_edit' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('apel.index')->withErrors($validator)->withInput();
      }


      $set = harilibur::find($request->id);
      $set->libur = $request->tanggal_apel_edit;
      $set->keterangan = $request->keterangan_edit;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('apel.index')->with('berhasil', 'Berhasil Merubah Data Hari Apel');
    }

    public function mesin()
    {
      $getMesin = mesinapel::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_mesinapel.actor')
                            ->select('preson_mesinapel.*', 'preson_pegawais.nama as actor')
                            ->get();

      return view('pages.apel.mesin', compact('getMesin'));
    }

    public function mesinPost(Request $request)
    {
      $message = [
        'mach_id.required' => 'Wajib di isi',
      ];

      $validator = validator::make($request->all(), [
        'mach_id' => 'required|max:3',
      ], $message);

      if($validator->fails()){
        return redirect()->route('apel.mesin')->withErrors($message)->withInput();
      }

      $save = new mesinapel;
      $save->mach_id = $request->mach_id;
      $save->catatan = $request->catatan;
      $save->flag_status = 1;
      $save->actor = Auth::user()->pegawai_id;
      $save->save();

      return redirect()->route('apel.mesin')->with('berhasil', 'Berhasil Menambahkan Nomor Mesin Apel');

    }

    public function pegawaiapel()
    {
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $getStruktural = struktural::get();

      return view('pages.apel.pegawaiapel', compact('getApel', 'getStruktural'));
    }

    public function pegawaiapelStore(Request $request)
    {
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $tanggalApel = apel::select('id', 'tanggal_apel')->where('id', '=', $request->apel_id)->first();
        $tanggalApelnya = date('d/m/Y', strtotime($tanggalApel->tanggal_apel));
      $getAbsenApel = DB::select("SELECT a.Mach_id, a.Fid, a.Tanggal_Log, a.Jam_Log, c.skpd_id as skpd, c.nama as pegawai,
                                        d.id as struktural
                                  FROM ta_log a, preson_mesinapel b, preson_pegawais c, preson_strukturals d
                                  WHERE a.Mach_id = b.mach_id
                                  AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalApelnya'
                                  AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                  AND a.Fid = c.fid
                                  AND c.struktural_id = d.id
                                  GROUP BY c.nama");

      $getSkpd = skpd::join('preson_pegawais', 'preson_pegawais.skpd_id', '=', 'preson_skpd.id')
                      ->select('preson_skpd.*')
                      ->groupby('preson_skpd.id')
                      ->get();

      $getStruktural = struktural::get();

      $jumlahPegawaiSKPD = DB::select("select b.nama as skpd, a.skpd_id, count(a.skpd_id) as jumlah_pegawai
                                      from preson_pegawais a, preson_skpd b
                                      where a.skpd_id = b.id
                                      group by skpd_id");

      return view('pages.apel.pegawaiapel', compact('getApel', 'tanggalApel', 'getAbsenApel', 'getSkpd', 'getStruktural', 'jumlahPegawaiSKPD'));
    }

    public function pegawaiapelCetak(Request $request)
    {
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $tanggalApel = apel::select('id', 'tanggal_apel')->where('id', '=', $request->apel_id)->first();
        $tanggalApelnya = date('d/m/Y', strtotime($tanggalApel->tanggal_apel));
      $getAbsenApel = DB::select("SELECT a.Mach_id, a.Fid, a.Tanggal_Log, a.Jam_Log, c.skpd_id as skpd, c.nama as pegawai,
                                        d.id as struktural
                                  FROM ta_log a, preson_mesinapel b, preson_pegawais c, preson_strukturals d
                                  WHERE a.Mach_id = b.mach_id
                                  AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalApelnya'
                                  AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                                  AND a.Fid = c.fid
                                  AND c.struktural_id = d.id
                                  GROUP BY c.nama");

      $getSkpd = skpd::join('preson_pegawais', 'preson_pegawais.skpd_id', '=', 'preson_skpd.id')
                      ->select('preson_skpd.*')
                      ->groupby('preson_skpd.id')
                      ->get();

      $getStruktural = struktural::get();

      $jumlahPegawaiSKPD = DB::select("select b.nama as skpd, a.skpd_id, count(a.skpd_id) as jumlah_pegawai
                                      from preson_pegawais a, preson_skpd b
                                      where a.skpd_id = b.id
                                      group by skpd_id");

      view()->share('tanggalApelnya', $tanggalApelnya);
      view()->share('getAbsenApel', $getAbsenApel);
      view()->share('getSkpd', $getSkpd);
      view()->share('getStruktural', $getStruktural);
      view()->share('jumlahPegawaiSKPD', $jumlahPegawaiSKPD);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.apel.cetakPegawaiApel')->setPaper('a4', 'landscape');
        return $pdf->download('Presensi Online Apel Periode '.$tanggalApelnya.'.pdf');
      }

      return view('pages.apel.cetakPegawaiApel');
    }

    public function pegawaiapelDetail($skpd, $tanggal_apel)
    {
      $tanggalApelnya = apel::select('id', 'tanggal_apel')->where('id', '=', $tanggal_apel)->first();
      $tanggalApel = date('d/m/Y', strtotime($tanggalApelnya->tanggal_apel));

      $getDetail = DB::select("SELECT pegawai.fid, pegawai.nama as pegawai, Jam_Log, struktural, skpd, nama_skpd, nip_sapk
                              	FROM (select a.nama, a.fid, a.nip_sapk, a.skpd_id as skpd, c.nama as nama_skpd, b.nama as struktural
                              					from preson_pegawais a, preson_strukturals b, preson_skpd c
                              						where a.skpd_id = '$skpd'
                              						and c.id = a.skpd_id
                              						and b.id = a.struktural_id) as pegawai

                              	LEFT OUTER JOIN (select a.Jam_Log as Jam_Log, a.Fid as Fid
                              										from ta_log a, preson_mesinapel b, preson_pegawais c, preson_strukturals d
                              										WHERE a.Mach_id = b.mach_id
                              										AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalApel'
                              										AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                              										AND a.Fid = c.fid
                              										AND c.struktural_id = d.id
                              										and c.skpd_id = '$skpd') as tabel_Apel
                              	ON pegawai.fid = tabel_Apel.Fid
                              GROUP BY pegawai.nama
                              ORDER BY pegawai.struktural ASC");

      if($getDetail == null){
        return redirect()->back();
      }else{
        return view('pages.apel.pegawaiapeldetail', compact('getDetail', 'tanggalApel', 'skpd', 'tanggalApelnya'));
      }

    }

    public function pegawaiapelDetailCetak(Request $request)
    {
      $tanggalApelnya = apel::select('tanggal_apel')->where('id', '=', $request->tanggalApel)->first();
      $tanggalApel = date('d/m/Y', strtotime($tanggalApelnya->tanggal_apel));

      $getDetail = DB::select("SELECT pegawai.fid, pegawai.nama as pegawai, Jam_Log, struktural, skpd, nama_skpd, nip_sapk
                              	FROM (select a.nama, a.fid, a.nip_sapk, a.skpd_id as skpd, c.nama as nama_skpd, b.nama as struktural
                              					from preson_pegawais a, preson_strukturals b, preson_skpd c
                              						where a.skpd_id = '$request->skpd'
                              						and c.id = a.skpd_id
                              						and b.id = a.struktural_id) as pegawai

                              	LEFT OUTER JOIN (select a.Jam_Log as Jam_Log, a.Fid as Fid
                              										from ta_log a, preson_mesinapel b, preson_pegawais c, preson_strukturals d
                              										WHERE a.Mach_id = b.mach_id
                              										AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalApel'
                              										AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                              										AND a.Fid = c.fid
                              										AND c.struktural_id = d.id
                              										and c.skpd_id = '$request->skpd') as tabel_Apel
                              	ON pegawai.fid = tabel_Apel.Fid
                              GROUP BY pegawai.nama
                              ORDER BY pegawai.struktural ASC");

      view()->share('tanggalApel', $tanggalApel);
      view()->share('getDetail', $getDetail);
      view()->share('tanggalApel', $tanggalApel);

      if($request->has('download')){
        $pdf = PDF::loadView('pages.apel.cetakPegawaiApelDetail
        ')->setPaper('a4', 'portrait');
        return $pdf->download('Presensi Online Apel Periode '.$tanggalApel.'.pdf');
      }

      return view('pages.apel.cetakPegawaiApelDetail');

    }

    public function apelSKPD()
    {
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.apel.apelskpd', compact('getApel', 'getunreadintervensi'));
    }

    public function apelSKPDStore(Request $request)
    {
      $skpd = Auth::user()->skpd_id;
      $getApel = apel::orderBy('tanggal_apel', 'desc')->get();
      $tanggalApel = apel::select('id', 'tanggal_apel')->where('id', '=', $request->apel_id)->first();
        $tanggalApelnya = date('d/m/Y', strtotime($tanggalApel->tanggal_apel));

      $getDetail = DB::select("SELECT pegawai.fid, pegawai.nama as pegawai, Jam_Log, struktural, skpd, nama_skpd, nip_sapk
                              	FROM (select a.nama, a.fid, a.nip_sapk, a.skpd_id as skpd, c.nama as nama_skpd, b.nama as struktural
                              					from preson_pegawais a, preson_strukturals b, preson_skpd c
                              						where a.skpd_id = '$skpd'
                              						and c.id = a.skpd_id
                              						and b.id = a.struktural_id) as pegawai

                              	LEFT OUTER JOIN (select a.Jam_Log as Jam_Log, a.Fid as Fid
                              										from ta_log a, preson_mesinapel b, preson_pegawais c, preson_strukturals d
                              										WHERE a.Mach_id = b.mach_id
                              										AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalApelnya'
                              										AND TIME_FORMAT(STR_TO_DATE(a.Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '10:00:00'
                              										AND a.Fid = c.fid
                              										AND c.struktural_id = d.id
                              										and c.skpd_id = '$skpd') as tabel_Apel
                              	ON pegawai.fid = tabel_Apel.Fid
                              GROUP BY pegawai.nama
                              ORDER BY pegawai.struktural ASC");

      if($getDetail == null){
        return redirect()->back();
      }else{
        return view('pages.apel.apelskpd', compact('getApel', 'getDetail', 'tanggalApel', 'skpd', 'tanggalApelnya'));
      }


    }
}
