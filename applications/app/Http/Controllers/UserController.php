<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Role;
use App\Models\Intervensi;

use Auth;
use Validator;
use DB;
use Hash;

class UserController extends Controller
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
      if(session('status') == 'administrator' || session('status') == 'superuser')
      {
        $getpegawai = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                          ->join('preson_users', 'preson_users.pegawai_id', '=', 'preson_pegawais.id')
                          ->select('preson_pegawais.id as pegawai_id','preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd')
                          ->where('preson_users.role_id', '=', 3)
                          ->get();
      }
      elseif(session('status') == 'admin')
      {
        $getpegawai = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                          ->join('preson_users', 'preson_users.pegawai_id', '=', 'preson_pegawais.id')
                          ->select('preson_pegawais.id as pegawai_id','preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd')
                          ->where('preson_users.role_id', '=', 3)
                          ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                          ->get();
      }

      if(session('status') == 'administrator' || session('status') == 'superuser')
      {
        $getuser    = user::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_users.pegawai_id')
                          ->join('preson_roles', 'preson_roles.id', '=', 'preson_users.role_id')
                          ->join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                          ->select('preson_pegawais.id as pegawai_id','preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd', 'preson_roles.title')
                          ->where('preson_users.role_id', '!=', 3)
                          ->orderby('preson_skpd.id')
                          ->orderby('preson_roles.id')
                          ->get();
      }
      elseif(session('status') == 'admin')
      {
        $getuser    = user::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_users.pegawai_id')
                          ->join('preson_roles', 'preson_roles.id', '=', 'preson_users.role_id')
                          ->join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                          ->select('preson_pegawais.id as pegawai_id','preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd', 'preson_roles.title')
                          ->where('preson_users.role_id', '=', 2)
                          ->orderby('preson_skpd.id')
                          ->orderby('preson_roles.id')
                          ->get();
      }
      return view('pages.user.index', compact('getpegawai', 'getuser', 'getunreadintervensi'));
    }

    public function store(Request $request)
    {
      $message = [
        'role_id.request' => 'Wajib di isi',
        'role_id.not_in' => 'Wajib di isi',
        'pegawai_id.request' => 'Wajib di isi',
        'pegawai_id.not_in' => 'Wajib di isi',
      ];

      $validator = validator::make($request->all(), [
        'role_id' => 'required|not_in:-- Pilih --',
        'pegawai_id'  => 'required|not_in:-- Pilih --',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('user.index')->withErrors($validator)->withInput();
      }

      $update = user::where('pegawai_id', $request->pegawai_id)->first();
      $update->role_id  = $request->role_id;
      $update->save();

      return redirect()->route('user.index')->with('berhasil', 'Berhasil Menambahkan Akun');

    }

    public function delete($id)
    {
      $delete = user::where('pegawai_id', $id)->first();
      $delete->role_id = 3;
      $delete->save();

      return redirect()->route('user.index')->with('berhasil', 'Berhasil Menghapus Akun');

    }

    public function reset()
    {
      if(session('status') == 'administrator' || session('status') == 'superuser')
      {
        $getuser  = user::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_users.pegawai_id')
                          ->join('preson_roles', 'preson_roles.id', '=', 'preson_users.role_id')
                          ->join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                          ->select('preson_pegawais.id as pegawai_id', 'preson_pegawais.nip_sapk', 'preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd', 'preson_roles.title')
                          ->orderby('preson_skpd.id')
                          ->orderby('preson_roles.id')
                          ->get();
      }
      else if(session('status') == 'admin')
      {
        $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                           ->where('preson_intervensis.flag_view', 0)
                                           ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                           ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                           ->count();

        $getuser  = user::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_users.pegawai_id')
                          ->join('preson_roles', 'preson_roles.id', '=', 'preson_users.role_id')
                          ->join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                          ->select('preson_pegawais.id as pegawai_id', 'preson_pegawais.nip_sapk', 'preson_pegawais.nama as nama_pegawai', 'preson_skpd.nama as nama_skpd', 'preson_roles.title')
                          ->where('preson_skpd.id', Auth::user()->skpd_id)
                          ->orderby('preson_skpd.id')
                          ->orderby('preson_roles.id')
                          ->get();
      }

      return view('pages.user.reset', compact('getuser', 'getunreadintervensi'));
    }

    public function resetPassword($id)
    {
      $reset = user::where('pegawai_id', $id)->first();
      $reset->password = Hash::make(12345678);
      $reset->save();

      return redirect()->route('user.reset')->with('berhasil', 'Berhasil Me Reset Password');
    }

    public function firstLogin()
    {
      return view('pages.user.firstLogin');
    }

    public function ubahPassword(Request $request)
    {
      $get = User::where('pegawai_id', $request->pegawai_id)->first();

      $messages = [
        'oldpass.required' => "Mohon isi password lama anda.",
        'newpass.required' => "Mohon isi password baru anda.",
        'newpass.confirmed' => "Mohon isi konfirmasi password baru anda dengan benar.",
        'newpass_confirmation.required' => "Mohon isi konfirmasi password baru anda.",
      ];

      $validator = Validator::make($request->all(), [
        'oldpass' => 'required',
        'newpass' => 'required|confirmed',
        'newpass_confirmation' => 'required'
      ], $messages);

      if ($validator->fails()) {
        return redirect()->route('firstLogin')->withErrors($validator)->withInput();
      }

      if(Hash::check($request->oldpass, $get->password))
      {
        $get->password = Hash::make($request->newpass);
        $get->save();

        return redirect()->route('home')->with('berhasil', "Berhasil mengganti password.");
      }
      else {
        return redirect()->route('firstLogin')->with('erroroldpass', 'Mohon masukkan password lama anda dengan benar.');
      }
    }

    public function profil()
    {
      $getunreadintervensi = intervensi::join('preson_pegawais', 'preson_pegawais.id', '=', 'preson_intervensis.pegawai_id')
                                         ->where('preson_intervensis.flag_view', 0)
                                         ->where('preson_pegawais.skpd_id', Auth::user()->skpd_id)
                                         ->where('preson_intervensis.pegawai_id', '!=', Auth::user()->pegawai_id)
                                         ->count();
      return view('pages.user.profil')->with('getunreadintervensi', $getunreadintervensi);
    }
}
