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
    <li class="active">Ubah Data Pegawai</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Ubah Data Pegawai</h3>
        <a href="{{ route('pegawai.index') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('pegawai.editStore') }}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('nama_pegawai') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Nama</label>
            <div class="col-sm-10">
              <input type="hidden" name="pegawai_id" value="{{ $pegawai->id}}">
              <input type="text" name="nama_pegawai" class="form-control" value="{{ $pegawai->nama }}" placeholder="@if($errors->has('nama_pegawai'))
                {{ $errors->first('nama_pegawai')}}@endif Nama">
            </div>
          </div>
          <div class="form-group {{ $errors->has('nip_sapk') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">NIP</label>
            <div class="col-sm-10">
              <input type="text" name="nip_sapk" class="form-control" value="{{ $pegawai->nip_sapk }}" placeholder="@if($errors->has('nip_sapk'))
                {{ $errors->first('nip_sapk')}}@endif NIP">
            </div>
          </div>
          <div class="form-group {{ $errors->has('nip_lm') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">NIP Lama</label>
            <div class="col-sm-10">
              <input type="text" name="nip_lm" class="form-control" value="{{ $pegawai->nip_lm }}" placeholder="@if($errors->has('nip_lm'))
                {{ $errors->first('nip_lm')}}@endif NIP Lama">
            </div>
          </div>
          <div class="form-group {{ $errors->has('fid') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Finger ID</label>
            <div class="col-sm-10">
              <input type="text" name="fid" class="form-control" value="{{ $pegawai->fid }}" onkeypress="return isNumber(event)" placeholder="@if($errors->has('fid'))
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
                @if ($key->id == $pegawai->skpd_id )
                <option value="{{$key->id}}" selected="">{{ $key->nama}}</option>
                @else
                <option value="{{$key->id}}" {{ old('skpd_id') == $key->id ? 'selected' : '' }}>{{ $key->nama}}</option>
                @endif
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
                @if ($key->id == $pegawai->golongan_id )
                <option value="{{$key->id}}" selected="">{{ $key->nama}}</option>
                @else
                <option value="{{$key->id}}" {{ old('golongan_id') == $key->id ? 'selected' : '' }}>{{ $key->nama}}</option>
                @endif
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
                @if ($key->id == $pegawai->struktural_id )
                <option value="{{$key->id}}" selected="">{{ $key->nama}}</option>
                @else
                <option value="{{$key->id}}" {{ old('struktural_id') == $key->id ? 'selected' : '' }}>{{ $key->nama}}</option>
                @endif
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jabatan') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Jabatan</label>
            <div class="col-sm-10">
              <input type="text" name="jabatan" class="form-control" value="{{ $pegawai->jabatan }}" placeholder="@if($errors->has('jabatan'))
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
                <input class="form-control pull-right" id="datepicker1" type="text" name="tanggal_lahir"  value="{{ $pegawai->tanggal_lahir }}" placeholder="@if($errors->has('tanggal_lahir'))
                  {{ $errors->first('tanggal_lahir')}}@endif Tanggal Lahir">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tempat_lahir') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tempat Lahir</label>
            <div class="col-sm-10">
              <input type="text" name="tempat_lahir" class="form-control" value="{{ $pegawai->tempat_lahir }}" placeholder="@if($errors->has('tempat_lahir'))
                {{ $errors->first('tempat_lahir')}}@endif Tempat Lahir">
            </div>
          </div>
          <div class="form-group {{ $errors->has('pendidikan_terakhir') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Pendidikan Terakhir</label>
            <div class="col-sm-10">
              <select name="pendidikan_terakhir" class="form-control select2">
                <option value="">-- PILIH --</option>
                <option value="D.II / PGSD" {{ $pegawai->pendidikan_terakhir == 'D.II / PGSD' ? 'selected' : '' }}>D.II / PGSD</option>
                <option value="D.II / PGA" {{ $pegawai->pendidikan_terakhir == 'D.II / PGA' ? 'selected' : '' }}>D.II / PGA</option>
                <option value="Diploma I" {{ $pegawai->pendidikan_terakhir == 'Diploma I' ? 'selected' : '' }}>Diploma I</option>
                <option value="Diploma II" {{ $pegawai->pendidikan_terakhir == 'Diploma II' ? 'selected' : '' }}>Diploma II</option>
                <option value="Diploma III/Sarjana Muda" {{ $pegawai->pendidikan_terakhir == 'Diploma III/Sarjana Muda' ? 'selected' : '' }}>Diploma III/Sarjana Muda</option>
                <option value="Diploma IV" {{ $pegawai->pendidikan_terakhir == 'Diploma IV' ? 'selected' : '' }}>Diploma IV</option>
                <option value="S-1/Sarjana" {{ $pegawai->pendidikan_terakhir == 'S-1/Sarjana' ? 'selected' : '' }}>S1/Sarjana</option>
                <option value="S-2" {{ $pegawai->pendidikan_terakhir == 'S-2' ? 'selected' : '' }}>S2</option>
                <option value="S-3/Doktor" {{ $pegawai->pendidikan_terakhir == 'S-3/Doktor' ? 'selected' : '' }}>S3/Doktor</option>
                <option value="SLTA" {{ $pegawai->pendidikan_terakhir == 'SLTA' ? 'selected' : '' }}>SLTA</option>
                <option value="SLTA Kejuaruan" {{ $pegawai->pendidikan_terakhir == 'SLTA Kejuaruan' ? 'selected' : '' }}>SLTA Kejuaruan</option>
                <option value="SLTP" {{ $pegawai->pendidikan_terakhir == 'SLTP' ? 'selected' : '' }}>SLTP</option>
                <option value="SLTP Kejuruan" {{ $pegawai->pendidikan_terakhir == 'SLTP Kejuruan' ? 'selected' : '' }}>SLTP Kejuruan</option>
                <option value="Sekolah Dasar" {{ $pegawai->pendidikan_terakhir == 'Sekolah Dasar' ? 'selected' : '' }}>Sekolah Dasar</option>
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('alamat') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Alamat</label>
            <div class="col-sm-10">
              <input type="text" name="alamat" class="form-control" value="{{ $pegawai->alamat }}" placeholder="@if($errors->has('alamat'))
                {{ $errors->first('alamat')}}@endif Alamat">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tpp_dibayarkan') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">TPP <br /><small>(setelah dipotong pajak)</small></label>
            <div class="col-sm-10">
              <input type="text" name="tpp_dibayarkan" class="form-control" value="{{ $pegawai->tpp_dibayarkan }}" onkeypress="return isNumber(event)" maxlength="8" placeholder="@if($errors->has('tpp_dibayarkan'))
                {{ $errors->first('tpp_dibayarkan')}}@endif TPP Setelah Dipotong Pajak">
            </div>
          </div>
          @if (session('status') == 'administrator' || session('status') == 'superuser')
          <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Status</label>
            <div class="col-sm-10">
              <select name="status" id="status" class="form-control select2">
                <option value="">-- PILIH --</option>
                <option value="1" {{ $pegawai->status == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="2" {{ $pegawai->status == '2' ? 'selected' : '' }}>Non Aktif</option>
                <option value="3" {{ $pegawai->status == '3' ? 'selected' : '' }}>Pensiun</option>
                <option value="4" {{ $pegawai->status == '4' ? 'selected' : '' }}>Meninggal</option>
              </select>
            </div>
          </div>
          <div class="form-group" id="upload_dokumen_edit">
            <label class="col-sm-2 control-label">File Dokumen</label>
            <div class="col-sm-10">
              <input type="file" name="upload_dokumen" class="form-control" accept=".png, .jpg, .pdf">
              <span style="color:red;">Hanya .jpg, .png, .pdf</br>*Kosongkan Jika Tidak Ingin Mengganti Berkas</span>
               @if($errors->has('upload_dokumen'))
                  <span class="help-block">
                    <strong>{{ $errors->first('upload_dokumen')}}
                    </strong>
                  </span>
                @endif
            </div>
          </div>
          @else
            <input type="hidden" name="status" value="{{ $pegawai->status }}" />
          @endif
        </div>
        <div class="box-footer">
          <button type="submit" class="btn bg-purple pull-right">Ubah Data</button>
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
 var level = $('#status').val();
  if (level==1) {
    $('#upload_dokumen_edit').hide();
  } else {
    $('#upload_dokumen_edit').show();
  }

$('#status').change(function(){
  var a = $(this).val();
  if (a==1) {
    $('#upload_dokumen_edit').hide();
  } else {
    $('#upload_dokumen_edit').show();
  }
});

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
