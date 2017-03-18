@extends('layout.master')

@section('title')
  <title>Jadwal Shift | Presensi Online</title>
@endsection

@section('breadcrumb')
  <h1>Jadwal Shift</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Jadwal Shift</li>
  </ol>
@endsection

@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Ubah Jadwal - {{ $getShift->nama_pegawai }} | {{ $getShift->tanggal}} - {{ date('l', strtotime($getShift->tanggal)) }}</h3>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('shift.jadwalEdit') }}" method="post">
      {{ csrf_field() }}
      <div class="box-body table-responsive">
        <table class="table table-striped">
          <tbody>
            <tr>
              <td>NIP</td>
              <td>:</td>
              <td><input type="hidden" name="id" value="{{ $getShift->id }}"><input type="text" name="nip_sapk" class="form-control" value="{{ $getShift->nip_sapk }}" readonly=""></td>
            </tr>
            <tr>
              <td>Nama</td>
              <td>:</td>
              <td><input type="text" name="nama_pegawai" class="form-control" value="{{ $getShift->nama_pegawai }}" readonly=""></td>
            </tr>
            <tr>
              <td>Tanggal</td>
              <td>:</td>
              <td><input type="text" class="form-control" name="tanggal" value="{{ $getShift->tanggal }}" placeholder="yyyy-mm-dd" readonly=""></td>
            </tr>
            <tr>
              <td>Jam Kerja</td>
              <td>:</td>
              <td><select class="form-control" name="jam_kerja_id">
                @foreach ($getJamKerja as $key)
                @if ($getShift->jam_kerja_id == $key->id)
                <option value="{{ $key->id }}" selected="">{{ $key->nama_jam_kerja}} - {{ $key->jam_masuk}} s/d {{ $key->jam_pulang}}</option>
                @else
                <option value="{{ $key->id }}">{{ $key->nama_jam_kerja}} - {{ $key->jam_masuk}} s/d {{ $key->jam_pulang}}</option>
                @endif
                @endforeach
                <option value=""></option>
              </select></td>
            </tr>
            <tr>
              <td>Keterangan</td>
              <td>:</td>
              <td><input type="text" name="keterangan" class="form-control" value=""></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="box-footer">
        <div class="box-footer">
          <button type="submit" class="btn btn-block bg-purple">Ubah</button>
        </div>
      </div>
      </form>
    </div>
  </div>
</div>


@endsection

@section('script')

@endsection
