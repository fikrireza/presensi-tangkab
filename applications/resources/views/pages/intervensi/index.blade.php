@extends('layout.master')

@section('title')
  <title>Intervensi</title>
  <link rel="stylesheet" href="{{asset('plugins/timepicker/bootstrap-timepicker.min.css')}}" media="screen" title="no title">
@endsection

@section('breadcrumb')
  <h1>Intervensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Intervensi</li>
  </ol>
@endsection

@section('content')
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 5000);

  window.setTimeout(function() {
    $(".alert-danger").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 5000);
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

@if(Session::has('gagal'))
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-danger">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Terjadi Kesalahan!</h4>
      <p>{{ Session::get('gagal') }}</p>
    </div>
  </div>
</div>
@endif

@if(Session::has('gagaltgl'))
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-warning">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Terjadi Kesalahan!</h4>
      <p>{{ Session::get('gagaltgl') }}</p>
    </div>
  </div>
</div>
@endif

{{-- Modal Tambah Intervensi--}}
<div class="modal modal-default fade" id="modaltambahIntervensi" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('intervensi.post') }}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Intervensi</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('jenis_intervensi') ? 'has-error' : '' }}">
            <label class="col-sm-3">Jenis Intervensi</label>
            <div class="col-sm-9">
              <select class="form-control select2" name="jenis_intervensi" id="id_intervensi">
                <option value="">-- PILIH --</option>
                @foreach ($getmasterintervensi as $key)
                  <option value="{{$key->id}}">{{$key->nama_intervensi}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mulai') ? 'has-error' : '' }}">
            <label class="col-sm-3">Tanggal Mulai</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_mulai" type="text" name="tanggal_mulai"  value="{{ old('tanggal_mulai') }}" placeholder="@if($errors->has('tanggal_mulai'))
                  {{ $errors->first('tanggal_mulai')}}@endif Tanggal Mulai">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_akhir') ? 'has-error' : '' }}">
            <label class="col-sm-3">Tanggal Akhir</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_akhir" type="text" name="tanggal_akhir"  value="{{ old('tanggal_akhir') }}" placeholder="@if($errors->has('tanggal_akhir')){{ $errors->first('tanggal_akhir')}}@endif Tanggal Akhir" onchange="durationDay()">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jumlah_hari') ? 'has-error' : '' }}" hidden="true">
            <label class="col-sm-3">Jumlah Hari</label>
            <div class="col-sm-9">
              <input type="text" name="jumlah_hari" id="jumlah_hari" class="form-control" value="{{ old('jumlah_hari') }}" placeholder="@if($errors->has('jumlah_hari'))
                {{ $errors->first('jumlah_hari')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Keterangan</label>
            <div class="col-sm-9">
              <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="@if($errors->has('keterangan'))
                {{ $errors->first('keterangan')}} @endif Keterangan">
            </div>
          </div>

          <div id="keterangantambahan">
            <div class='form-group'>
              <label class='col-sm-3'>Nama Atasan</label>
              <div class='col-sm-9'>
                <select name='atasan' class='form-control'>
                  <option value="---">-- Pilih --</option>
                  @foreach ($getpegawai as $key)
                    <option value='{{$key->nip_sapk}}//{{$key->nama}}'>{{$key->nama}}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div id="jamijin">
              <div class='form-group'>
                <label class='col-sm-3'>Jam Ijin</label>
                <div class='col-sm-9'>
                  <div class="input-group bootstrap-timepicker timepicker">
                    <input id="timepicker1" type="text" class="form-control input-small" name="jam_ijin">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group {{ $errors->has('berkas[]') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Upload Document</label>
            <div class="tab-content col-sm-10">
              <div class="tab-pane active" id="tab_Dokumen">
                <div class="box-body">
                  <table class="table" id="duploaddocument">
                    <tbody>
                      <tr>
                        <td><input type="checkbox" name="chk"/></td>
                        <td>
                          <input type="file" name="berkas[]" class="form-control {{ $errors->has('berkas[]') ? 'has-error' : '' }}" accept=".png, .jpg, .pdf">
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <span style="color:red;">Hanya .jpg, .png, .pdf</span>
                   @if($errors->has('berkas[]'))
                  <span class="help-block">
                    <strong>{{ $errors->first('berkas[]')}}
                    </strong>
                  </span>
                @endif
                </div>
                <div class="box-footer clearfix">
                  <div class="col-md-9">
                    <label class="btn btn-sm bg-green" onclick="adduploaddocument('duploaddocument')">Tambah Dokumen</label>&nbsp;<label class="btn btn-sm bg-red" onclick="deluploaddocument('duploaddocument')">Hapus Dokumen</label>
                  </div>
                </div>
              </div>
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

{{-- Modal Edit Intervensi --}}
<div class="modal modal-default fade" id="modaleditIntervensi" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('intervensi.edit') }}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Intervensi</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('jenis_intervensi_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3">Jenis Intervensi</label>
            <div class="col-sm-9">
              <select class="form-control select2" name="jenis_intervensi_edit" id="jenis_intervensi_edit">
                <option value="">-- PILIH --</option>
                @foreach ($getmasterintervensi as $key)
                  <option value="{{$key->id}}" id="mi_{{$key->id}}">{{$key->nama_intervensi}}</option>
                @endforeach
              </select>
              <input type="hidden" name="id_edit" class="form-control" id="id_edit" value="{{ old('id_edit') }}" placeholder="@if($errors->has('id_edit'))
                {{ $errors->first('id_edit')}} @endif id" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mulai_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3">Tanggal Mulai</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right tanggal_mulai_edit" id="tanggal_mulai_edit" type="text" name="tanggal_mulai_edit"  value="{{ old('tanggal_mulai_edit') }}" placeholder="@if($errors->has('tanggal_mulai_edit'))
                  {{ $errors->first('tanggal_mulai_edit')}}@endif Tanggal Mulai" onchange="durationDayEdit()">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_akhir_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3">Tanggal Akhir</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right tanggal_akhir_edit" id="tanggal_akhir_edit" type="text" name="tanggal_akhir_edit"  value="{{ old('tanggal_akhir_edit') }}" placeholder="@if($errors->has('tanggal_akhir_edit')){{ $errors->first('tanggal_akhir_edit')}}@endif Tanggal Akhir" onchange="durationDayEdit()">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jumlah_hari_edit') ? 'has-error' : '' }}" hidden="true">
            <label class="col-sm-3">Jumlah Hari</label>
            <div class="col-sm-9">
              <input type="text" name="jumlah_hari_edit" class="form-control" id="jumlah_hari_edit" value="{{ old('jumlah_hari_edit') }}" placeholder="@if($errors->has('jumlah_hari_edit'))
                {{ $errors->first('jumlah_hari_edit')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3">Keterangan</label>
            <div class="col-sm-9">
              <input type="text" name="keterangan_edit" class="form-control" id="keterangan_edit" value="{{ old('keterangan_edit') }}" placeholder="@if($errors->has('keterangan_edit'))
                {{ $errors->first('keterangan_edit')}} @endif Keterangan" required="">
            </div>
          </div>

          <div id="keterangantambahanedit">
            <div class='form-group'>
              <label class='col-sm-3'>Nama Atasan</label>
              <div class='col-sm-9'>
                <select name='atasan_edit' class='form-control'>
                  <option value="---">-- Pilih --</option>
                  @foreach ($getpegawai as $key)
                    <option value='{{$key->nip_sapk}}//{{$key->nama}}'>{{$key->nama}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div id="jamijinedit">
              <div class='form-group'>
                <label class='col-sm-3'>Jam Ijin</label>
                <div class='col-sm-9'>
                  <input type='text' name='jam_ijin_edit' class='form-control'>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group {{ $errors->has('berkas_edit[]') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Upload Document</label>
            <div class="tab-content col-sm-10">
              <div class="tab-pane active" id="tab_Dokumen">
                <div class="box-body">
                  <table class="table" id="duploaddocumentedit">
                    <tbody>
                      <tr>
                        <td><input type="checkbox" name="chk"/></td>
                        <td>
                          <input type="file" name="berkas_edit[]" class="form-control {{ $errors->has('berkas_edit[]') ? 'has-error' : '' }}" accept=".png, .jpg, .pdf">
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <span style="color:red;">Hanya .jpg, .png, .pdf</span>
                   @if($errors->has('berkas_edit[]'))
                  <span class="help-block">
                    <strong>{{ $errors->first('berkas_edit[]')}}
                    </strong>
                  </span>
                @endif
                </div>
                <div class="box-footer clearfix">
                  <div class="col-md-9">
                    <label class="btn btn-sm bg-green" onclick="adduploaddocument('duploaddocumentedit')">Tambah Dokumen</label>&nbsp;<label class="btn btn-sm bg-red" onclick="deluploaddocument('duploaddocumentedit')">Hapus Dokumen</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
</div>


{{-- Modal Batal Intervensi --}}
<div class="modal fade" id="batalIntervensi" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Batal Intervensi ?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk batalkan intervensi ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setBatal">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal View Documents --}}
<div class="modal fade" id="modalviewdocument" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Preview Berkas</h4>
      </div>
      <div class="modal-body">
        <div id="previewdocument"></div>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-right btn-flat" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>



<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Intervensi</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahIntervensi">Tambah Intervensi</a>
        @if(session('status') != 'pegawai' && session('status') != 'sekretaris' && session('status') != 'bpkad')
        <a href="{{ route('intervensi.kelola') }}" class="btn bg-green pull-right">Lihat Intervensi</a>
        @endif
      </div>
      <div class="box-body table-responsive">
        <table id="table_intervensi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Jenis Intervensi</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Akhir</th>
              <th>Keterangan</th>
              <th>Preview Berkas</th>
              <th>Form Izin</th>
              <th>Status Intervensi</th>
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($intervensi as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->jenis_intervensi }}</td>
              <td>{{ $key->tanggal_mulai }}</td>
              <td>{{ $key->tanggal_akhir }}</td>
              <td>{{ $key->deskripsi }}</td>
              <td>
                @php
                  $fileberkas = explode("//", $key->berkas);
                @endphp
                @if (count($fileberkas)>1)
                  @for ($i=0; $i < count($fileberkas); $i++)
                    @if ($fileberkas[$i]!="")
                      <a href="#" data-value="{{url('/documents')}}/{{$fileberkas[$i]}}" class="viewdocument" data-toggle="modal" data-target="#modalviewdocument" title="Klik untuk lihat file.">
                        <i class="fa fa-file-o"></i>
                      </a>&nbsp;
                    @endif
                  @endfor
                @elseif (count($fileberkas)==1)
                  @if ($fileberkas[0]!="" && $fileberkas[0]!="-")
                    <a href="#" data-value="{{url('/documents')}}/{{$fileberkas[0]}}" class="viewdocument" data-toggle="modal" data-target="#modalviewdocument" title="Klik untuk lihat file.">
                      <i class="fa fa-file-o"></i>
                    </a>&nbsp;
                    @else
                      -
                  @endif
                @endif
              </td>
              <td>
                @if ($key->id_intervensi==2 || $key->id_intervensi==3 || $key->id_intervensi==13)
                  <a href="{{route('intervensi.suratijin', $key->id)}}" title="Download Surat Ijin">
                    <i class="fa fa-file-o"></i>
                  </a>
                @else
                  -
                @endif
              </td>
              <td>@if (($key->flag_status == 0) && (date('Y-m-d', strtotime($key->tanggal_akhir. ' + 45 days')) >= date('Y-m-d')))
                <small class="label label-info">Belum Ditanggapi</small>
              @elseif($key->flag_status == 1)
                <small class="label label-success">Sudah Disetujui</small>
              @elseif($key->flag_status == 3)
                <small class="label label-warning">Dibatalkan</small>
              @else
                <small class="label label-danger">Tidak Disetujui</small>
              @endif</td>
              <td>@if ($key->flag_status == 0)
                  @if (date('Y-m-d', strtotime($key->tanggal_akhir. ' + 45 days')) >= date('Y-m-d'))
                    <a href="" data-value="{{ $key->id }}" class="btn btn-xs btn-warning editIntervensi" data-toggle="modal" data-target="#modaleditIntervensi"><i class="fa fa-edit"></i> Ubah</a>
                    <a href="" class="btn btn-xs btn-danger batalIntervensi" data-toggle="modal" data-target="#batalIntervensi" data-value="{{ $key->id }}"><i class="fa fa-close"></i> Batal</a>
                  @else
                    -
                  @endif
                  @else
                    -
                  @endif
              </td>
            </tr>
            <?php $no++; ?>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')

<script src="{{asset('plugins/timepicker/bootstrap-timepicker.min.js')}}"/>
<script src="https://code.jquery.com/jquery-1.9.1.js"></script>
<script>
  $('#timepicker1').timepicker({
    showInputs: false
  });
</script>

<script src="{{asset('plugins/gdocviewer/jquery.gdocsviewer.min.js')}}"/>
<script>
  $('a.embed').gdocsViewer();
</script>

<script>
// var date = new Date();
// date.setDate(date.getDate()-3);
$('#tanggal_mulai').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  // startDate: date,
  todayHighlight: true,
  daysOfWeekDisabled: [0,6]
});
$('#tanggal_akhir').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  // startDate: date,
  todayHighlight: true,
  daysOfWeekDisabled: [0,6]
});
$('.tanggal_mulai_edit').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  // startDate: date,
  todayHighlight: true,
  daysOfWeekDisabled: [0,6]
});
$('.tanggal_akhir_edit').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  // startDate: date,
  todayHighlight: true,
  daysOfWeekDisabled: [0,6]
});
</script>


