@extends('layout.master')

@section('title')
  <title>Revisi Presensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Revisi Presensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('revisiintervensi.index') }}">Revisi Presensi</a></li>
    <li class="active">Tambah Revisi</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Tambah Data Revisi Presensi</h3>
        <a href="{{ route('revisiintervensi.index') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form action="{{ route('revisiintervensi.caripegawai') }}" method="post" class="form-horizontal" role="search">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('skpd') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">SKPD</label>
            <div class="col-sm-8">
              <select name="skpd" class="form-control select2">
                <option value="">-- Pilih --</option>
                  @foreach ($getskpd as $key)
                    <option value="{{ $key->id }}" @if($key->id == $skpd_id) selected="" @endif>{{ $key->nama }}</option>
                  @endforeach
              </select>
                @if($errors->has('skpd'))
                  <span class="help-block">
                    <strong>{{ $errors->first('skpd')}}
                    </strong>
                  </span>
                @endif
            </div>
            <button type="submit" class="btn bg-green pull-left">Cari Pegawai</button>
          </div>
        </div>
     </form>
    <form class="form-horizontal" role="form" action="{{ route('revisiintervensi.createStore') }}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="col-md-10">
              <div class="box-body table-responsive box box-primary box-solid">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>
                        <span data-toggle="tooltip" data-placement="right" title="Pilih Semua">
                          <input type="checkbox" onClick="toggle(this)" />
                        </span>
                      </th>
                      <th>NIP</th>
                      <th>Nama</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($getcaripegawai == null)
                    <tr>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                    @else
                      @foreach($getcaripegawai as $key)
                      <tr>
                        <td><input type="checkbox" class="minimal" name="idpegawai[]" value="{{$key->id}}"></td>
                        <td>{{ $key->nip_sapk }}</td>
                        <td>{{ $key->nama }}</td>
                      </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
                @if(Session::has('gagal'))
                  <div class="row">
                    <div class="col-md-12">
                      <span style="color:red;">{{ Session::get('gagal') }}</span>
                    </div>
                  </div>
                @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mulai') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tanggal Mulai</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_mulai" type="text" name="tanggal_mulai" 
                 value="{{ old('tanggal_mulai') }}" placeholder="@if($errors->has('tanggal_mulai'))
                  {{ $errors->first('tanggal_mulai')}}@endif Tanggal Awal">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_akhir') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tanggal Akhir</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_akhir" type="text" name="tanggal_akhir" 
                 value="{{ old('tanggal_akhir') }}" placeholder="@if($errors->has('tanggal_akhir'))
                  {{ $errors->first('tanggal_akhir')}}@endif Tanggal Akhir" onchange="durationDay()">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jumlah_hari') ? 'has-error' : '' }}" hidden="true">
            <label class="col-sm-2 control-label">Jumlah Hari</label>
            <div class="col-sm-10">
              <input type="text" name="jumlah_hari" id="jumlah_hari" class="form-control" value="{{ old('jumlah_hari') }}" placeholder="@if($errors->has('jumlah_hari'))
                {{ $errors->first('jumlah_hari')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Keterangan</label>
            <div class="col-sm-10">
              <textarea name="keterangan" class="form-control" rows="5" cols="40" placeholder="@if($errors->has('keterangan'))
                {{ $errors->first('keterangan')}}@endif Keterangan ">{{ old('keterangan') }}</textarea>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">Status Jam</label>
            <div class="col-sm-2">
              <input type="checkbox" class="flat-red" name="status_jam_datang">
              <span class="text-muted"><b style="color: #333333">Jam Datang</b></span>
            </div>
            <div class="col-sm-2">
              <input type="checkbox" class="flat-red" name="status_jam_pulang">
              <span class="text-muted"><b style="color: #333333">Jam Pulang</b></span>
            </div>
          </div>
          <div class="form-group {{ $errors->has('upload_revisi') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Upload Document</label>
            <div class="col-sm-10">
              <input type="file" name="upload_revisi" class="form-control {{ $errors->has('upload_revisi') ? 'has-error' : '' }}" accept=".png, .jpg, .pdf">
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
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
var today = new Date();
// var startDate = new Date(today.getFullYear(), today.getMonth(), 1);
// var endDate = new Date(today.getFullYear(), today.getMonth()+1, 0);
// date.setDate(date.getDate()-3);
  $(".select2").select2();
  $(function () {
    $("#table_revisi").DataTable();
  });
  $('#tanggal_mulai').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    // startDate: startDate,
    // endDate: endDate,
    todayHighlight: true,
    daysOfWeekDisabled: [0,6]
  });
  $('#tanggal_akhir').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    // startDate: startDate,
    // endDate: endDate,
    todayHighlight: true,
    daysOfWeekDisabled: [0,6]
});
</script>
<script type="text/javascript">
  function toggle(pilih) {
  checkboxes = document.getElementsByName('idpegawai[]');
  for(var i=0, n=checkboxes.length;i<n;i++) {
    checkboxes[i].checked = pilih.checked;
  }
} 
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
      $('#table_revisi tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_revisi').DataTable();

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
<script>
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
