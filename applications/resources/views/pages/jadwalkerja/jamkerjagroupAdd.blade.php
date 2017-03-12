@extends('layout.master')

@section('title')
<title>Tambah Group Jam Kerja | Presensi Online</title>
<link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('headscript')
<link rel="stylesheet" href="{{ asset('plugins/iCheck/all.css') }}">
@endsection

@section('breadcrumb')
  <h1>Tambah Group Jam Kerja</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('jadwal-kerja') }}">Jadwal Jam Kerja</a></li>
    <li><a href="{{ route('jadwal-kerja.group') }}">Group Jam Kerja</a></li>
    <li class="active">Tambah Group Jam Kerja</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Tambah Group Jam Kerja</h3>
        <a href="{{ route('jadwal-kerja.group') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('jadwal-kerja.postgroup') }}" method="post">
        {{ csrf_field() }}
        <div class="box-body">
          <table class="table" id="JamKerja">
            <tr>
              <td>&nbsp;</td>
              <td><label class="control-label">Nama Group Jam Kerja</label>&nbsp;</td>
              <td><input type="text" name="nama_group" class="form-control" value="{{ old('nama_group') }}" placeholder="@if($errors->has('nama_group'))
                {{ $errors->first('nama_group')}}@endif Nama Group Jam Kerja" required="">
                </td>
            </tr>
            <tr>
              <td><input type="checkbox" name="chk" class="flat-purple"/></td>
              <td><label class="control-label">Jam Kerja</label></td>
              <td><select name="jamKerja[1][jam_kerja_id]" class="form-control select2" required="">
                <option value="--Pilih--">-- Pilih --</option>
                @foreach ($getJamKerja as $key)
                <option value="{{ $key->id }}">{{ $key->nama_jam_kerja}} -> {{ $key->jam_masuk }} s/d {{$key->jam_pulang}}</option>
                @endforeach
              </select>
              @if($errors->has('jamKerja[1][jam_kerja_id]'))
              <span class="help-block">
                <i>* {{$errors->first('jamKerja[1][jam_kerja_id]')}}</i>
              </span>
              @endif</td>
            </tr>
          </table>
        </div>
        <div class="box-footer clearfix">
          <div class="col-md-6">
            <label class="btn bg-green" onclick="addJamKerja('JamKerja')">Tambah Jam Kerja</label>&nbsp;<label class="btn bg-red" onclick="delJamKerja('JamKerja')">Hapus Jam Kerja</label>
          </div>
          <div class="col-md-6">
            <button type="submit" class="btn bg-purple pull-right">Simpan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/iCheck/icheck.min.js') }}"></script>
<script type="text/javascript">
$(".select2").select2();
$('input[type="checkbox"].flat-purple').iCheck({
  checkboxClass: 'icheckbox_flat-purple'
});

var numA=1;
  function addJamKerja(tableID) {
      numA++;
      var table = document.getElementById(tableID);
      var rowCount = table.rows.length;
      var row = table.insertRow(rowCount);
      var cell1 = row.insertCell(0);
      cell1.innerHTML = '<input type="checkbox" name="chk" class="flat-purple"/>';
      var cell2 = row.insertCell(1);
      cell2.innerHTML = '<label class="control-label">Jam Kerja</label>';
      var cell3 = row.insertCell(2);
      cell3.innerHTML = '<select name="jamKerja['+numA+'][jam_kerja_id]" class="form-control select2" required=""><option value="--Pilih--">--Pilih --</option>@foreach ($getJamKerja as $key)<option value="{{ $key->id }}">{{ $key->nama_jam_kerja}} -> {{ $key->jam_masuk }} s/d {{$key->jam_pulang}}</option>@endforeach</select>@if($errors->has("jamKerja['+numA+'][jam_kerja_id]"))<span class="help-block"><i>* {{$errors->first("jamKerja['+numA+'][jam_kerja_id]")}}</i></span>@endif';
      $(".select2").select2();
      $('input[type="checkbox"].flat-purple').iCheck({
        checkboxClass: 'icheckbox_flat-purple'
      });
  }

  function delJamKerja(tableID) {
      try {
      var table = document.getElementById(tableID);
      var rowCount = table.rows.length;
      for(var i=0; i<rowCount; i++) {
          var row = table.rows[i];
          var chkbox = row.cells[0].childNodes[0];
          if(null != chkbox && true == chkbox.checked) {
              table.deleteRow(i);
              rowCount--;
              i--;
              numA--;
          }
      }
      }catch(e) {
          alert(e);
      }
  }

</script>
@endsection
