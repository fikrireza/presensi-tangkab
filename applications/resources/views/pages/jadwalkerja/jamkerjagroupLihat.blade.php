@extends('layout.master')

@section('title')
<title>Group Jam Kerja | Presensi Online</title>
<link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('headscript')
<link rel="stylesheet" href="{{ asset('plugins/iCheck/all.css') }}">
@endsection

@section('breadcrumb')
  <h1>Group Jam Kerja</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('jadwal-kerja') }}">Jadwal Jam Kerja</a></li>
    <li><a href="{{ route('jadwal-kerja.group') }}">Group Jam Kerja</a></li>
    <li class="active">Group Jam Kerja</li>
  </ol>
@endsection

@section('content')

{{-- Modal NonAktif Group Kerja --}}
<div class="modal fade" id="myModalNonAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Non Aktif Group Kerja?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk me-non Aktifkan Group Kerja ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setnonaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal Aktif Group Kerja --}}
<div class="modal fade" id="myModalAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Aktifkan Group Kerja?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk Aktifkan Group Kerja ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Group Jam Kerja</h3>
        <a href="{{ route('jadwal-kerja.group') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('jadwal-kerja.postgroup') }}" method="post">
        {{ csrf_field() }}
        <div class="box-body">
          <table class="table">
            <tr>
              <td><input type="checkbox" name="chk" class="flat-purple" disabled=""/></td>
              <td><label class="control-label">Nama Group Jam Kerja</label>&nbsp;</td>
              <td><input type="text" name="nama_group" class="form-control" value="{{ $lihat[0]->nama_group}}" readonly=""></td>
            </tr>
            @foreach ($lihat as $lihats)
            <tr>
              <td><input type="checkbox" name="chk" class="flat-purple" disabled=""/></td>
              <td><label class="control-label">Jam Kerja</label>&nbsp;</td>
              <td><select name="jam_kerja_id" class="form-control select2" disabled="">
                <option value="--Pilih--">-- Pilih --</option>
                @foreach ($getJamKerja as $key)
                <option value="{{ $key->id }}" @if($lihats->jam_kerja_id == $key->id) selected="" @endif>{{ $key->nama_jam_kerja}} -> {{ $key->jam_masuk }} s/d {{$key->jam_pulang}}</option>
                @endforeach
              </select></td>
              <td>@if ($lihats->flag_status == 1)
              @if (session('status') == 'administrator' || session('status') == 'superuser')
                <a href="" class="nonaktif" data-toggle="modal" data-target="#myModalNonAktif" data-value="{{ $lihats->id }}">NonAktif</a>
              @endif
              @else
              @if (session('status') == 'administrator' || session('status') == 'superuser')
                <a href="" class="aktif" data-toggle="modal" data-target="#myModalAktif" data-value="{{ $lihats->id }}">Aktifkan</a>
              @endif
              @endif</td>
            </tr>
            @endforeach
          </table>
          <table class="table" id="JamKerja">
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td><input type="hidden" name="nama_group" class="form-control" value="{{ $lihat[0]->nama_group}}">
              <input type="hidden" name="group_id" class="form-control" value="{{ $lihat[0]->group_id}}"></td>
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


$('a.nonaktif').click(function(){
  var a = $(this).data('value');
  $('#setnonaktif').attr('href', "{{ url('/') }}/jadwal-kerja-group/non/"+a);
});
$('a.aktif').click(function(){
  var a = $(this).data('value');
  $('#setaktif').attr('href', "{{ url('/') }}/jadwal-kerja-group/aktif/"+a);
});
</script>
@endsection
