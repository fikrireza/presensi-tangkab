@extends('layout.master')

@section('title')
  <title>Manajemen Apel</title>
@endsection

@section('breadcrumb')
  <h1>Manajemen Apel</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Manajemen Apel</li>
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


{{-- Modal Tambah Apel--}}
<div class="modal modal-default fade" id="modaltambahApel" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('apel.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Hari Apel</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('tanggal_apel') ? 'has-error' : '' }}">
            <label class="col-sm-3">Tanggal Apel</label>
            <div class="col-sm-9">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_apel" type="text" name="tanggal_apel"  value="{{ old('tanggal_apel') }}" placeholder="@if($errors->has('tanggal_apel')){{ $errors->first('tanggal_apel')}}@endif Tanggal Apel">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Keterangan</label>
            <div class="col-sm-9">
              <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="@if($errors->has('keterangan')){{ $errors->first('keterangan')}} @endif Keterangan" required="">
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

{{-- Modal Edit Hari Apel --}}
<div class="modal modal-default fade" id="modaleditApel" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('apel.edit') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data Hari Apel</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('tanggal_apel_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3">Hari Apel</label>
            <div class="col-sm-6">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right tanggal_apel_edit" id="tanggal_apel_edit" type="text" name="tanggal_apel_edit" value="{{ old('tanggal_apel_edit') }}" placeholder="@if($errors->has('tanggal_apel_edit')){{ $errors->first('tanggal_apel_edit')}}@endif Hari Apel">
                <input type="hidden" name="id" id="id">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan_edit') ? 'has-error' : '' }}">
            <label class="col-sm-3">Keterangan</label>
            <div class="col-sm-6">
              <input type="text" name="keterangan_edit" class="form-control" id="keterangan_edit" value="{{ old('keterangan_edit') }}" placeholder="@if($errors->has('keterangan_edit')){{ $errors->first('keterangan_edit')}} @endif Keterangan" required="">
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

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Hari Apel</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahApel">Tambah Hari Apel</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_apel" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Hari</th>
              <th>Tanggal</th>
              <th>Keterangan</th>
              @if(session('status') == 'superuser')
              <th>Aktor</th>
              @endif
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              @if(session('status') == 'superuser')
              <th></th>
              @endif
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getApel->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($getApel as $key)
            <tr>
              <td>{{ $no }}</td>
              <?php
                $day = explode('-', $key->tanggal_apel);
                $day = $day[1]."/".$day[2]."/".$day[0];
                $day = date('D', strtotime($day));

                $dayList = array(
                	'Sun' => 'Minggu',
                	'Mon' => 'Senin',
                	'Tue' => 'Selasa',
                	'Wed' => 'Rabu',
                	'Thu' => 'Kamis',
                	'Fri' => 'Jum&#039;at',
                	'Sat' => 'Sabtu'
                );
                 ?>
              <td>{{ $dayList[$day] }}</td>
              <td>@php
                  $day = explode('-', $key->tanggal_apel);
                  $tanggal_apel = $day[2]."/".$day[1]."/".$day[0];
              @endphp{{ $tanggal_apel }}</td>
              <td>{{ $key->keterangan }}</td>
              @if(session('status') == 'superuser')
              <td>{{ $key->actor }}</td>
              @endif
              <td>@if($key->tanggal_apel >= date('Y-m-d'))
                <a href="" data-value="{{ $key->id }}" class="editApel" data-toggle="modal" data-target="#modaleditApel"><i class="fa fa-edit"></i> Ubah</a>
              @else
                -
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
<script>
$(function () {
  $("#table_apel").DataTable();
});
$('#tanggal_apel').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  todayHighlight: true,
  daysOfWeekDisabled: [0,6]
});
$('.tanggal_apel_edit').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  todayHighlight: true,
  daysOfWeekDisabled: [0,6]
});
</script>

<script type="text/javascript">
@if ($errors->has('tanggal_apel') || $errors->has('keterangan'))
  $('#modaltambahApel').modal('show');
@endif
@if ($errors->has('tanggal_apel_edit') || $errors->has('keterangan_edit'))
  $('#modaleditApel').modal('show');
@endif
</script>

<script type="text/javascript">
  $(function(){
    $("#table_apel").on("clicl", "a.editApel", function(){
      var a = $(this).data('value');
      $.ajax({
        url: "{{ url('/') }}/apel/"+a,
        dataType: 'json',
        success: function(data){
          var id = data.id;
          var tanggal_apel_edit = data.tanggal_apel;
          var keterangan_edit = data.keterangan;

          // set
          $('#id').attr('value', id);
          $('#tanggal_apel_edit').attr('value', tanggal_apel_edit);
          $('#keterangan_edit').attr('value', keterangan_edit);
        }
      });
    });
  });
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_apel tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_apel').DataTable();

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
