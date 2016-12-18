<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Intervensi;
use App\Models\Pegawai;
use App\Models\Users;
use App\Models\Skpd;

use Validator;
use Auth;
use Image;

class IntervensiController extends Controller
{


    public function index()
    {

      $intervensi = intervensi::where('pegawai_id', Auth::user()->pegawai_id)->get();

      return view('pages.intervensi.index', compact('intervensi'));
    }

    public function store(Request $request)
    {
      $message = [
        'jenis_intervensi.required' => 'Wajib di isi',
        'tanggal_mulai.required' => 'Wajib di isi',
        'tanggal_akhir.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'berkas'  => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'jenis_intervensi' => 'required',
        'tanggal_mulai' => 'required',
        'tanggal_akhir' => 'required',
        'keterangan' => 'required',
        'berkas'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('intervensi.index')->withErrors($validator)->withInput();
      }

      $file = $request->file('berkas');

      if($file != null)
      {
        $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_mulai.'-'.$request->jenis_intervensi.'.' . $file->getClientOriginalExtension();
        Image::make($file)->resize(443,350)->save('documents/'. $photo_name);
      }else{
        $photo_name;
      }

      $set = new intervensi;
      $set->pegawai_id = Auth::user()->pegawai_id;
      $set->jenis_intervensi = $request->jenis_intervensi;
      $set->tanggal_mulai = $request->tanggal_mulai;
      $set->tanggal_akhir = $request->tanggal_akhir;
      $set->deskripsi = $request->keterangan;
      $set->berkas = $photo_name;
      $set->flag_status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('intervensi.index')->with('berhasil', 'Berhasil Menambahkan Intervensi');
    }

    public function bind($id)
    {
      $find = intervensi::find($id);

      return $find;
    }

    public function edit(Request $request)
    {
      dd($request);
      $message = [
        'jenis_intervensi_edit.required' => 'Wajib di isi',
        'tanggal_mulai_edit.required' => 'Wajib di isi',
        'tanggal_akhir_edit.required' => 'Wajib di isi',
        'keterangan_edit.required' => 'Wajib di isi',
        'berkas'  => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'jenis_intervensi_edit' => 'required',
        'tanggal_mulai_edit' => 'required',
        'tanggal_akhir_edit' => 'required',
        'keterangan_edit' => 'required',
        'berkas'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('intervensi.index')->withErrors($validator)->withInput();
      }

      $file = $request->file('berkas');

      if($file != null)
      {
        $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_mulai.'-'.$request->jenis_intervensi.'.' . $file->getClientOriginalExtension();
        Image::make($file)->save('documents/'. $photo_name);
      }else{
        $photo_name;
      }

      $set = new intervensi;
      $set->pegawai_id = Auth::user()->pegawai_id;
      $set->jenis_intervensi = $request->jenis_intervensi;
      $set->tanggal_mulai = $request->tanggal_mulai;
      $set->tanggal_akhir = $request->tanggal_akhir;
      $set->deskripsi = $request->keterangan;
      $set->berkas = $photo_name;
      $set->flag_status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('intervensi.index')->with('berhasil', 'Berhasil Mengubah Intervensi');
    }

    public function kelola()
    {
      if(session('status') === 'admin')
      {
        $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                              ->join('preson_users', 'preson_users.skpd_id', '=', 'preson_pegawais.skpd_id')
                              ->where('preson_users.pegawai_id', Auth::user()->pegawai_id)
                              ->select('preson_intervensis.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.nip_sapk')
                              ->orderBy('tanggal_mulai', 'desc')
                              ->get();

        $pegawai = pegawai::select('id', 'nama')->where('skpd_id', Auth::user()->skpd_id)->get();
      }
      elseif(session('status') === 'administrator')
      {
        $getSKPD = skpd::get();

        $pegawai = pegawai::select('id', 'nama')->get();
      }

      return view('pages.intervensi.kelola', compact('getSKPD', 'pegawai'));
    }

    public function kelolaAksi($id)
    {
      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                            ->select('preson_pegawais.nama as nama_pegawai', 'preson_intervensis.*')
                            ->where('preson_intervensis.id', $id)->first();

      return view('pages.intervensi.aksi', compact('intervensi'));
    }

    public function kelolaApprove($id)
    {
      $approve = intervensi::find($id);
      $approve->flag_status = 1;
      $approve->actor = Auth::user()->pegawai_id;
      $approve->update();

      return redirect()->route('intervensi.kelola')->with('berhasil', 'Berhasil Approve Intervensi');
    }

    public function kelolaPost(Request $request)
    {
      $message = [
        'pegawai_id.required' => 'Wajib di isi',
        'jenis_intervensi.required' => 'Wajib di isi',
        'tanggal_mulai.required' => 'Wajib di isi',
        'tanggal_akhir.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'berkas'  => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'pegawai_id' => 'required',
        'jenis_intervensi' => 'required',
        'tanggal_mulai' => 'required',
        'tanggal_akhir' => 'required',
        'keterangan' => 'required',
        'berkas'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('intervensi.kelola')->withErrors($validator)->withInput();
      }

      $file = $request->file('berkas');

      if($file != null)
      {
        $photo_name = Auth::user()->nip_sapk.'-'.$request->tanggal_mulai.'-'.$request->jenis_intervensi.'.' . $file->getClientOriginalExtension();
        $file->move('documents/', $photo_name );
      }else{
        $photo_name = '';
      }

      $set = new intervensi;
      $set->pegawai_id = $request->pegawai_id;
      $set->jenis_intervensi = $request->jenis_intervensi;
      $set->tanggal_mulai = $request->tanggal_mulai;
      $set->tanggal_akhir = $request->tanggal_akhir;
      $set->deskripsi = $request->keterangan;
      $set->berkas = $photo_name;
      $set->flag_status = 0;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('intervensi.kelola')->with('berhasil', 'Berhasil Menambahkan Intervensi');
    }

    public function skpd($id)
    {
      $intervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                            ->join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->select('preson_intervensis.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.nip_sapk')
                            ->where('preson_skpd.id', $id)
                            ->orderBy('tanggal_mulai', 'desc')
                            ->get();

      $pegawai = pegawai::select('id', 'nama')->where('skpd_id', Auth::user()->skpd_id)->get();

      return view('pages.intervensi.detailSKPD', compact('intervensi', 'pegawai'));
    }
}
