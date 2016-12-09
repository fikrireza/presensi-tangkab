@extends('layout.master')

@section('title')
  <title>Presensi Online</title>
@endsection

@section('content')
<div class="col-md-12">
  @if(Session::has('firsttimelogin'))
    <div class="alert alert-success panjang">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Selamat Datang!</h4>
      <p>{{ Session::get('firsttimelogin') }}</p>
    </div>
  @endif
</div>

<div class="row">
  <div class="col-md-12">
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
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-purple">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. 12.345.678,-</sup></h3>
          <p>Jumlah TPP</p>
        </div>
        {{-- <a class="small-box-footer">
          <i>Jumlah TPP</i>
        </a> --}}
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-maroon">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. 12.345.678,-</sup></h3>
          <p>Yang Dibayarkan</p>
        </div>
        {{-- <div class="icon">
          <i class="ion ion-person-stalker"></i>
        </div> --}}
        {{-- <a href="" class="small-box-footer">Lihat Data Selengkapnya <i class="fa fa-arrow-circle-right"></i></a> --}}
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
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @if ($absensi->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($absensi as $key)
            @if($key->Jam_Log > '15:00:00')
            <tr class="table-active">
            @endif
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
              <td><?php
                  $tanggal = explode('/', $key->Tanggal_Log);
                  $tanggal = $tanggal[0].'/'.$tanggal[1].'/'.$tanggal[2];
              ?>
                {{ date('l', strtotime($tanggal)) }}</td>
              <td>{{ $key->Tanggal_Log }}</td>
              <td>{{ $key->Jam_Log }}</td>
            </tr>
            <?php $no++; ?>
            @endforeach
            @endif
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
