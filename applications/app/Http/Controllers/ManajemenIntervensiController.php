<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManajemenIntervensi;

class ManajemenIntervensiController extends Controller
{
    public function index()
    {
      $get = ManajemenIntervensi::all();
      return view('pages/manajemen-intervensi/index')->with('getintervensi', $get);
    }
}
