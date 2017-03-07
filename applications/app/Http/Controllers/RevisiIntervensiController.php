<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\User;
use App\Models\ManajemenIntervensi;

use Validator;
use Auth;
use DB;
use Hash;

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
      $getrevisiintervensi = [];
      return view('pages.revisiintervensi.index', compact('getrevisiintervensi'));
    }


    public function create()
    {
      $getskpd = Skpd::select('*')->get();
      $getcaripegawai = null;
      // dd($getcaripegawai);
      return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai'));
    }


    public function caripegawai(Request $req)
    {
      // dd($req);
      $message = [
        'skpd.required' => 'Wajib di isi'
      ];

      $validator = Validator::make($req->all(), [
        'skpd' => 'required'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.create')->withErrors($validator)->withInput();
      }

      $getskpd = Skpd::select('*')->get();
      $getcaripegawai = Pegawai::select('*')->where('skpd_id', $id)->paginate(5);
      // dd($getcaripegawai);

        return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai'));
    }

    public function createStore(Request $request)
    {
      // dd($request);
       $message = [
        'skpd.required' => 'Wajib di isi',
        'tanggal_revisi.required' => 'Wajib di isi',
        'keterangan.required' => 'Wajib di isi',
        'upload_revisi' => 'Hanya .jpg, .png, .pdf'
      ];

      $validator = Validator::make($request->all(), [
        'skpd' => 'required',
        'tanggal_revisi' => 'required',
        'keterangan' => 'required',
        'upload_revisi'  => 'mimes:jpeg,png,pdf,jpg'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('revisiintervensi.create')->withErrors($validator)->withInput();
      }

    }

    
}
