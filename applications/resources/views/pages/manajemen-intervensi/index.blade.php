@extends('layout.master')

@section('title')
  <title>Master Intervensi</title>
@endsection

@section('breadcrumb')
  <h1>Master Intervensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Master Intervensi</li>
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
    <form class="form-horizontal" action="{{route('manajemenintervensi.store')}}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Master Intervensi</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('nama_intervensi') ? 'has-error' : '' }}">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama" class="form-control" value="{{ old('nama_intervensi') }}" placeholder="@if($errors->has('nama_intervensi')){{ $errors->first('nama_intervensi')}} @endif Nama" required="">
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
        <h3 class="box-title">Master Intervensi</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahgolongan">Tambah Master Intervensi</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_golongan" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width: 10%">No</th>
              <th>Nama</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getintervensi->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($getintervensi as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama_intervensi }}</td>
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
    $("#table_golongan").DataTable();
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
