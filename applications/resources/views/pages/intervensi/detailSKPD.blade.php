@extends('layout.master')

@section('title')
  <title>SKPD Detail Intervensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>SKPD Detail Intervensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('intervensi.index') }}">Intervensi</a></li>
    <li><a href="{{ route('intervensi.kelola') }}">Kelola Intervensi</a></li>
    <li class="active">Detail Intervensi</li>
  </ol>
@endsection

@section('content')
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 2000);
</script>

@if(Session::has('berhasil'))
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-success">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
      <p>{{ Session::get('berhasil') }}</p>
    </div>
  </div>
</div>
@endif

{{-- Modal Tambah Intervensi--}}
<div class="modal modal-default fade" id="modaltambahIntervensi" role="dialog">
  <div class="modal-dialog" style="width:800px;">
    <form class="form-horizontal" action="{{ route('intervensi.kelola.post') }}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Intervensi</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('pegawai_id') ? 'has-error' : '' }}">
            <label class="col-md-3 control-label">Pegawai</label>
            <div class="col-md-9">
              <select class="form-control select2" name="pegawai_id" style="width:100%;">
                <option value="">-- PILIH --</option>
                @foreach ($pegawai as $key)
                <option value="{{ $key->id }}" {{ old('pegawai_id') == $key->id ? 'selected' : ''}}>{{ $key->nama }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jenis_intervensi') ? 'has-error' : '' }}" >
            <label class="col-sm-3 control-label">Jenis Intervensi</label>
            <div class="col-sm-9">
              <select class="form-control select2" name="jenis_intervensi" style="width:100%;">
                <option value="">-- PILIH --</option>
                <option value="Ijin" {{ old('jenis_interrvensi') == 'Ijin' ? 'selected' : ''}}>Ijin</option>
                <option value="Sakit" {{ old('jenis_intervensi') == 'Sakit' ? 'selected' : ''}}>Sakit</option>
                <option value="Cuti" {{ old('jenis_intervensi') == 'Cuti' ? 'selected' : ''}}>Cuti</option>
                <option value="DinasLuar" {{ old('jenis_intervensi') == 'DinasLuar' ? 'selected' : ''}}>Dinas Luar</option>
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mulai') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Tanggal Mulai</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_mulai" type="text" name="tanggal_mulai"  value="{{ old('tanggal_mulai') }}" placeholder="@if($errors->has('tanggal_mulai')){{ $errors->first('tanggal_mulai')}}@endif Tanggal Mulai">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_akhir') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Tanggal Akhir</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_akhir" type="text" name="tanggal_akhir"  value="{{ old('tanggal_akhir') }}" placeholder="@if($errors->has('tanggal_akhir')){{ $errors->first('tanggal_akhir')}}@endif Tanggal Akhir" onchange="durationDay()">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jumlah_hari') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Jumlah Hari</label>
            <div class="col-sm-9">
              <input type="text" name="jumlah_hari" id="jumlah_hari" class="form-control" value="{{ old('jumlah_hari') }}" placeholder="@if($errors->has('jumlah_hari')){{ $errors->first('jumlah_hari')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Keterangan</label>
            <div class="col-sm-9">
              <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="@if($errors->has('keterangan')){{ $errors->first('keterangan')}} @endif Keterangan" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('berkas') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Berkas</label>
            <div class="col-sm-9">
              <input type="file" name="berkas" class="form-control" accept=".png, .jpg, .pdf" value="{{ old('berkas') }}">
              <span style="color:red;">Hanya .jpg, .png, .pdf</br>*Kosongkan Jika Tidak Ingin Mengganti Berkas</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <button type="submit" class="btn btn-danger">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Kelola Intervensi</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahIntervensi">Tambah Intervensi Pegawai</a>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Jenis Intervensi</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Akhir</th>
              <th>Status Intervensi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @if ($intervensi->isEmpty())
            <tr>
              <td colspan="7" align="center"> Anda Belum Pernah Melakukan Intervensi </td>
            </tr>
            @else
            @foreach ($intervensi as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nip_sapk }}</td>
              <td>{{ $key->nama_pegawai }}</td>
              <td>{{ $key->jenis_intervensi }}</td>
              <td>{{ $key->tanggal_mulai }}</td>
              <td>{{ $key->tanggal_akhir }}</td>
              <td>@if ($key->flag_status == 0)
                <small class="label label-info">Belum diSetujui</small>
              @elseif($key->flag_status == 1)
                <small class="label label-success">Sudah diSetujui</small>
              @else
                <small class="label label-danger">Tidak diSetujui</small>
              @endif</td>
            </tr>
            <?php $no++; ?>
            @endforeach
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
  $(".select2").select2();

  var date = new Date();
  date.setDate(date.getDate()-3);
  $('#tanggal_mulai').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    startDate: date,
    todayHighlight: true,
    daysOfWeekDisabled: [0,6]
  });
  $('#tanggal_akhir').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    startDate: date,
    todayHighlight: true,
    daysOfWeekDisabled: [0,6]
  });
  $('.tanggal_mulai_edit').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    startDate: date,
    todayHighlight: true,
    daysOfWeekDisabled: [0,6]
  });
  $('.tanggal_akhir_edit').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    startDate: date,
    todayHighlight: true,
    daysOfWeekDisabled: [0,6]
  });

  $(function(){
    $("#table_skpd").DataTable();
  });

  @if ($errors->has('pegawai_id') || $errors->has('jenis_intervensi') || $errors->has('tanggal_mulai') || $errors->has('tanggal_akhir'))
  $('#modaltambahIntervensi').modal('show');
  @endif
</script>
<script type="text/javascript">
  function durationDay(){
    $(document).ready(function() {
      $('#tanggal_mulai, #tanggal_akhir').on('change textInput input', function () {
            if ( ($("#tanggal_mulai").val() != "") && ($("#tanggal_akhir").val() != "")) {
                var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
                var firstDate = new Date($("#tanggal_mulai").val());
                var secondDate = new Date($("#tanggal_akhir").val());
                var diffDays = Math.round(Math.round((secondDate.getTime() - firstDate.getTime()) / (oneDay))); 
                $("#jumlah_hari").val(diffDays+1);
            }
        });
    });

  }
</script>
@endsection
