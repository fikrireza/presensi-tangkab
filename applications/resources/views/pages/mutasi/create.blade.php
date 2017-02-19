@extends('layout.master')

@section('title')
  <title>Master Mutasi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Master Mutasi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('mutasi.index') }}">Master Mutasi</a></li>
    <li class="active">Tambah Mutasi</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Tambah Data Mutasi</h3>
        <a href="{{ route('pegawai.index') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('mutasi.createStore') }}" method="post"  enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group">
            <label class="col-sm-3 control-label">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama_pegawai" class="form-control" value="{{$getpegskpd->pegawai_nama}}" readonly="true">
              <input type="hidden" name="pegawai_id" value="{{$getpegskpd->pegawai_id}}">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">SKPD Lama</label>
            <div class="col-sm-9">
              <input type="text" name="nama_skpd" class="form-control" value="{{$getpegskpd->skpd_nama}}" readonly="true">
              <input type="hidden" name="skpd_id_old" value="{{$getpegskpd->skpd_id}}">
            </div>
          </div>
          <div class="form-group {{ $errors->has('skpd_id_new') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">SKPD Mutasi</label>
            <div class="col-sm-9">
              <select name="skpd_id_new" class="form-control select2">
                <option value="">-- Pilih --</option>
                @foreach ($getskpd as $key)
                <option value="{{$key->id}}">{{ $key->nama}}</option>
                @endforeach
              </select>
                @if($errors->has('skpd_id_new'))
                  <span class="help-block">
                    <strong>{{ $errors->first('skpd_id_new')}}
                    </strong>
                  </span>
                @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mutasi') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Tanggal Mutasi</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" value="{{ old('tanggal_mutasi') }}" id="tanggal_mutasi" type="text" name="tanggal_mutasi" placeholder="@if($errors->has('tanggal_mutasi'))
                  {{ $errors->first('tanggal_mutasi')}}@endif Tanggal Mutasi">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Keterangan</label>
            <div class="col-sm-9">
              <textarea name="keterangan" class="form-control" rows="5" cols="40" placeholder="@if($errors->has('keterangan'))
                {{ $errors->first('keterangan')}}@endif Keterangan ">{{ old('keterangan') }}</textarea>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tpp_dibayarkan') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">TPP <br /><small>(setelah dipotong pajak)</small></label>
            <div class="col-sm-9">
              <input type="text" name="tpp_dibayarkan" class="form-control pull-right" value="{{ old('tpp_dibayarkan') }}"  onkeypress="return isNumber(event)" maxlength="8" placeholder="@if($errors->has('tpp_dibayarkan')) 
                {{ $errors->first('tpp_dibayarkan')}}@endif TPP Setelah Dipotong Pajak ">
            </div>
          </div>
          <div class="form-group {{ $errors->has('nomor_sk') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Nomor SK</label>
            <div class="col-sm-9">
              <input type="text" name="nomor_sk" class="form-control pull-right" value="{{ old('nomor_sk') }}" placeholder="@if($errors->has('nomor_sk')) 
                {{ $errors->first('nomor_sk')}}@endif Nomor SK ">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_sk') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Tanggal SK</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" value="{{ old('tanggal_sk') }}" id="tanggal_sk" type="text" name="tanggal_sk" placeholder="@if($errors->has('tanggal_sk'))
                  {{ $errors->first('tanggal_sk')}}@endif Tanggal SK">
              </div>
            </div>
          </div>
           <div class="form-group {{ $errors->has('upload_sk') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Upload SK</label>
            <div class="col-sm-6">
              <input type="file" name="upload_sk" class="form-control" accept=".png, .jpg, .pdf" value="{{ old('upload_sk') }}">
              <span style="color:red;">Hanya .jpg, .png, .pdf</span>
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
var date = new Date();
$('#tanggal_mutasi').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
});
$('#tanggal_sk').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
});

function isNumber(evt) {
  evt = (evt) ? evt : window.event;
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
      return false;
  }
  return true;
}
</script>
@endsection