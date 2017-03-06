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
      $getcaripegawai = [];
      // dd($getskpd);
      return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai'));
    }


    public function caripegawai($id)
    {
      $getskpd = Skpd::select('*')->get();
      $getcaripegawai = Pegawai::select('*')->where('skpd_id', $id)->paginate(5);
      // dd($getcaripegawai);

        return view('pages.revisiintervensi.create', compact('getskpd','getcaripegawai'));
    }

    
}
