@extends('layout.master')

@section('title')
  <title>Ubah Tambah Jam Kerja</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Ubah Tambah Jam Kerja</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('jadwal-kerja') }}">Jadwal Jam Kerja</a></li>
    <li><a href="{{ route('jadwal-kerja.group') }}">Group Jam Kerja</a></li>
    <li><a href="{{ route('jadwal-kerja.jam') }}">Jam Kerja</a></li>
    <li class="active">Ubah Jam Kerja</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Ubah Jam Kerja</h3>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('jadwal-kerja.editjam') }}" method="update">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('nama_jam_kerja') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Nama Jam Kerja</label>
            <div class="col-sm-8">
              <input type="hidden" name="id" value="{{ $getJamKerja->id }}">
              <input type="text" name="nama_jam_kerja" class="form-control" value="{{ $getJamKerja->nama_jam_kerja }}" placeholder="@if($errors->has('nama_jam_kerja'))
                {{ $errors->first('nama_jam_kerja')}}@endif Nama" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('jam_masuk') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Jam Masuk</label>
            <div class="col-sm-8">
              <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
              <input type="text" name="jam_masuk" class="form-control" value="{{ $getJamKerja->jam_masuk }}" placeholder="@if($errors->has('jam_masuk'))
                {{ $errors->first('jam_masuk')}} @endif 00:00:00" required="">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jam_masuk_awal') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Jam Masuk Awal</label>
            <div class="col-sm-8">
              <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
              <input type="text" name="jam_masuk_awal" class="form-control" value="{{ $getJamKerja->jam_masuk_awal }}" placeholder="@if($errors->has('jam_masuk_awal'))
                {{ $errors->first('jam_masuk_awal')}} @endif 00:00:00" required="">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jam_masuk_akhir') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Jam Masuk Akhir</label>
            <div class="col-sm-8">
              <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
              <input type="text" name="jam_masuk_akhir" class="form-control" value="{{ $getJamKerja->jam_masuk_akhir }}" placeholder="@if($errors->has('jam_masuk_akhir'))
                {{ $errors->first('jam_masuk_akhir')}} @endif 00:00:00" required="">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jam_pulang') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Jam Pulang</label>
            <div class="col-sm-8">
              <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
              <input type="text" name="jam_pulang" class="form-control" value="{{ $getJamKerja->jam_pulang }}" placeholder="@if($errors->has('jam_pulang'))
                {{ $errors->first('jam_pulang')}} @endif 00:00:00" required="">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jam_pulang_awal') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Jam Pulang Awal</label>
            <div class="col-sm-8">
              <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
              <input type="text" name="jam_pulang_awal" class="form-control" value="{{ $getJamKerja->jam_pulang_awal }}" placeholder="@if($errors->has('jam_pulang_awal'))
                {{ $errors->first('jam_pulang_awal')}} @endif 00:00:00" required="">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jam_pulang_akhir') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Jam Pulang Akhir</label>
            <div class="col-sm-8">
              <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
              </div>
              <input type="text" name="jam_pulang_akhir" class="form-control" value="{{ $getJamKerja->jam_pulang_akhir }}" placeholder="@if($errors->has('jam_pulang_akhir'))
                {{ $errors->first('jam_pulang_akhir')}} @endif 00:00:00" required="">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('toleransi_terlambat') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Toleransi Terlambat</label>
            <div class="col-sm-8">
              <input type="text" name="toleransi_terlambat" class="form-control" value="{{ $getJamKerja->toleransi_terlambat }}" placeholder="@if($errors->has('toleransi_terlambat'))
                {{ $errors->first('toleransi_terlambat')}} @endif 00 Menit" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('toleransi_pulcep') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Toleransi Pulang Cepat</label>
            <div class="col-sm-8">
              <input type="text" name="toleransi_pulcep" class="form-control" value="{{ $getJamKerja->toleransi_pulcep }}" placeholder="@if($errors->has('toleransi_pulcep'))
                {{ $errors->first('toleransi_pulcep')}} @endif 00 Menit" required="">
            </div>
          </div>
        </div>
        <div class="box-footer">
          <button type="submit" class="btn bg-purple pull-right">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('plugins/iCheck/icheck.min.js') }}"></script>
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
$(".select2").select2();
</script>
@endsection
