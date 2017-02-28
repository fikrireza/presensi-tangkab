@extends('layout.master')

@section('title')
  <title>Master Pegawai</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Master Pegawai</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('pegawai.index') }}">Master Pegawai</a></li>
    <li class="active">Tambah Pegawai</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Tambah Data Pegawai</h3>
        <a href="{{ route('pegawai.index') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('pegawai.post') }}" method="post">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('nama_pegawai') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Nama</label>
            <div class="col-sm-10">
              <input type="text" name="nama_pegawai" class="form-control" value="{{ old('nama_pegawai') }}" placeholder="@if($errors->has('nama_pegawai'))
                {{ $errors->first('nama_pegawai')}}@endif Nama">
            </div>
          </div>
          <div class="form-group {{ $errors->has('nip_sapk') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">NIP</label>
            <div class="col-sm-10">
              <input type="text" name="nip_sapk" class="form-control" value="{{ old('nip_sapk') }}" onkeypress="return isNumber(event)" placeholder="@if($errors->has('nip_sapk'))
                {{ $errors->first('nip_sapk')}}@endif NIP">
                @if($errors->has('nip_sapk'))
                  <span class="help-block">
                    <strong>{{ $errors->first('nip_sapk')}}
                    </strong>
                  </span>
                @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('nip_lm') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">NIP Lama</label>
            <div class="col-sm-10">
              <input type="text" name="nip_lm" class="form-control" value="{{ old('nip_lm') }}" placeholder="@if($errors->has('nip_lm'))
                {{ $errors->first('nip_lm')}}@endif NIP Lama">
            </div>
          </div>
          <div class="form-group {{ $errors->has('fid') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Finger ID</label>
            <div class="col-sm-10">
              <input type="text" name="fid" class="form-control" value="{{ old('fid') }}" onkeypress="return isNumber(event)" placeholder="@if($errors->has('fid'))
                {{ $errors->first('fid')}}@endif Finger ID" maxlength="14">
              @if($errors->has('fid'))
                <span class="help-block">
                  <strong>{{ $errors->first('fid')}}
                  </strong>
                </span>
              @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('skpd_id') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">SKPD</label>
            <div class="col-sm-10">
              <select name="skpd_id" class="form-control select2">
                <option value="">-- Pilih --</option>
                @foreach ($skpd as $key)
                <option value="{{$key->id}}" {{ old('skpd_id') == $key->id ? 'selected' : '' }}>{{ $key->nama}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('golongan_id') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Golongan</label>
            <div class="col-sm-10">
              <select name="golongan_id" class="form-control select2">
                <option value="">-- Pilih --</option>
                @foreach ($golongan as $key)
                <option value="{{$key->id}}" {{ old('golongan_id') == $key->id ? 'selected' : '' }}>{{ $key->nama}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('struktural_id') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Struktural/Eselon</label>
            <div class="col-sm-10">
              <select name="struktural_id" class="form-control select2">
                <option value="">-- Pilih --</option>
                @foreach ($struktural as $key)
                <option value="{{$key->id}}" {{ old('struktural_id') == $key->id ? 'selected' : '' }}>{{ $key->nama}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jabatan') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Jabatan</label>
            <div class="col-sm-10">
              <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" placeholder="@if($errors->has('jabatan'))
                {{ $errors->first('jabatan')}}@endif Jabatan">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_lahir') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tanggal Lahir</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="datepicker1" type="text" name="tanggal_lahir"  value="{{ old('tanggal_lahir') }}" placeholder="@if($errors->has('tanggal_lahir'))
                  {{ $errors->first('tanggal_lahir')}}@endif Tanggal Lahir">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tempat_lahir') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tempat Lahir</label>
            <div class="col-sm-10">
              <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}" placeholder="@if($errors->has('tempat_lahir'))
                {{ $errors->first('tempat_lahir')}}@endif Tempat Lahir">
            </div>
          </div>
          <div class="form-group {{ $errors->has('pendidikan_terakhir') ? 'has-error' : '' }}">
            <label class="col-sm-2">Pendidikan Terakhir</label>
            <div class="col-sm-10">
              <select name="pendidikan_terakhir" class="form-control select2">
                <option value="">-- PILIH --</option>
                <option value="D.II / PGSD" {{ old('pendidikan_terakhir') == 'D.II / PGSD' ? 'selected' : '' }}>D.II / PGSD</option>
                <option value="D.II / PGA" {{ old('pendidikan_terakhir') == 'D.II / PGA' ? 'selected' : '' }}>D.II / PGA</option>
                <option value="Diploma I" {{ old('pendidikan_terakhir') == 'Diploma I' ? 'selected' : '' }}>Diploma I</option>
                <option value="Diploma II" {{ old('pendidikan_terakhir') == 'Diploma II' ? 'selected' : '' }}>Diploma II</option>
                <option value="Diploma III/Sarjana Muda" {{ old('pendidikan_terakhir') == 'Diploma III/Sarjana Muda' ? 'selected' : '' }}>Diploma III/Sarjana Muda</option>
                <option value="Diploma IV" {{ old('pendidikan_terakhir') == 'Diploma IV' ? 'selected' : '' }}>Diploma IV</option>
                <option value="S1/Sarjana" {{ old('pendidikan_terakhir') == 'S1/Sarjana' ? 'selected' : '' }}>S1/Sarjana</option>
                <option value="S2" {{ old('pendidikan_terakhir') == 'S2' ? 'selected' : '' }}>S2</option>
                <option value="S3/Doktor" {{ old('pendidikan_terakhir') == 'S3/Doktor' ? 'selected' : '' }}>S3/Doktor</option>
                <option value="SLTA" {{ old('pendidikan_terakhir') == 'SLTA' ? 'selected' : '' }}>SLTA</option>
                <option value="SLTA Kejuaruan" {{ old('pendidikan_terakhir') == 'SLTA Kejuaruan' ? 'selected' : '' }}>SLTA Kejuaruan</option>
                <option value="SLTP" {{ old('pendidikan_terakhir') == 'SLTP' ? 'selected' : '' }}>SLTP</option>
                <option value="SLTP Kejuruan" {{ old('pendidikan_terakhir') == 'SLTP Kejuruan' ? 'selected' : '' }}>SLTP Kejuruan</option>
                <option value="Sekolah Dasar" {{ old('pendidikan_terakhir') == 'Sekolah Dasar' ? 'selected' : '' }}>Sekolah Dasar</option>
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('alamat') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Alamat</label>
            <div class="col-sm-10">
              <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}" placeholder="@if($errors->has('alamat'))
                {{ $errors->first('alamat')}}@endif Alamat">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tpp_dibayarkan') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">TPP <br /><small>(setelah dipotong pajak)</small></label>
            <div class="col-sm-10">
              <input type="text" name="tpp_dibayarkan" class="form-control" value="{{ old('tpp_dibayarkan') }}" onkeypress="return isNumber(event)" maxlength="8" placeholder="@if($errors->has('tpp_dibayarkan'))
                {{ $errors->first('tpp_dibayarkan')}}@endif TPP Setelah Dipotong Pajak ">
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
$('#datepicker1').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  todayHighlight: true,
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
