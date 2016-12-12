@extends('layout.master')

@section('title')
  <title>Absensi</title>
@endsection

@section('breadcrumb')
  <h1>Absensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Absensi</li>
  </ol>
@endsection

@section('content')
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 2000);
</script>

@if(Session::has('berhasil'))
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


<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Hari</th>
              <th>Tanggal</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($absensi as $hari)
            @foreach ($hari as $key)
            <tr class="table-active">
              <td>{{ $no }}</td>
              <?php
                $day = explode('/', $key->Tanggal_Log);
                $day = $day[1]."/".$day[0]."/".$day[2];
                $day = date('D', strtotime($day));
                $dayList = array(
                	'Sun' => 'Minggu',
                	'Mon' => 'Senin',
                	'Tue' => 'Selasa',
                	'Wed' => 'Rabu',
                	'Thu' => 'Kamis',
                	'Fri' => 'Jum&#039;at',
                	'Sat' => 'Sabtu'
                );
              ?>
             <td>{{ $key->nama_pegawai }}</td>
             <td>{{ $dayList[$day] }}</td>
             <td>@if($key->Tanggal_Log != null) {{ $key->Tanggal_Log }} @else - @endif</td>
             <td>@if($key->Jam_Datang != null) {{ $key->Jam_Datang }} @else - @endif</td>
             <td>@if($key->Jam_Pulang != null) {{ $key->Jam_Pulang }} @else - @endif</td>
            </tr>
            <?php $no++; ?>
            @endforeach
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