<script type="text/javascript">
@if ($errors->has('jenis_intervensi') || $errors->has('tanggal_mulai') || $errors->has('tanggal_akhir') || $errors->has('keterangan'))
  $('#modaltambahIntervensi').modal('show');
@endif
@if ($errors->has('jenis_intervensi_edit') || $errors->has('tanggal_mulai_edit') || $errors->has('tanggal_akhir_edit') || $errors->has('keterangan_edit'))
  $('#modaleditIntervensi').modal('show');
@endif
</script>

<script type="text/javascript">
  $(function(){
    $('#table_intervensi').DataTable().on('click', 'a.editIntervensi[data-value]', function () {
      var a = $(this).data('value');
      $.ajax({
        url: "{{ url('/') }}/intervensi/bind/"+a,
        dataType: 'json',
        success: function(data){
          var id_edit = data.id;
          var jenis_intervensi_edit = data.jenis_intervensi;
          var tanggal_mulai_edit = data.tanggal_mulai;
          var tanggal_akhir_edit = data.tanggal_akhir;
          var jumlah_hari_edit = data.jumlah_hari;
          var keterangan_edit = data.deskripsi;
          var id_intervensi = data.id_intervensi;

          // set
          $('#id_edit').attr('value', id_edit);
          $('#jenis_intervensi_edit').attr('value', jenis_intervensi_edit);
          $('#tanggal_akhir_edit').attr('value', tanggal_akhir_edit);
          $('#tanggal_mulai_edit').attr('value', tanggal_mulai_edit);
          $('#jumlah_hari_edit').attr('value', jumlah_hari_edit);
          $('#keterangan_edit').attr('value', keterangan_edit);

          $('#mi_'+id_intervensi).attr('selected', 'true');
        }
      });
    });
  });

  $('#table_intervensi').DataTable().on('click', 'a.batalIntervensi[data-value]', function () {
    var a = $(this).data('value');
    $('#setBatal').attr('href', "{{ url('/') }}/intervensi/batal/"+a);
  });
