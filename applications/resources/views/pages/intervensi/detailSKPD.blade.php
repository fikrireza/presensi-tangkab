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
    <form class="form-horizontal" action="{{ route('intervensi.kelola.post') }}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Intervensi</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('pegawai_id') ? 'has-error' : '' }}">
            <label class="col-md-3">Pegawai</label>
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
            <label class="col-sm-3">Jenis Intervensi</label>
            <div class="col-sm-9">
              <select class="form-control select2" name="jenis_intervensi" style="width:100%;" id="id_intervensi">
                <option value="">-- PILIH --</option>
                @foreach ($getmasterintervensi as $key)
                  <option value="{{$key->id}}">{{$key->nama_intervensi}}</option>
                @endforeach
                {{-- <option value="Ijin" {{ old('jenis_interrvensi') == 'Ijin' ? 'selected' : ''}}>Ijin</option>
                <option value="Sakit" {{ old('jenis_intervensi') == 'Sakit' ? 'selected' : ''}}>Sakit</option>
                <option value="Cuti" {{ old('jenis_intervensi') == 'Cuti' ? 'selected' : ''}}>Cuti</option>
                <option value="DinasLuar" {{ old('jenis_intervensi') == 'DinasLuar' ? 'selected' : ''}}>Dinas Luar</option> --}}
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
                <input class="form-control pull-right" id="tanggal_akhir" type="text" name="tanggal_akhir"  value="{{ old('tanggal_akhir') }}" placeholder="@if($errors->has('tanggal_akhir'))
                  {{ $errors->first('tanggal_akhir')}}@endif Tanggal Akhir" onchange="durationDay()">
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
                {{ $errors->first('keterangan')}} @endif Keterangan" required="">
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
                  <input type='text' name='jam_ijin' class='form-control'>
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

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Kelola Intervensi</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahIntervensi">Tambah Intervensi Pegawai</a>
      </div>
      <div class="box-body">
        <table id="table_intervensi" class="table table-bordered table-striped">
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
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($intervensi->isEmpty())
            <tr>
              <td colspan="7" align="center"> Tidak ada Intervensi</td>
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
                <small class="label label-info">Belum Ditanggapi</small>
              @elseif($key->flag_status == 1)
                <small class="label label-success">Sudah Disetujui</small>
              @else
                <small class="label label-danger">Tidak Disetujui</small>
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

    });
  </script>

@endsection
