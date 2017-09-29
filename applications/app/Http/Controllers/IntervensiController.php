<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ManajemenIntervensi;
use App\Models\Intervensi;
use App\Models\Pegawai;
use App\Models\Users;
use App\Models\Skpd;
use App\Models\HariLibur;

use Validator;
use Auth;
use DB;
use Image;
use PDF;
use DateTime;
use DatePeriod;
use DateIntercal;
use DateInterval;
use Carbon\Carbon;

class IntervensiController extends Controller
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
      $intervensi = intervensi::where('pegawai_id', Auth::user()->pegawai_id)->orderby('id', 'desc')->get();
      $getmasterintervensi = ManajemenIntervensi::where('flag_old', 0)->where('id', '!=', 9999)->get();
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();
      $getpegawai = Pegawai::where('skpd_id', Auth::user()->skpd_id)->get();

      return view('pages.intervensi.index', compact('intervensi', 'getmasterintervensi','getunreadintervensi', 'getpegawai'));
    }

    public function store(Request $request)
    {
      // --- validasi form input
      $message = [
        'jenis_intervensi.required' => 'Wajib di isi',
        'tanggal_mulai.required' => 'Wajib di isi',
        'tanggal_akhir.required' => 'Wajib di isi',
        'jumlah_hari.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        // 'berkas'  => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'jenis_intervensi' => 'required',
        'tanggal_mulai' => 'required',
        'tanggal_akhir' => 'required',
        'jumlah_hari' => 'required',
        'keterangan' => 'required',
        // 'berkas'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('intervensi.index')->withErrors($validator)->withInput();
      }
      // --- end of validasi form input


      //------ start menentukan tanggal kurang dari 3 hari
        $datestart = Carbon::createFromFormat('Y-m-d',  $request->tanggal_mulai);
        $dateend = Carbon::createFromFormat('Y-m-d',  $request->tanggal_akhir);
        $datenow = Carbon::today();

        $getcountharilibur = HariLibur::whereBetween('libur', [$request->tanggal_mulai,$request->tanggal_akhir])->count();

        $countjumlhari = $request->jumlah_hari - $getcountharilibur;

        $interval = date_diff($datenow, $datestart);
        $getvalcount =  $interval->format('%R%a');
        // total hari
        $days = $getvalcount;

        // create an iterateable period of date (P1D equates to 1 day)
        $period = new DatePeriod($datestart, new DateInterval('P1D'), $datenow);

        foreach($period as $dt) {
            $curr = $dt->format('D');

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                if($days > 0)
                  {
                    $days--;
                  }
                  else if($days < 0)
                  {
                    $days++;
                  }
                  else
                  {
                    $days--;
                  }
            }

        }
      if($days >= -45)
      {

      } else {
        return redirect()->route('intervensi.index')->with('gagaltgl',' Tanggal yang pilih lebih dari 45 hari sebelum hari ini.');
      }
      //------ end menentukan tanggal kurang dari 3 hari


      // [di non aktifkan per tanggal 31 mei 2017]
      // --- validasi izin tidak masuk kerja 2x sebulan
      // if ($request->jenis_intervensi==13) {
      //   if ($request->jumlah_hari>2) {
      //     return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin tidak masuk kerja melebihi batas maksimal.');
      //   }
      //
      //   $datenow = date('m-Y');
      //   $pegawaiid = Auth::user()->pegawai_id;
      //   $countsum = DB::select("select sum(jumlah_hari) as 'total' from preson_intervensis
      //                                   where DATE_FORMAT(tanggal_mulai,'%m-%Y') = '$datenow'
      //                                   and pegawai_id = $pegawaiid and id_intervensi = 13");
      //
      //   $result = $countsum[0]->total;
      //   if ($result>=2) {
      //     return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin tidak masuk kerja melebihi batas maksimal.');
      //   }
      // }
      // --- end of validasi izin tidak masuk kerja 2x sebulan


      // --- validasi izin datang telat/pulang cepat 2x sebulan
      if ($request->jenis_intervensi==2 || $request->jenis_intervensi==3 || $request->jenis_intervensi==13) {
        if ($request->jumlah_hari>2) {
          return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin datang telat atau pulang cepat melebihi batas maksimal.');
        }

        $datenow = date('m-Y');
        $pegawaiid = Auth::user()->pegawai_id;
        $countsum = DB::select("select sum(jumlah_hari) as 'total' from preson_intervensis
                                where DATE_FORMAT(tanggal_mulai,'%m-%Y') = '$datenow'
                                and pegawai_id = $pegawaiid and (id_intervensi = 2 or id_intervensi = 3 or id_intervensi = 13) and flag_status != 3");

        $result = $countsum[0]->total;
        if ($result>=2) {
          return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin datang telat atau pulang cepat melebihi batas maksimal.');
        }
      }
      // --- end of validasi izin datang telat/pulang cepat 2x sebulan


      // --- validasi ketersediaan tanggal intervensi
      $gettanggalintervensi = Intervensi::select('tanggal_mulai', 'tanggal_akhir')
                                          ->where('pegawai_id', Auth::user()->pegawai_id)
                                          ->where('flag_status', '!=', 3)
                                          ->get();

      $tanggalmulai = $request->tanggal_mulai;
      $tanggalakhir = $request->tanggal_akhir;

      $dateRange=array();
      $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
      $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

      if ($iDateTo>=$iDateFrom)
      {
          array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
          while ($iDateFrom<$iDateTo)
          {
              $iDateFrom+=86400; // add 24 hours
              array_push($dateRange,date('Y-m-d',$iDateFrom));
          }
      }

      $flagtanggal = 0;
      foreach ($dateRange as $key) {
        foreach ($gettanggalintervensi as $keys) {
          $start_ts = strtotime($keys->tanggal_mulai);
          $end_ts = strtotime($keys->tanggal_akhir);
          $user_ts = strtotime($key);

          if (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) {
            $flagtanggal=1;
            break;
          }
        }
        if ($flagtanggal==1) break;
      }

      if ($flagtanggal==1) {
        return redirect()->route('intervensi.index')->with('gagal', 'Tanggal intervensi yang anda pilih telah tercatat pada database.');
      }
      // --- end of validasi ketersediaan tanggal intervensi


      // --- proses penyimpanan data ke database
      $file = $request->file('berkas');

      $doc_name = '';
      if($file != null)
      {
        $i = 1;
        foreach ($file as $key) {
          $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_mulai.'-'.$request->jenis_intervensi.'-'.$i.'.'. $key->getClientOriginalExtension();
          $key->move('documents/', $photo_name);
          $doc_name .= $photo_name.'//';
          $i++;
        }
      }
      else
      {
        $doc_name = "-";
      }

      $getnamaintervensi = ManajemenIntervensi::find($request->jenis_intervensi);
      $set = new intervensi;
      $set->pegawai_id = Auth::user()->pegawai_id;
      $set->jenis_intervensi = $getnamaintervensi->nama_intervensi;
-     $set->id_intervensi = $request->jenis_intervensi;
      $set->tanggal_mulai = $request->tanggal_mulai;
      $set->tanggal_akhir = $request->tanggal_akhir;
      $set->jumlah_hari = $countjumlhari;
      $set->deskripsi = $request->keterangan;

      if ($request->atasan!="---") {
        $dataatasan = explode("//", $request->atasan);
        $set->nip_atasan = $dataatasan[0];
        $set->nama_atasan = $dataatasan[1];
      }

      if ($request->jam_ijin!="") {
        $set->jam_ijin = $request->jam_ijin;
      }

      $set->berkas = $doc_name;
      $set->flag_status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('intervensi.index')->with('berhasil', 'Berhasil Menambahkan Intervensi');
      // --- end of proses penyimpanan data ke database

    }

    public function bind($id)
    {

      $find = intervensi::find($id);

      return $find;
    }

    public function edit(Request $request)
    {
      $message = [
        'jenis_intervensi_edit.required' => 'Wajib di isi',
        'tanggal_mulai_edit.required' => 'Wajib di isi',
        'tanggal_akhir_edit.required' => 'Wajib di isi',
        'jumlah_hari_edit.required' => 'Wajib di isi',
        'keterangan_edit.required' => 'Wajib di isi',
        // 'berkas'  => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'jenis_intervensi_edit' => 'required',
        'tanggal_mulai_edit' => 'required',
        'tanggal_akhir_edit' => 'required',
        'jumlah_hari_edit' => 'required',
        'keterangan_edit' => 'required',
        // 'berkas'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('intervensi.index')->withErrors($validator)->withInput();
      }

      //------ start menentukan tanggal kurang dari 3 hari
        $datestart = Carbon::createFromFormat('Y-m-d',  $request->tanggal_mulai_edit);
        $dateend = Carbon::createFromFormat('Y-m-d',  $request->tanggal_akhir_edit);
        $datenow = Carbon::today();

        $getcountharilibur = HariLibur::whereBetween('libur', [$request->tanggal_mulai_edit,$request->tanggal_akhir_edit])->count();

        $countjumlhari = $request->jumlah_hari_edit - $getcountharilibur;

        $interval = date_diff($datenow, $datestart);
        $getvalcount =  $interval->format('%R%a');
        // total hari
        $days = $getvalcount;

        // create an iterateable period of date (P1D equates to 1 day)
        $period = new DatePeriod($datestart, new DateInterval('P1D'), $datenow);

        foreach($period as $dt) {
            $curr = $dt->format('D');

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                if($days > 0)
                  {
                    $days--;
                  }
                  else if($days < 0)
                  {
                    $days++;
                  }
                  else
                  {
                    $days--;
                  }
            }

        }
      if($days >= -45)
      {

      } else {
        return redirect()->route('intervensi.index')->with('gagaltgl',' Tanggal yang pilih lebih dari 45 hari sebelum hari ini.');
      }
      //------ end menentukan tanggal kurang dari 3 hari


      // --- validasi izin tidak masuk kerja 2x sebulan
      if ($request->jenis_intervensi_edit==13) {
        if ($request->jumlah_hari_edit>2) {
          return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin tidak masuk kerja melebihi batas maksimal.');
        }

        $datenow = date('m-Y');
        $pegawaiid = Auth::user()->pegawai_id;
        $countsum = DB::select("select sum(jumlah_hari) as 'total' from preson_intervensis
                                        where DATE_FORMAT(tanggal_mulai,'%m-%Y') = '$datenow'
                                        and pegawai_id = $pegawaiid and id_intervensi = 12");

        $result = $countsum[0]->total;
        if ($result>=2) {
          return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin tidak masuk kerja melebihi batas maksimal.');
        }
      }
      // --- end of validasi izin tidak masuk kerja 2x sebulan


      // --- validasi izin datang telat/pulang cepat 2x sebulan
      if ($request->jenis_intervensi_edit==2 || $request->jenis_intervensi_edit==3) {
        if ($request->jumlah_hari_edit>2) {
          return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin datang telat atau pulang cepat melebihi batas maksimal.');
        }

        $datenow = date('m-Y');
        $pegawaiid = Auth::user()->pegawai_id;
        $countsum = DB::select("select sum(jumlah_hari) as 'total' from preson_intervensis
                                where DATE_FORMAT(tanggal_mulai,'%m-%Y') = '$datenow'
                                and pegawai_id = $pegawaiid and id_intervensi = 5 or id_intervensi = 6");

        $result = $countsum[0]->total;
        if ($result>=2) {
          return redirect()->route('intervensi.index')->with('gagal', 'Jumlah izin datang telat atau pulang cepat melebihi batas maksimal.');
        }
      }
      // --- end of validasi izin datang telat/pulang cepat 2x sebulan


      // --- validasi ketersediaan tanggal intervensi
      $cek = intervensi::find($request->id_edit);
      if ($request->tanggal_mulai_edit!=$cek->tanggal_mulai || $request->tanggal_akhir_edit!=$cek->tanggal_akhir) {
        $gettanggalintervensi = Intervensi::select('tanggal_mulai', 'tanggal_akhir')
                                            ->where('pegawai_id', Auth::user()->pegawai_id)
                                            ->where('id', '!=', $request->id_edit)
                                            ->get();

        $tanggalmulai = $request->tanggal_mulai_edit;
        $tanggalakhir = $request->tanggal_akhir_edit;

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

        $flagtanggal = 0;
        foreach ($dateRange as $key) {
          foreach ($gettanggalintervensi as $keys) {
            $start_ts = strtotime($keys->tanggal_mulai);
            $end_ts = strtotime($keys->tanggal_akhir);
            $user_ts = strtotime($key);

            if (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) {
              $flagtanggal=1;
              break;
            }
          }
          if ($flagtanggal==1) break;
        }

        if ($flagtanggal==1) {
          return redirect()->route('intervensi.index')->with('gagal', 'Tanggal intervensi yang anda pilih telah tercatat pada database.');
        }
      }
      // --- end of validasi ketersediaan tanggal intervensi

      $file = $request->file('berkas_edit');

      if($file != null)
      {
        $doc_name="";
        $i = 1;
        foreach ($file as $key) {
          $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_mulai_edit.'-'.$request->jenis_intervensi_edit.'-'.$i.'.'. $key->getClientOriginalExtension();
          $key->move('documents/', $photo_name);
          $doc_name .= $photo_name.'//';
          $i++;
        }

        $getnamaintervensi = ManajemenIntervensi::find($request->jenis_intervensi_edit);

        $set = intervensi::find($request->id_edit);
        $set->pegawai_id = Auth::user()->pegawai_id;
        $set->jenis_intervensi = $getnamaintervensi->nama_intervensi;
        $set->id_intervensi = $request->jenis_intervensi_edit;
        $set->tanggal_mulai = $request->tanggal_mulai_edit;
        $set->tanggal_akhir = $request->tanggal_akhir_edit;
        $set->jumlah_hari = $countjumlhari;
        $set->deskripsi = $request->keterangan_edit;

        if ($request->atasan_edit!="---") {
          $dataatasan = explode("//", $request->atasan_edit);
          $set->nip_atasan = $dataatasan[0];
          $set->nama_atasan = $dataatasan[1];
        }

        if ($request->jam_ijin_edit!="") {
          $set->jam_ijin = $request->jam_ijin_edit;
        }

        $set->berkas = $doc_name;
        $set->flag_status = 0;
        $set->actor = Auth::user()->pegawai_id;
        $set->save();
      }else{
        $getnamaintervensi = ManajemenIntervensi::find($request->jenis_intervensi_edit);

        $set = intervensi::find($request->id_edit);
        $set->pegawai_id = Auth::user()->pegawai_id;
        $set->jenis_intervensi = $getnamaintervensi->nama_intervensi;
        $set->id_intervensi = $request->jenis_intervensi_edit;
        $set->tanggal_mulai = $request->tanggal_mulai_edit;
        $set->tanggal_akhir = $request->tanggal_akhir_edit;
        $set->jumlah_hari = $countjumlhari;
        $set->deskripsi = $request->keterangan_edit;

        if ($request->atasan_edit!="---") {
          $dataatasan = explode("//", $request->atasan_edit);
          $set->nip_atasan = $dataatasan[0];
          $set->nama_atasan = $dataatasan[1];
        }

        if ($request->jam_ijin_edit!="") {
          $set->jam_ijin = $request->jam_ijin_edit;
        }

        $set->flag_status = 0;
        $set->actor = Auth::user()->pegawai_id;
        $set->save();
      }

      return redirect()->route('intervensi.index')->with('berhasil', 'Berhasil Mengubah Intervensi');
    }

    public function kelola()
    {
      if(session('status') === 'admin')
      {
        $getmasterintervensi = ManajemenIntervensi::where('flag_old', 0)->where('id', '!=', 9999)->get();

        $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                              ->join('preson_users', 'preson_users.skpd_id', '=', 'preson_pegawais.skpd_id')
                              ->where('preson_users.pegawai_id', Auth::user()->pegawai_id)
                              ->select('preson_intervensis.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.nip_sapk')
                              ->orderBy('tanggal_mulai', 'desc')
                              ->get();

        $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                           ->where('preson_intervensis.flag_view', 0)
                                           ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                           ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                           ->count();

        $pegawai = pegawai::select('id', 'nama')->where('skpd_id', Auth::user()->skpd_id)->get();
        $getpegawai = Pegawai::where('skpd_id', Auth::user()->skpd_id)->get();
      }
      elseif(session('status') === 'administrator' || session('status') == 'superuser')
      {
        $getSKPD = skpd::get();

        $pegawai = pegawai::select('id', 'nama')->get();

        $getmasterintervensi = ManajemenIntervensi::where('flag_old', 0)->where('id', '!=', 9999)->get();
        $getpegawai = Pegawai::where('skpd_id', Auth::user()->skpd_id)->get();
      }

      return view('pages.intervensi.kelola', compact('getSKPD', 'getpegawai', 'pegawai', 'intervensi', 'getmasterintervensi', 'getunreadintervensi'));
    }

    public function kelolaAksi($id)
    {
      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                            ->select('preson_pegawais.nama as nama_pegawai', 'preson_intervensis.*')
                            ->where('preson_intervensis.id', $id)->first();

      if($intervensi == null){
        abort(404);
      }

      $set = intervensi::find($id);
      $set->flag_view = 1;
      $set->save();

      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();

      return view('pages.intervensi.aksi', compact('intervensi', 'getunreadintervensi'));
    }


    public function kelolaApprove($id)
    {
      $approve = intervensi::find($id);
      $approve->flag_status = 1;
      $approve->actor = Auth::user()->pegawai_id;
      $approve->update();

      return redirect()->route('intervensi.kelola')->with('berhasil', 'Berhasil Setujui Intervensi');
    }

    public function kelolaDecline($id)
    {
      $approve = intervensi::find($id);
      $approve->flag_status = 2;
      $approve->actor = Auth::user()->pegawai_id;
      $approve->update();

      return redirect()->route('intervensi.kelola')->with('berhasil', 'Berhasil Tolak Intervensi');
    }

    public function kelolaPost(Request $request)
    {
      // dd($request);
      // --- validasi form input
      $message = [
        'jenis_intervensi.required' => 'Wajib di isi',
        'tanggal_mulai.required' => 'Wajib di isi',
        'tanggal_akhir.required' => 'Wajib di isi',
        'jumlah_hari.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        // 'berkas'  => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'jenis_intervensi' => 'required',
        'tanggal_mulai' => 'required',
        'tanggal_akhir' => 'required',
        'jumlah_hari' => 'required',
        'keterangan' => 'required',
        // 'berkas'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('intervensi.kelola')->withErrors($validator)->withInput();
      }
      // --- end of validasi form input


      //------ start menentukan tanggal kurang dari 3 hari
        $datestart = Carbon::createFromFormat('Y-m-d',  $request->tanggal_mulai);
        $dateend = Carbon::createFromFormat('Y-m-d',  $request->tanggal_akhir);
        $datenow = Carbon::today();

        $getcountharilibur = HariLibur::whereBetween('libur', [$request->tanggal_mulai,$request->tanggal_akhir])->count();

        $countjumlhari = $request->jumlah_hari - $getcountharilibur;

        $interval = date_diff($datenow, $datestart);
        $getvalcount =  $interval->format('%R%a');
        // total hari
        $days = $getvalcount;

        // create an iterateable period of date (P1D equates to 1 day)
        $period = new DatePeriod($datestart, new DateInterval('P1D'), $datenow);

        foreach($period as $dt) {
            $curr = $dt->format('D');

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                if($days > 0)
                  {
                    $days--;
                  }
                  else if($days < 0)
                  {
                    $days++;
                  }
                  else
                  {
                    $days--;
                  }
            }

        }
      if($days >= -45)
      {

      } else {
        return redirect()->route('intervensi.kelola')->with('gagaltgl',' Tanggal yang pilih lebih dari 45 hari sebelum hari ini.');
      }
      //------ end menentukan tanggal kurang dari 3 hari


      // --- validasi izin tidak masuk kerja 2x sebulan
      if ($request->jenis_intervensi==13) {
        if ($request->jumlah_hari>2) {
          return redirect()->route('intervensi.kelola')->with('gagal', 'Jumlah izin tidak masuk kerja melebihi batas maksimal.');
        }

        $datenow = date('m-Y');
        $pegawaiid = Auth::user()->pegawai_id;
        $countsum = DB::select("select sum(jumlah_hari) as 'total' from preson_intervensis
                                        where DATE_FORMAT(tanggal_mulai,'%m-%Y') = '$datenow'
                                        and pegawai_id = $pegawaiid and id_intervensi = 12");

        $result = $countsum[0]->total;
        if ($result>=2) {
          return redirect()->route('intervensi.kelola')->with('gagal', 'Jumlah izin tidak masuk kerja melebihi batas maksimal.');
        }
      }
      // --- end of validasi izin tidak masuk kerja 2x sebulan


      // --- validasi izin datang telat/pulang cepat 2x sebulan
      if ($request->jenis_intervensi==2 || $request->jenis_intervensi==3) {
        if ($request->jumlah_hari>2) {
          return redirect()->route('intervensi.kelola')->with('gagal', 'Jumlah izin datang telat atau pulang cepat melebihi batas maksimal.');
        }

        $datenow = date('m-Y');
        $pegawaiid = Auth::user()->pegawai_id;
        $countsum = DB::select("select sum(jumlah_hari) as 'total' from preson_intervensis
                                where DATE_FORMAT(tanggal_mulai,'%m-%Y') = '$datenow'
                                and pegawai_id = $pegawaiid and id_intervensi = 5 or id_intervensi = 6");

        $result = $countsum[0]->total;
        if ($result>=2) {
          return redirect()->route('intervensi.kelola')->with('gagal', 'Jumlah izin datang telat atau pulang cepat melebihi batas maksimal.');
        }
      }
      // --- end of validasi izin datang telat/pulang cepat 2x sebulan


      // --- validasi ketersediaan tanggal intervensi
      $gettanggalintervensi = Intervensi::select('tanggal_mulai', 'tanggal_akhir')
                                          ->where('pegawai_id', Auth::user()->pegawai_id)
                                          ->where('flag_status', '!=', 3)
                                          ->get();

      $tanggalmulai = $request->tanggal_mulai;
      $tanggalakhir = $request->tanggal_akhir;

      $dateRange=array();
      $iDateFrom=mktime(1,0,0,substr($tanggalmulai,5,2), substr($tanggalmulai,8,2), substr($tanggalmulai,0,4));
      $iDateTo=mktime(1,0,0,substr($tanggalakhir,5,2), substr($tanggalakhir,8,2), substr($tanggalakhir,0,4));

      if ($iDateTo>=$iDateFrom)
      {
          array_push($dateRange,date('Y-m-d',$iDateFrom)); // first entry
          while ($iDateFrom<$iDateTo)
          {
              $iDateFrom+=86400; // add 24 hours
              array_push($dateRange,date('Y-m-d',$iDateFrom));
          }
      }

      $flagtanggal = 0;
      foreach ($dateRange as $key) {
        foreach ($gettanggalintervensi as $keys) {
          $start_ts = strtotime($keys->tanggal_mulai);
          $end_ts = strtotime($keys->tanggal_akhir);
          $user_ts = strtotime($key);

          if (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) {
            $flagtanggal=1;
            break;
          }
        }
        if ($flagtanggal==1) break;
      }

      if ($flagtanggal==1) {
        return redirect()->route('intervensi.kelola')->with('gagal', 'Tanggal intervensi yang anda pilih telah tercatat pada database.');
      }
      // --- end of validasi ketersediaan tanggal intervensi


      // --- proses penyimpanan data ke database
      $file = $request->file('berkas');

      $doc_name = '';
      if($file != null)
      {
        $i = 1;
        foreach ($file as $key) {
          $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_mulai.'-'.$request->jenis_intervensi.'-'.$i.'.'. $key->getClientOriginalExtension();
          $key->move('documents/', $photo_name);
          $doc_name .= $photo_name.'//';
          $i++;
        }
      }
      else
      {
        $doc_name = "-";
      }

      $getnamaintervensi = ManajemenIntervensi::find($request->jenis_intervensi);
      $set = new intervensi;
      $set->pegawai_id = $request->pegawai_id;
      $set->jenis_intervensi = $getnamaintervensi->nama_intervensi;
-     $set->id_intervensi = $request->jenis_intervensi;
      $set->tanggal_mulai = $request->tanggal_mulai;
      $set->tanggal_akhir = $request->tanggal_akhir;
      $set->jumlah_hari = $countjumlhari;
      $set->deskripsi = $request->keterangan;

      if ($request->atasan!="---") {
        $dataatasan = explode("//", $request->atasan);
        $set->nip_atasan = $dataatasan[0];
        $set->nama_atasan = $dataatasan[1];
      }

      if ($request->jam_ijin!="") {
        $set->jam_ijin = $request->jam_ijin;
      }

      $set->berkas = $doc_name;
      $set->flag_status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('intervensi.index')->with('berhasil', 'Berhasil Menambahkan Intervensi');
      // --- end of proses penyimpanan data ke database

    }

    public function skpd($id)
    {
      $id = skpd::find($id);

      if($id == null){
        abort(404);
      }

      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                            ->join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->select('preson_intervensis.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.nip_sapk')
                            ->where('preson_skpd.id', '=', $id->id)
                            ->orderBy('tanggal_mulai', 'desc')
                            ->get();

      $pegawai = pegawai::select('id', 'nama')->where('skpd_id', Auth::user()->skpd_id)->get();
      $getpegawai = Pegawai::where('skpd_id', Auth::user()->skpd_id)->get();

      $getmasterintervensi = ManajemenIntervensi::where('flag_old', 0)->get();

      return view('pages.intervensi.detailSKPD', compact('intervensi', 'getpegawai', 'pegawai', 'getmasterintervensi'));
    }

    public function batal($id)
    {
      $approve = intervensi::find($id);
      $approve->flag_status = 3;
      $approve->actor = Auth::user()->pegawai_id;
      $approve->update();

      return redirect()->route('intervensi.index')->with('berhasil', 'Berhasil Batalkan Intervensi');
    }

    public function resetStatus($id)
    {
      $set = Intervensi::find($id);
      $set->flag_status = 0;
      $set->save();

      return redirect()->route('intervensi.kelola')->with('berhasil', 'Berhasil reset status intervensi');
    }

    public function suratIjin($id)
    {
      $get = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                          ->where('preson_intervensis.id', $id)
                          ->get();


      if ($get[0]->nip_atasan!="") {
        $atasan = pegawai::where('nip_sapk', $get[0]->nip_atasan)->get();
      } else {
        return redirect()->route('intervensi.index')->with('gagal', 'Data intervensi anda tidak memiliki data yang lengkap sehingga form izin tidak dapat dicetak.');
      }


      $pribadi = pegawai::find($get[0]->id);

      //month library
      $monthnow = date('m');
      $month="";
      switch ($monthnow) {
        case "01":
          $month = "Januari";
          break;
        case "02":
          $month = "Februari";
          break;
        case "03":
          $month = "Maret";
          break;
        case "04":
          $month = "April";
          break;
        case "05":
          $month = "Mei";
          break;
        case "06":
          $month = "Juni";
          break;
        case "07":
          $month = "Juli";
          break;
        case "08":
          $month = "Agustus";
          break;
        case "09":
          $month = "September";
          break;
        case "10":
          $month = "Oktober";
          break;
        case "11":
          $month = "November";
          break;
        case "12":
          $month = "Desember";
          break;
        default:
          $month = "Unrecognized Month Number";
      }
      $datenow = date('d')." $month ".date('Y');

      $tanggalijin = $get[0]->tanggal_mulai;
      $d = date_parse_from_format("Y-m-d", $tanggalijin);

      $month="";
      switch ($monthnow) {
        case "1":
          $month = "Januari";
          break;
        case "2":
          $month = "Februari";
          break;
        case "3":
          $month = "Maret";
          break;
        case "4":
          $month = "April";
          break;
        case "5":
          $month = "Mei";
          break;
        case "6":
          $month = "Juni";
          break;
        case "7":
          $month = "Juli";
          break;
        case "8":
          $month = "Agustus";
          break;
        case "9":
          $month = "September";
          break;
        case "10":
          $month = "Oktober";
          break;
        case "11":
          $month = "November";
          break;
        case "12":
          $month = "Desember";
          break;
        default:
          $month = "Unrecognized Month Number";
      }

      $tanggalijin = $d["day"]." $month ".$d["year"];

      $data = array('data' => [
        "nama_intervensi" => $get[0]->jenis_intervensi,
        "tanggal" => $datenow,
        "atasan_langsung" => $get[0]->nama_atasan,
        "nip_atasan" => $get[0]->nip_atasan,
        "jabatan_atasan" => $atasan[0]->jabatan,
        "nama_pegawai" => $get[0]->nama,
        "nip_pegawai" => $get[0]->nip_sapk,
        "jabatan_pegawai" => $pribadi->jabatan,
        "jam_ijin" => $get[0]->jam_ijin,
        "tanggal_ijin" => $tanggalijin,
        "keterangan" => $get[0]->deskripsi
      ]);

      $pdf = PDF::loadView('pdf.suratijin', $data);
      return $pdf->download('suratijin.pdf');
    }

    public function previewSuratIjin($id)
    {
      $get = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                          ->where('preson_intervensis.id', $id)
                          ->get();

      $atasan = pegawai::where('nip_sapk', $get[0]->nip_atasan)->get();

      $pribadi = pegawai::find($get[0]->id);

      //month library
      $monthnow = date('m');
      $month="";
      switch ($monthnow) {
        case "01":
          $month = "Januari";
          break;
        case "02":
          $month = "Februari";
          break;
        case "03":
          $month = "Maret";
          break;
        case "04":
          $month = "April";
          break;
        case "05":
          $month = "Mei";
          break;
        case "06":
          $month = "Juni";
          break;
        case "07":
          $month = "Juli";
          break;
        case "08":
          $month = "Agustus";
          break;
        case "09":
          $month = "September";
          break;
        case "10":
          $month = "Oktober";
          break;
        case "11":
          $month = "Novembe";
          break;
        case "12":
          $month = "Desember";
          break;
        default:
          $month = "Unrecognized Month Number";
      }
      $datenow = date('d')." $month ".date('Y');

      $tanggalijin = $get[0]->tanggal_mulai;
      $d = date_parse_from_format("Y-m-d", $tanggalijin);

      $month="";
      switch ($monthnow) {
        case "1":
          $month = "Januari";
          break;
        case "2":
          $month = "Februari";
          break;
        case "3":
          $month = "Maret";
          break;
        case "4":
          $month = "April";
          break;
        case "5":
          $month = "Mei";
          break;
        case "6":
          $month = "Juni";
          break;
        case "7":
          $month = "Juli";
          break;
        case "8":
          $month = "Agustus";
          break;
        case "9":
          $month = "September";
          break;
        case "10":
          $month = "Oktober";
          break;
        case "11":
          $month = "Novembe";
          break;
        case "12":
          $month = "Desember";
          break;
        default:
          $month = "Unrecognized Month Number";
      }

      $tanggalijin = $d["day"]." $month ".$d["year"];

      $data = [
        "nama_intervensi" => $get[0]->jenis_intervensi,
        "tanggal" => $datenow,
        "atasan_langsung" => $get[0]->nama_atasan,
        "nip_atasan" => $get[0]->nip_atasan,
        "jabatan_atasan" => $atasan[0]->jabatan,
        "nama_pegawai" => $get[0]->nama,
        "nip_pegawai" => $get[0]->nip_sapk,
        "jabatan_pegawai" => $pribadi->jabatan,
        "jam_ijin" => $get[0]->jam_ijin,
        "tanggal_ijin" => $tanggalijin,
        "keterangan" => $get[0]->deskripsi
      ];

      return view('pdf.suratijin')->with('data', $data);
    }
}