</script>

<script type="text/javascript">
  function durationDay(){
    $(document).ready(function() {
      $('#tanggal_mulai, #tanggal_akhir').on('change textInput input', function () {
            if ( ($("#tanggal_mulai").val() != "") && ($("#tanggal_akhir").val() != "")) {
                var dDate1 = new Date($("#tanggal_mulai").val());
                var dDate2 = new Date($("#tanggal_akhir").val());
                var iWeeks, iDateDiff, iAdjust = 0;
                if (dDate2 < dDate1) return -1; // error code if dates transposed
                var iWeekday1 = dDate1.getDay(); // day of week
                var iWeekday2 = dDate2.getDay();
                iWeekday1 = (iWeekday1 == 0) ? 7 : iWeekday1; // change Sunday from 0 to 7
                iWeekday2 = (iWeekday2 == 0) ? 7 : iWeekday2;
                if ((iWeekday1 > 5) && (iWeekday2 > 5)) iAdjust = 1; // adjustment if both days on weekend
                iWeekday1 = (iWeekday1 > 5) ? 5 : iWeekday1; // only count weekdays
                iWeekday2 = (iWeekday2 > 5) ? 5 : iWeekday2;

                // calculate differnece in weeks (1000mS * 60sec * 60min * 24hrs * 7 days = 604800000)
                iWeeks = Math.floor((dDate2.getTime() - dDate1.getTime()) / 604800000)

                if (iWeekday1 <= iWeekday2) {
                  iDateDiff = (iWeeks * 5) + (iWeekday2 - iWeekday1)
                } else {
                  iDateDiff = ((iWeeks + 1) * 5) - (iWeekday1 - iWeekday2)
                }

                iDateDiff -= iAdjust // take into account both days on weekend
                $("#jumlah_hari").val(iDateDiff+1);
                //return (iDateDiff + 1); // add 1 because dates are inclusive
            }
        });
    });
  }
