<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Struktural;
use App\Models\User;

use Validator;
use Auth;
use DB;
use Hash;

class PegawaiController extends Controller
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
      $pegawai = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                        ->join('preson_golongans', 'preson_golongans.id', '=', 'preson_pegawais.golongan_id')
                        ->join('preson_jabatans', 'preson_jabatans.id', '=', 'preson_pegawais.jabatan_id')
                        ->join('preson_strukturals', 'preson_strukturals.id', '=', 'preson_pegawais.struktural_id')
                        ->select('preson_pegawais.id', 'preson_pegawais.fid', 'preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd', 'preson_golongans.nama as nama_golongan', 'preson_jabatans.nama as nama_jabatan', 'preson_strukturals.nama as nama_struktural')
                        ->get();

      return view('pages.pegawai.index', compact('pegawai'));
    }

    public function create()
    {
      $skpd = skpd::select('id', 'nama')->get();
      $golongan = golongan::select('id', 'nama')->get();
      $struktural = struktural::select('id', 'nama')->get();
      $jabatan = jabatan::select('id', 'nama')->get();

      return view('pages.pegawai.create', compact('skpd', 'golongan', 'struktural', 'jabatan'));
    }

    public function store(Request $request)
    {
      $message = [
        'nama_pegawai.required' => 'Wajib di isi',
        'nip_sapk.required' => 'Wajib di isi',
        'fid.required' => 'Wajib di isi',
        'fid.unique'  => 'Finger ID Sudah diPakai',
        'skpd_id.required' => 'Wajib di isi',
        'golongan_id.required' => 'Wajib di isi',
        'jabatan_id.required' => 'Wajib di isi',
        'struktural_id.required' => 'Wajib di isi',
        'tanggal_lahir.required' => 'Wajib di isi',
        'tempat_lahir.required' => 'Wajib di isi',
        'pendidikan_terakhir.required' => 'Wajib di isi',
        'alamat.required' => 'Wajib di isi',
        'tpp_dibayarkan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama_pegawai' => 'required',
        'nip_sapk' => 'required',
        'fid' => 'required|unique:preson_pegawais',
        'skpd_id' => 'required',
        'golongan_id' => 'required',
        'jabatan_id' => 'required',
        'struktural_id' => 'required',
        'tanggal_lahir' => 'required',
        'tempat_lahir' => 'required',
        'pendidikan_terakhir' => 'required',
        'alamat' => 'required',
        'tpp_dibayarkan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pegawai.create')->withErrors($validator)->withInput();
      }
      
      $set = new pegawai;
      $set->nama = $request->nama_pegawai;
      $set->nip_sapk = $request->nip_sapk;
      $set->nip_lm  = $request->nip_lm;
      $set->fid   = $request->fid;
      $set->skpd_id = $request->skpd_id;
      $set->golongan_id = $request->golongan_id;
      $set->jabatan_id = $request->jabatan_id;
      $set->struktural_id = $request->struktural_id;
      $set->tanggal_lahir = $request->tanggal_lahir;
      $set->tempat_lahir = $request->tempat_lahir;
      $set->pendidikan_terakhir = $request->pendidikan_terakhir;
      $set->alamat  = $request->alamat;
      $set->tpp_dibayarkan  = $request->tpp_dibayarkan;
      $set->actor = Auth::user()->id;
      $set->status = 1;
      $set->save();

      $pegawai_id = pegawai::select('id')->where('nip_sapk', $request->nip_sapk)->first();

      $new = new user;
      $new->nip_sapk = $request->nip_sapk;
      $new->nama = $request->nama_pegawai;
      $new->password = Hash::make(12345678);
      $new->email = strtolower(str_replace(' ','', $request->nama_pegawai)).'@tangerangkab.go.id';
      $new->role_id = 3;
      $new->skpd_id = $request->skpd_id;
      $new->pegawai_id = $pegawai_id->id;
      $new->save();


      return redirect()->route('pegawai.index')->with('berhasil', 'Pegawai Baru Berhasil di Tambahkan');
    }

    public function edit($id)
    {
      $pegawai = pegawai::find($id);

      $skpd = skpd::select('id', 'nama')->get();
      $golongan = golongan::select('id', 'nama')->get();
      $struktural = struktural::select('id', 'nama')->get();
      $jabatan = jabatan::select('id', 'nama')->get();


      return view('pages.pegawai.edit', compact('pegawai', 'skpd', 'golongan', 'struktural', 'jabatan'));
    }

    public function editStore(Request $request)
    {
      $message = [
        'nama_pegawai.required' => 'Wajib di isi',
        'nip_sapk.required' => 'Wajib di isi',
        'fid.required' => 'Wajib di isi',
        'fid.unique'  => 'Finger ID Sudah diPakai',
        'skpd_id.required' => 'Wajib di isi',
        'golongan_id.required' => 'Wajib di isi',
        'jabatan_id.required' => 'Wajib di isi',
        'struktural_id.required' => 'Wajib di isi',
        'tanggal_lahir.required' => 'Wajib di isi',
        'tempat_lahir.required' => 'Wajib di isi',
        'pendidikan_terakhir.required' => 'Wajib di isi',
        'alamat.required' => 'Wajib di isi',
        'tpp_dibayarkan.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama_pegawai' => 'required',
        'nip_sapk' => 'required',
        'fid' => 'required|unique:preson_pegawais,id',
        'skpd_id' => 'required',
        'golongan_id' => 'required',
        'jabatan_id' => 'required',
        'struktural_id' => 'required',
        'tanggal_lahir' => 'required',
        'tempat_lahir' => 'required',
        'pendidikan_terakhir' => 'required',
        'alamat' => 'required',
        'tpp_dibayarkan' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('pegawai.edit', ['id' => $request->pegawai_id])->withErrors($validator)->withInput();
      }

      $set = pegawai::find($request->pegawai_id);
      $set->nama = $request->nama_pegawai;
      $set->nip_sapk = $request->nip_sapk;
      $set->nip_lm  = $request->nip_lm;
      $set->fid   = $request->fid;
      $set->skpd_id = $request->skpd_id;
      $set->golongan_id = $request->golongan_id;
      $set->jabatan_id = $request->jabatan_id;
      $set->struktural_id = $request->struktural_id;
      $set->tanggal_lahir = $request->tanggal_lahir;
      $set->tempat_lahir = $request->tempat_lahir;
      $set->pendidikan_terakhir = $request->pendidikan_terakhir;
      $set->alamat  = $request->alamat;
      $set->tpp_dibayarkan  = $request->tpp_dibayarkan;
      $set->actor = Auth::user()->id;
      $set->status = 1;
      $set->update();

      return redirect()->route('pegawai.index')->with('berhasil', 'Behasil Mengubah Data Pegawai');
    }
}
