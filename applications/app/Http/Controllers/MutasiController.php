<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Mutasi;

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
      
      return view('pages.pegawai.index');
    }

    public function create($id)
    {
      
      // dd($id);
      return view('mutasi/create');
    }


    public function createStore(Request $request)
    {
      
      return redirect()->route('pages.pegawai.index');
    }
}