</script>
<script type="text/javascript">
    $(document).ready(function(){
          $("#tanggal_mulai_edit").datepicker({
              todayBtn:  1,
              autoclose: true,
          }).on('changeDate', function (selected) {
            $("#tanggal_akhir_edit").prop('disabled', false);
            $("#tanggal_akhir_edit").val("");
            $("#jumlah_hari_edit").val("");
              var minDate = new Date(selected.date.valueOf());
              $("#tanggal_akhir_edit").datepicker('setStartDate', minDate);
          });

          $("#tanggal_akhir_edit").datepicker()
              .on('changeDate', function (selected) {
                  var minDate = new Date(selected.date.valueOf());
              //    $('.tgl_faktur_awal').datepicker('setEndDate', minDate);
              });
      });
</script>
<script type="text/javascript">
  function durationDayEdit(){
    $(document).ready(function() {
      $('#tanggal_mulai_edit, #tanggal_akhir_edit').on('change textInput input', function () {
            if ( ($("#tanggal_mulai_edit").val() != "") && ($("#tanggal_akhir_edit").val() != "")) {
                var dDate1 = new Date($("#tanggal_mulai_edit").val());
                var dDate2 = new Date($("#tanggal_akhir_edit").val());
                var iWeeks, iDateDiff, iAdjust = 0;
                if (dDate2 < dDate1) return -1; // error code if dates transposed
                var iWeekday1 = dDate1.getDay(); // day of week
                var iWeekday2 = dDate2.getDay();
                iWeekday1 = (iWeekday1 == 0) ? 7 : iWeekday1; // change Sunday from 0 to 7
                iWeekday2 = (iWeekday2 == 0) ? 7 : iWeekday2;
                if ((iWeekday1 > 5) && (iWeekday2 > 5)) iAdjust = 1; // adjustment if both days on weekend
                iWeekday1 = (iWeekday1 > 5) ? 5 : iWeekday1; // only count weekdays
                iWeekday2 = (iWeekday2 > 5) ? 5 : iWeekday2;

                // calculate differnece in weeks (1000mS * 60sec * 60min * 24hrs * 7 days = 604800000)
                iWeeks = Math.floor((dDate2.getTime() - dDate1.getTime()) / 604800000)

                if (iWeekday1 <= iWeekday2) {
                  iDateDiff = (iWeeks * 5) + (iWeekday2 - iWeekday1)
                } else {
                  iDateDiff = ((iWeeks + 1) * 5) - (iWeekday1 - iWeekday2)
                }

                iDateDiff -= iAdjust // take into account both days on weekend
                $("#jumlah_hari_edit").val(iDateDiff+1);
                //return (iDateDiff + 1); // add 1 because dates are inclusive
            }
        });
    });
  }
