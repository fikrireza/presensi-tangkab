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
      <form class="form-horizontal" role="form" action="{{ route('mutasi.createStore') }}" method="post">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('nama_pegawai') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama_pegawai" class="form-control" value="{{ old('nama_pegawai') }}" placeholder="@if($errors->has('nama_pegawai'))
                {{ $errors->first('nama_pegawai')}}@endif Nama">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tpp_dibayarkan') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">TPP <br /><small>(setelah dipotong pajak)</small></label>
            <div class="col-sm-9">
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
