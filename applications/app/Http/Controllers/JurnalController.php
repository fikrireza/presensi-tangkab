<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skpd;
use App\Models\Jurnal;

use Auth;
use DB;

class JurnalController extends Controller
{

  public function index()
  {
    $getJurnal = DB::select("SELECT skpd.id, skpd.nama, tpp_januari, tpp_februari, tpp_maret, tpp_april, tpp_mei, tpp_juni, tpp_juli, tpp_agustus, tpp_september, tpp_oktober, tpp_november, tpp_desember
  	FROM (select id, nama from preson_skpd where preson_skpd.status = 1 AND preson_skpd.flag_shift = 0) as skpd

  	LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_januari, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '01'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Januari
  	ON skpd.id = Januari.id
  	LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_februari, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '02'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Februari
  	ON skpd.id = Februari.id
  	LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_maret, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '03'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Maret
  	ON skpd.id = Maret.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_april, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '04'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as April
  	ON skpd.id = April.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_mei, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '05'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Mei
  	ON skpd.id = Mei.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_juni, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '06'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Juni
  	ON skpd.id = Juni.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_juli, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '07'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Juli
  	ON skpd.id = Juli.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_agustus, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '08'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Agustus
  	ON skpd.id = Agustus.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_september, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '09'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as September
  	ON skpd.id = September.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_oktober, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '10'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Oktober
  	ON skpd.id = Oktober.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_november, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '11'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as November
  	ON skpd.id = November.id
    LEFT OUTER JOIN (select preson_jurnal.jumlah_tpp as tpp_desember, preson_skpd.id as id from preson_jurnal, preson_skpd
  										where bulan = '12'
  										and tahun = '2017'
  										and preson_skpd.id = preson_jurnal.skpd_id) as Desember
  	ON skpd.id = Desember.id");

    $januari=0;$februari=0;$maret=0;$april=0;$mei=0;$juni=0;$juli=0;$agustus=0;$september=0; $oktober=0;$november=0;$desember=0;
    foreach ($getJurnal as $key) {
      $januari += $key->tpp_januari;
      $februari += $key->tpp_februari;
      $maret += $key->tpp_maret;
      $april += $key->tpp_april;
      $mei += $key->tpp_mei;
      $juni += $key->tpp_juni;
      $juli += $key->tpp_juli;
      $agustus += $key->tpp_agustus;
      $september += $key->tpp_september;
      $oktober += $key->tpp_oktober;
      $november += $key->tpp_november;
      $desember += $key->tpp_desember;
    }

    $grandTotal = $januari+$februari+$maret+$april+$mei+$juni+$juli+$agustus+$september+$oktober+$november+$desember;

    return view('pages.jurnal.index', compact('getJurnal', 'januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember', 'grandTotal'));
  }

}
