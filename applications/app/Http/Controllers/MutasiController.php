<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Mutasi;
use App\Models\Intervensi;

use Validator;
use Auth;
use DB;
use Hash;

class MutasiController extends Controller
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
      $mutasi = Mutasi::get();

      $pegawai_id = Auth::user()->pegawai_id;
      $skpd_id = Auth::user()->skpd_id;

      if(session('status') == 'administrator')
      {
      $list = [];
      $getmutasi = collect($list);

      $listskpd = DB::select("select a.id, a.pegawai_id, (select b.skpd_id_new from preson_mutasi b where
                            b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) as skpd_new_last,
                            (select d.nama from preson_mutasi b left join preson_skpd d on
                            b.skpd_id_new = d.id where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) as  skpd_nama_last from preson_mutasi a left join preson_pegawais c on a.pegawai_id=c.id
                            group by skpd_new_last order by skpd_new_last asc");

      $getskpd = collect($listskpd);

      $getskpdterkait = [];

      }else if (session('status') == 'admin'){

        // $getmutasi = Mutasi::Select('preson_mutasi.id','preson_mutasi.pegawai_id', 'preson_mutasi.skpd_id_new', 'preson_mutasi.skpd_id_old', DB::raw('count(preson_mutasi.pegawai_id) as jumlahmutasi'))
        //           ->where('preson_mutasi.skpd_id_new', Auth::user()->skpd_id)
        //           ->whereNotIn('preson_mutasi.pegawai_id', [Auth::user()->id])
        //           ->groupBy('preson_mutasi.pegawai_id')
        //           ->orderby('preson_mutasi.skpd_id_old', 'desc')
        //           ->get();

        $list = DB::select("select a.id, a.pegawai_id,c.nip_sapk, c.nama as nama_pegawai,
                          (select b.skpd_id_old from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) as skpd_old_last,
                          (select b.skpd_id_new from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) as skpd_new_last,
                           d.nama as nama_skpd,
                          (select count(1) from preson_mutasi e where e.pegawai_id = a.pegawai_id) as jumlahmutasi
                            from preson_mutasi a left join preson_pegawais c on a.pegawai_id=c.id
                            left join preson_skpd d on
                          (select b.skpd_id_old from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) = d.id
                            where (select b.skpd_id_new from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) =  ('$skpd_id')
                          group by a.pegawai_id order by a.skpd_id_old desc");

        $getmutasi = collect($list);

        $getskpd = [];

        $getskpdterkait = Skpd::where('id', $skpd_id)->first();

        $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                           ->where('preson_intervensis.flag_view', 0)
                                           ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                           ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                           ->count();
      }

      return view('pages.mutasi.index', compact('getunreadintervensi', 'getmutasi','mutasi', 'getskpd', 'getskpdterkait'));
    }

    public function create($id)
    {

      $getpegskpd = Pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                ->Select('preson_pegawais.id as pegawai_id','preson_pegawais.nip_sapk as pegawai_nip_sapk', 'preson_pegawais.nama as pegawai_nama', 'preson_skpd.id as skpd_id', 'preson_skpd.nama as skpd_nama')->Where('preson_pegawais.id','=',$id)->first();


      $getskpd = Skpd::whereNotIn('id', [$getpegskpd->skpd_id])->get();

      return view('pages.mutasi.create', compact('getskpd', 'getpegskpd'));
    }


    public function createStore(Request $request)
    {

      // dd($request);
      $message = [
        'skpd_id_new.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'tanggal_mutasi.required' => 'Wajib di isi',
        // 'tpp_dibayarkan.required' => 'Wajib di isi',
        'nomor_sk.required' => 'Wajib di isi',
        'tanggal_sk.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($request->all(), [
        'skpd_id_new' => 'required',
        'keterangan' => 'required',
        'tanggal_mutasi' => 'required',
        // 'tpp_dibayarkan' => 'required',
        'nomor_sk' => 'required',
        'tanggal_sk' => 'required'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('mutasi.create', ['id' => $request->pegawai_id])->withErrors($validator)->withInput();
      }
      $fileTemp = $request->file('upload_sk') ;
      if ($fileTemp != null) {
        $i = 1;
         $doc_name = '';
          foreach ($request->file('upload_sk') as $key) {
            $file = $request->upload_sk[$i];
            $file_name = $request->pegawai_nip_sapk.'-'.$request->nama_pegawai.'-'.$request->tanggal_mutasi.'-'.$request->tanggal_sk.'-'.$i.'.'. $file->getClientOriginalExtension();
            $doc_name .= $file_name.'//';

            $file->move('documents/', $file_name);
            $i++;
          }
      } else {
        $doc_name = '-';
      }

      $new = new Mutasi;
      $new->pegawai_id = $request->pegawai_id;
      $new->skpd_id_old = $request->skpd_id_old;
      $new->skpd_id_new = $request->skpd_id_new;
      $new->tanggal_mutasi = $request->tanggal_mutasi;
      $new->keterangan = $request->keterangan;
      $new->tpp_dibayarkan = $request->tpp_dibayarkan;
      $new->nomor_sk = $request->nomor_sk;
      $new->tanggal_sk = $request->tanggal_sk;
      $new->upload_sk = $doc_name;
      $new->actor = Auth::user()->id;
      $new->flag_mutasi = 1;
      $new->save();

      $set = pegawai::find($request->pegawai_id);
      $set->skpd_id = $request->skpd_id_new;
      $set->flag_mutasi = 1;
      $set->update();

      $akses = User::where('pegawai_id', $request->pegawai_id)->first();
      $akses->role_id = 3;
      $akses->skpd_id = $request->skpd_id_new;
      $akses->update();

      return redirect()->route('pegawai.index')->with('berhasil', 'Pegawai Berhasil Dimutasi');
    }

    public function viewAll($id)
    {
      $mutasi = Mutasi::get();

      $pegawai_id = Auth::user()->pegawai_id;

      $list = DB::select("select a.id, a.pegawai_id,c.nip_sapk, c.nama as nama_pegawai,
                          (select b.skpd_id_old from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) as skpd_old_last,
                          (select b.skpd_id_new from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) as skpd_new_last,
                           d.nama as nama_skpd,
                          (select count(1) from preson_mutasi e where e.pegawai_id = a.pegawai_id) as jumlahmutasi
                            from preson_mutasi a left join preson_pegawais c on a.pegawai_id=c.id
                            left join preson_skpd d on
                          (select b.skpd_id_old from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) = d.id
                            where (select b.skpd_id_new from preson_mutasi b where b.pegawai_id = a.pegawai_id order by b.created_at desc limit 1) =  ('$id')
                          group by a.pegawai_id order by a.skpd_id_old desc");

      $getmutasi = collect($list);

      $getskpdterkait = Skpd::where('id', $id)->first();

      // dd($getskpdterkait);
      return view('pages.mutasi.viewall', compact('getmutasi', 'getskpdterkait'));
    }


    public function view($id)
    {

      $getmutasi = Mutasi::Where('pegawai_id', $id)->orderBy('created_at','desc')->paginate(5);
      $empty = "";
      if ($getmutasi[0] != null) {
        $empty = "Tidak Kosong";
      } else {
        $empty = "Kosong";
      }
      // dd($getmutasi);
      return view('pages.mutasi.view', compact('getmutasi','empty'));
    }

    public function viewPegawai()
    {
      $getmutasi = Mutasi::Where('pegawai_id', Auth::user()->pegawai_id)->orderBy('created_at','desc')->paginate(5);
      $empty = "";
      if ($getmutasi[0] != null) {
        $empty = "Tidak Kosong";
      } else {
        $empty = "Kosong";
      }

      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.mutasi.view', compact('getmutasi','empty', 'getunreadintervensi'));
    }
}
