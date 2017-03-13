<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pegawai;
use App\Models\Skpd;
use App\Models\Shift;
use App\Models\JamKerja;
use App\Models\JadwalKerja;
use App\Models\JamKerjaGroup;

use Validator;
use Auth;
use DB;

class ShiftController extends Controller
{

    public function index()
    {
      $getSkpd = skpd::where('flag_shift', 0)->get();

      $skpdShift = skpd::where('flag_shift', 1)->get();

      return view('pages.shift.index', compact('getSkpd', 'skpdShift'));
    }

    public function skpdShift(Request $request)
    {

      $set = skpd::find($request->skpd_id);
      $set->flag_shift = 1;
      $set->update();

      return redirect()->route('shift.index')->with('berhasil', 'SKPD Terpilih Menjadi Shift');
    }

    public function jadwalShift()
    {
      

      return view('pages.shift.jadwalShift');
    }
}