</script>
<script type="text/javascript">
    $(document).ready(function(){
          $("#tanggal_mulai").datepicker({
              todayBtn:  1,
              autoclose: true,
          }).on('changeDate', function (selected) {
            $("#tanggal_akhir").prop('disabled', false);
            $("#tanggal_akhir").val("");
            $("#jumlah_hari").val("");
              var minDate = new Date(selected.date.valueOf());
              $("#tanggal_akhir").datepicker('setStartDate', minDate);
          });

          $("#tanggal_akhir").datepicker()
              .on('changeDate', function (selected) {
                  var minDate = new Date(selected.date.valueOf());
              //    $('.tgl_faktur_awal').datepicker('setEndDate', minDate);
              });
      });
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_intervensi tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_intervensi').DataTable();

      // Apply the search
      table.columns().every( function () {
          var that = this;

          $( 'input', this.footer() ).on( 'keyup change', function () {
              if ( that.search() !== this.value ) {
                  that
                      .search( this.value )
                      .draw();
              }
          } );
      } );
  } );
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
      if (tableID=="duploaddocumentedit") {
        cell2.innerHTML = '<input type="file" name="berkas_edit[]" class="form-control" value="" accept=".png, .jpg, .pdf"/>';
      } else{
        cell2.innerHTML = '<input type="file" name="berkas[]" class="form-control" value="" accept=".png, .jpg, .pdf"/>';
      }
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

  <script>
    $(function(){
      //
      $('#keterangantambahan').hide();
      $('#jamijin').hide();
      $('#keterangantambahanedit').hide();
      $('#jamijinedit').hide();

      // add nama_atasan column to specific intervention type
      $('select#id_intervensi').on('change', function(){

        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;


        if (valueSelected==2 || valueSelected==3) {
          $('#keterangantambahan').show();
          $('#jamijin').show();
        } else if (valueSelected==13) {
          $('#keterangantambahan').show();
          $('#jamijin').hide();
        } else {
          $('#keterangantambahan').hide();
          $('#jamijin').hide();
        }
      });


      // add nama_atasan column to specific intervention type in edit modal
      $('select#jenis_intervensi_edit').on('change', function(){

        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;
        if (valueSelected==2 || valueSelected==3) {
          $('#keterangantambahanedit').show();
          $('#jamijinedit').show();
        } else if (valueSelected==13) {
          $('#keterangantambahan').show();
          $('#jamijinedit').hide();
        } else {
          $('#keterangantambahanedit').hide();
          $('#jamijinedit').hide();
        }
      });


      // preview document in modal
      $('#table_intervensi').DataTable().on('click', 'a.viewdocument[data-value]', function () {
          var a = $(this).data('value');
          var ext1 = a.split('//');
          var ext2 = ext1[1].split('/');
          var ext3 = ext2[ext2.length-1];
          var ext = ext3.split('.');
          if (ext[1]=="png" || ext[1]=="jpg" || ext[1]=="jpeg") {
            $("#previewdocument").html("<img style='max-width:100%;' src='"+a+"'>");
          } else if (ext[1]=="pdf") {

            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
              $("#previewdocument").html("<a href='"+a+"' target='_blank'>Download File</a>");
             } else {
               $("#previewdocument").html("<embed src='"+a+"' width='100%' height='700px' />");
             }
            // $("#previewdocument").html("<span href='"+a+"' class='embed'></span>");
            // $('span.embed').gdocsViewer();
          } else {
            $("#previewdocument").html("Ekstensi file tidak support!");
          }
      });
    });
  </script>
@endsection
