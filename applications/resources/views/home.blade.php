@extends('layout.master')

@section('title')
  <title>Presensi Online</title>
@endsection

@section('content')
<div class="col-md-12">
  @if(Session::has('berhasil'))
    <div class="alert alert-success panjang">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Selamat Datang!</h4>
      <p>{{ Session::get('berhasil') }}</p>
    </div>
  @endif
</div>

<div class="row">
  <div class="col-md-12">
    @if (session('status') === 'administrator' || session('status') === 'admin')
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-teal">
        <div class="inner">
          <h3>66</h3>
          <p>Jumlah Pegawai</p>
        </div>
        {{-- <a href="" class="small-box-footer">Lihat Data Selengkapnya <i class="fa fa-arrow-circle-right"></i></a> --}}
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-yellow">
        <div class="inner">
          <h3>65<sup style="font-size: 20px"></sup></h3>
          <p>Jumlah Hadir</p>
        </div>
        {{-- <a class="small-box-footer">
          <i>Jumlah Hadir</i>
        </a> --}}
      </div>
    </div>
    @endif
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-purple">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. {{ number_format($tpp->tpp_dibayarkan,0,',','.') }},-</sup></h3>
          <p>Jumlah TPP</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-maroon">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. 12.345.678,-</sup></h3>
          <p>Yang Dibayarkan</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body">
        <table id="table_absen" class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Hari</th>
              <th>Tanggal</th>
              <th>Jam Datang</th>
              <th>Jam Pulang</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($absensi as $hari)
            @foreach ($hari as $key)
            <tr>
              <td>{{ $no }}</td>
              <?php
                $day = explode('/', $key->hari_ini);
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
              <td>{{ $key->hari_ini }}</td>
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

@section('script')
<script>
  $(function(){
    $("#table_absen").DataTable();
  });
</script>
@endsection
