@extends('layout.master')

@section('title')
  <title>Master Golongan</title>
@endsection

@section('breadcrumb')
  <h1>Master Golongan</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Golongan</li>
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


{{-- Modal Tambah Golongan--}}
<div class="modal modal-default fade" id="modaltambahgolongan" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('golongan.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Golongan</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('nama') ? 'has-error' : '' }}">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="@if($errors->has('nama'))
                {{ $errors->first('nama')}} @endif Nama" required="">
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

{{-- Modal NonAktif Golongan --}}
<div class="modal fade" id="myModalNonAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Non Aktif Golongan?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk me-non Aktifkan Golongan ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setnonaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal Aktif Golongan --}}
<div class="modal fade" id="myModalAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Aktifkan Golongan?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk Aktifkan Golongan ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Golongan</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahgolongan">Tambah Golongan</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_golongan" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width: 10%">No</th>
              <th>Nama</th>
              @if (session('status') == 'superuser')
              <th>Aktor</th>
              <th>Aksi</th>
              @endif
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              @if (session('status') == 'superuser')
              <th></th>
              <th></th>
              @endif
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($golongan as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
              @if(session('status') == 'superuser')
              <td>{{ $key->actor }}</td>
              <td>
                @if ($key->status == 1)
                <span data-toggle="tooltip" title="NonAktif Golongan">
                  <a href="" class="btn btn-danger btn-xs nonaktif" data-toggle="modal" data-target="#myModalNonAktif" data-value="{{ $key->id }}">NonAktif</a>
                </span>
                @else
                <span data-toggle="tooltip" title="Aktif Golongan">
                  <a href="" class="btn btn-primary btn-xs aktif" data-toggle="modal" data-target="#myModalAktif" data-value="{{ $key->id }}">Aktifkan</a>
                </span>
                @endif
              </td>
              @endif
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
<script>
  $(function () {
    $("#table_golongan").DataTable();
  });
  $('a.nonaktif').click(function(){
    var a = $(this).data('value');
    $('#setnonaktif').attr('href', "{{ url('/') }}/golongan/non/"+a);
  });
  $('a.aktif').click(function(){
    var a = $(this).data('value');
    $('#setaktif').attr('href', "{{ url('/') }}/golongan/aktif/"+a);
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#modaltambahskpd').modal('show');
@endif
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_golongan tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_golongan').DataTable();

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
