@extends('layout.master')

@section('title')
  <title>Intervensi</title>
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
    <form class="form-horizontal" action="{{ route('intervensi.post') }}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Intervensi</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('jenis_intervensi') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Jenis Intervensi</label>
            <div class="col-sm-9">
              <select class="form-control select2" name="jenis_intervensi">
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
                <input class="form-control pull-right" id="tanggal_mulai" type="text" name="tanggal_mulai"  value="{{ old('tanggal_mulai') }}" placeholder="@if($errors->has('tanggal_mulai'))
                  {{ $errors->first('tanggal_mulai')}}@endif Tanggal Mulai">
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
              <input type="text" name="jumlah_hari" id="jumlah_hari" class="form-control" value="{{ old('jumlah_hari') }}" placeholder="@if($errors->has('jumlah_hari'))
                {{ $errors->first('jumlah_hari')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Keterangan</label>
            <div class="col-sm-9">
              <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="@if($errors->has('keterangan'))
                {{ $errors->first('keterangan')}} @endif Keterangan" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('berkas') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Berkas</label>
            <div class="col-sm-9">
              <input type="file" name="berkas" class="form-control" accept=".png, .jpg, .pdf" value="{{ old('berkas') }}">
              <span style="color:red;">Hanya .jpg, .png, .pdf</span>
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
  <div class="modal-dialog" style="width:800px;">
    <form class="form-horizontal" action="{{ route('intervensi.edit') }}" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data Hari Libur & Cuti Bersama</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('id_edit') ? 'has-error' : '' }}" style="visibility: hidden;">
            <label class="col-sm-3 control-label">id</label>
            <div class="col-sm-9">
              <input type="text" name="id_edit" class="form-control" id="id_edit" value="{{ old('id_edit') }}" placeholder="@if($errors->has('id_edit'))
                {{ $errors->first('id_edit')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('jenis_intervensi_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Jenis Intervensi</label>
            <div class="col-sm-9">
              <select class="form-control select2" name="jenis_intervensi_edit">
                <option value="">-- PILIH --</option>
                <option value="Ijin" id="Ijin">Ijin</option>
                <option value="Sakit" id="Sakit">Sakit</option>
                <option value="Cuti" id="Cuti">Cuti</option>
                <option value="DinasLuar" id="DinasLuar">Dinas Luar</option>
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mulai_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Tanggal Mulai</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right tanggal_mulai_edit" id="tanggal_mulai_edit" type="text" name="tanggal_mulai_edit"  value="{{ old('tanggal_mulai_edit') }}" placeholder="@if($errors->has('tanggal_mulai_edit'))
                  {{ $errors->first('tanggal_mulai_edit')}}@endif Tanggal Mulai">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_akhir_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Tanggal Akhir</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right tanggal_akhir_edit" id="tanggal_akhir_edit" type="text" name="tanggal_akhir_edit"  value="{{ old('tanggal_akhir_edit') }}" placeholder="@if($errors->has('tanggal_akhir_edit')){{ $errors->first('tanggal_akhir_edit')}}@endif Tanggal Akhir" onchange="durationDayEdit()">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jumlah_hari_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Jumlah Hari</label>
            <div class="col-sm-9">
              <input type="text" name="jumlah_hari_edit" class="form-control" id="jumlah_hari_edit" value="{{ old('jumlah_hari_edit') }}" placeholder="@if($errors->has('jumlah_hari_edit'))
                {{ $errors->first('jumlah_hari_edit')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Keterangan</label>
            <div class="col-sm-9">
              <input type="text" name="keterangan_edit" class="form-control" id="keterangan_edit" value="{{ old('keterangan_edit') }}" placeholder="@if($errors->has('keterangan_edit'))
                {{ $errors->first('keterangan_edit')}} @endif Keterangan" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('berkas_edit') ? 'has-error' : ''}}">
            <label class="col-sm-3 control-label">Berkas</label>
            <div class="col-sm-9">
              <input type="file" name="berkas_edit" class="form-control" accept=".png, .jpg, .pdf" value="{{ old('berkas_edit') }}">
              <span style="color:red;">Hanya .jpg, .png, .pdf</br>*Kosongkan Jika Tidak Ingin Mengganti Berkas</span>
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



<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Intervensi</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahIntervensi">Tambah Intervensi</a>
        @if(session('status') != 'pegawai')
        <a href="{{ route('intervensi.kelola') }}" class="btn bg-green pull-right">Lihat Intervensi</a>
        @endif
      </div>
      <div class="box-body">
        <table id="table_intervensi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Jenis Intervensi</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Akhir</th>
              <th>Keterangan</th>
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
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($intervensi->isEmpty())
            <tr>
              <td colspan="7" align="center">Dinas Anda Belum Pernah Melakukan Intervensi </td>
            </tr>
            @else
            @foreach ($intervensi as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->jenis_intervensi }}</td>
              <td>{{ $key->tanggal_mulai }}</td>
              <td>{{ $key->tanggal_akhir }}</td>
              <td>{{ $key->deskripsi }}</td>
              <td>@if (($key->flag_status == 0) && (date('Y-m-d', strtotime($key->tanggal_akhir. ' + 3 days')) >= date('Y-m-d')))
                <small class="label label-info">Belum Disetujui</small>
              @elseif($key->flag_status == 1)
                <small class="label label-success">Sudah Disetujui</small>
              @elseif($key->flag_status == 3)
                <small class="label label-warning">Dibatalkan</small>
              @else
                <small class="label label-danger">Tidak Disetujui</small>
              @endif</td>
              <td>@if ($key->flag_status == 0)
                  @if (date('Y-m-d', strtotime($key->tanggal_akhir. ' + 3 days')) >= date('Y-m-d'))
                    <a href="" data-value="{{ $key->id }}" class="editIntervensi" data-toggle="modal" data-target="#modaleditIntervensi"><i class="fa fa-edit"></i> Ubah</a>
                    <a href="" class="batalIntervensi" data-toggle="modal" data-target="#batalIntervensi" data-value="{{ $key->id }}"><i class="fa fa-close"></i> Batal</a>
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
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script>
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
    $('.editIntervensi').click(function(){
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

          // set
          $('#id_edit').attr('value', id_edit);
          $('#jenis_intervensi_edit').attr('value', jenis_intervensi_edit);
          $('#tanggal_akhir_edit').attr('value', tanggal_akhir_edit);
          $('#tanggal_mulai_edit').attr('value', tanggal_mulai_edit);
          $('#jumlah_hari_edit').attr('value', jumlah_hari_edit);
          $('#keterangan_edit').attr('value', keterangan_edit);

          if(jenis_intervensi_edit=="Ijin")
          {
            $('#Ijin').attr('selected', 'true');
          }
          else if(jenis_intervensi_edit=="Sakit")
          {
            $('#Sakit').attr('selected', 'true');
          }
          else if(jenis_intervensi_edit=="Cuti")
          {
            $('#Cuti').attr('selected', 'true');
          }
          else if(jenis_intervensi_edit=="DinasLuar")
          {
            $('#DinasLuar').attr('selected', 'true');
          }
        }
      });
    });
  });

  $('a.batalIntervensi').click(function(){
    var a = $(this).data('value');
    $('#setBatal').attr('href', "{{ url('/') }}/intervensi/batal/"+a);
  });
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
@endsection
