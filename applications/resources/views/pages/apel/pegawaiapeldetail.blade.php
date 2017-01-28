@extends('layout.master')

@section('title')
  <title>Detail Absensi Apel</title>
@endsection

@section('breadcrumb')
  <h1>Detail Absensi Apel</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Detail Absensi Apel</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Detail Absensi Apel {{$getDetail[0]->nama_skpd}} - {{ $tanggalApel }}</h3>
        <a href="{{ route('pegawaiapel.detailCetak', ['download' => 'pdf', 'skpd' => $skpd, 'tanggalApel' => $tanggalApelnya]) }}" class="btn bg-green pull-right">Cetak</a>
      </div>
      <div class="box-body">
        <table id="table_user" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Struktural</th>
              <th>Jam Absen</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no=1;
            @endphp
            @foreach ($getDetail as $key)
              <tr>
                <td>{{$no}}</td>
                <td>{{ $key->nip_sapk }}</td>
                <td>{{ $key->pegawai }}</td>
                <td>{{ $key->struktural }}</td>
                <td>{{ $key->Jam_Log }}</td>
              </tr>
              @php
                $no++;
              @endphp
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
  $(function () {
    $("#table_user").DataTable();
  });
</script>
@endsection
