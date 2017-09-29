@extends('layout.master')

@section('title')
  <title>Detail Laporan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <style media="screen">
    .link a {
      text-decoration-color: black;
    }
    .sesuai {
      background-color: brown;
    }
  </style>
@endsection

@section('content')

  @if(Session::has('berhasil'))
  <script>
    window.setTimeout(function() {
      $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
          $(this).remove();
      });
    }, 2000);
  </script>

  <div class="row">
    <div class="col-md-12">
      <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
        <p>{{ Session::get('berhasil') }}</p>
      </div>
    </div>
  </div>
  @endif

  @if(Session::has('gagal'))
  <script>
    window.setTimeout(function() {
      $(".alert-danger").fadeTo(500, 0).slideUp(500, function(){
          $(this).remove();
      });
    }, 2000);
  </script>

  <div class="row">
    <div class="col-md-12">
      <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-close"></i> Gagal!</h4>
        <p>{{ Session::get('gagal') }}</p>
      </div>
    </div>
  </div>
  @endif

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Jurnal TPP 2017</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th class="text-center">SKPD</th>
              <th class="text-center">Januari</th>
              <th class="text-center">Februari</th>
              <th class="text-center">Maret</th>
              <th class="text-center">April</th>
              <th class="text-center">Mei</th>
              <th class="text-center">Juni</th>
              <th class="text-center">Juli</th>
              <th class="text-center">Agustus</th>
              <th class="text-center">September</th>
              <th class="text-center">Oktober</th>
              <th class="text-center">November</th>
              <th class="text-center">Desember</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp
            <tr>
              <td colspan="2" class="text-center"><b>Total Dibayarkan</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($januari), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($februari), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($maret), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($april), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($mei), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($juni), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($juli), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($agustus), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($september), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($oktober), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($november), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($desember), 0, ',', '.') }}</b></td>
              <td bgcolor="pink"><b><u>{{ number_format(round($grandTotal), 0, ',', '.') }}</u></b></td>
            </tr>
            @foreach ($getJurnal as $jurnal)
              <tr>
                <td>{{ $no }}</td>
                <td><b>{{ $jurnal->nama }}</b></td>
                <td class="{{ ($jurnal->flag_januari == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '01-2017')) }}">{{ number_format(round($jurnal->tpp_januari), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_februari == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '02-2017')) }}">{{ number_format(round($jurnal->tpp_februari), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_maret == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '03-2017')) }}">{{ number_format(round($jurnal->tpp_maret), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_april == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '04-2017')) }}">{{ number_format(round($jurnal->tpp_april), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_mei == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '05-2017')) }}">{{ number_format(round($jurnal->tpp_mei), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_juni == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '06-2017')) }}">{{ number_format(round($jurnal->tpp_juni), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_juli == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '07-2017')) }}">{{ number_format(round($jurnal->tpp_juli), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_agustus == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '08-2017')) }}">{{ number_format(round($jurnal->tpp_agustus), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_september == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '09-2017')) }}">{{ number_format(round($jurnal->tpp_september), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_oktober == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '10-2017')) }}">{{ number_format(round($jurnal->tpp_oktober), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_november == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '11-2017')) }}">{{ number_format(round($jurnal->tpp_november), 0, ',', '.') }}</a></td>
                <td class="{{ ($jurnal->flag_desember == 0) ? 'sesuai' : '' }}"><a href="{{ route('jurnal.getJurnal', array('skpd_id' => $jurnal->id, 'bulan' => '12-2017')) }}">{{ number_format(round($jurnal->tpp_desember), 0, ',', '.') }}</a></td>
                @php
                  $grand = $jurnal->tpp_januari+$jurnal->tpp_februari+$jurnal->tpp_maret+$jurnal->tpp_april+$jurnal->tpp_mei+$jurnal->tpp_juni+$jurnal->tpp_juli+$jurnal->tpp_agustus+$jurnal->tpp_september+$jurnal->tpp_oktober+$jurnal->tpp_november+$jurnal->tpp_desember;
                @endphp
                <td bgcolor="yellow">{{ number_format(round($grand), 0, ',', '.') }}</td>
              </tr>
              @php
              $no++
              @endphp
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
