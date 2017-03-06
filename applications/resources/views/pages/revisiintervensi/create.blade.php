@extends('layout.master')

@section('title')
  <title>Revisi Intervensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Revisi Intervensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('mutasi.index') }}">Revisi Intervensi</a></li>
    <li class="active">Tambah Mutasi</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Tambah Data Revisi Intervensi</h3>
        <a href="{{ route('revisiintervensi.index') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('revisiintervensi.createStore') }}" method="post"  enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('skpd') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">SKPD</label>
            <div class="col-sm-8">
              <select name="skpd" class="form-control select2">
                <option value="">-- Pilih --</option>
                @foreach ($getskpd as $key)
                <option value="{{ $key->id }}" {{ old('skpd') == $key->id ? 'selected' : ''}}>{{ $key->nama }}</option>
                <a class="btn btn-sm bg-green" href="{{ url('revisi-intervensi/caripegawai', 1) }}">Cari Pegawai</a>
                @endforeach
              </select>
                @if($errors->has('skpd'))
                  <span class="help-block">
                    <strong>{{ $errors->first('skpd')}}
                    </strong>
                  </span>
                @endif
            </div>
            <a class="btn btn-sm bg-green" href="{{ url('revisi-intervensi/caripegawai', 15) }}">Cari Pegawai</a>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="col-md-10">
              <div class="box box-primary">
                <div class="box-body no-padding">
                  <table class="table table-hover">
                    <tbody>
                      <tr class="bg-blue">
                        <th style="width:10px;">Pilih</th>
                        <th>NIP</th>
                        <th>Nama Pegawai</th>
                      </tr>
                      @if(!$getcaripegawai->isEmpty())
                        @foreach($getcaripegawai as $key)
                          <tr>
                            <td><input type="checkbox" name="chk"/></td>
                            <td>{{$key->nip_sapk}}</td>
                            <td>{{$key->nama}}</td>
                          </tr>
                        @endforeach
                      @else
                      <tr>
                        <td class="text-muted" colspan="5" style="text-align:center;">
                          Data pegawai tidak tersedia.
                        </td>
                      </tr>
                    @endif
                    </tbody>
                  </table>
                </div>
                <div class="box-footer">
                  <ul class="pagination pagination-sm no-margin pull-right">
                    {{ $getcaripegawai->links() }}
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_revisi') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tanggal Revisi</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="datepicker1" type="text" name="tanggal_revisi"  value="{{ old('tanggal_revisi') }}" placeholder="@if($errors->has('tanggal_revisi'))
                  {{ $errors->first('tanggal_revisi')}}@endif Tanggal Revisi">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Keterangan</label>
            <div class="col-sm-10">
              <textarea name="keterangan" class="form-control" rows="5" cols="40" placeholder="@if($errors->has('keterangan'))
                {{ $errors->first('keterangan')}}@endif Keterangan ">{{ old('keterangan') }}</textarea>
            </div>
          </div>
          <div class="form-group {{ $errors->has('upload_revisi') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Upload Document</label>
            <div class="col-sm-10">
              <input type="file" name="upload_revisi" class="form-control {{ $errors->has('upload_revisi') ? 'has-error' : '' }}" accept=".png, .jpg, .pdf" required>
              <span style="color:red;">Hanya .jpg, .png, .pdf</span>
               @if($errors->has('upload_revisi'))
              <span class="help-block">
                <strong>{{ $errors->first('upload_revisi')}}
                </strong>
              </span>
              @endif
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
<script language="javascript">
    var numA=1;
    function adduploaddocument(tableID) {
      numA++;
      var table = document.getElementById(tableID);
      var rowCount = table.rows.length;
      var row = table.insertRow(rowCount);
      var cell1 = row.insertCell(0);
      cell1.innerHTML = '<input type="checkbox" name="chk[]"/>';
      var cell2 = row.insertCell(1);
      cell2.innerHTML = '<input type="file" name="upload_sk['+numA+']" class="form-control" value="" accept=".png, .jpg, .pdf" required/>';
    }

    function deluploaddocument(tableID) {
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
