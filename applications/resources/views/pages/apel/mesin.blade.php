@extends('layout.master')

@section('title')
  <title>Master Mesin Apel</title>
@endsection

@section('breadcrumb')
  <h1>Master Mesin Apel</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Mesin Apel</li>
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


{{-- Modal Tambah mesinapel--}}
<div class="modal modal-default fade" id="modaltambahmesinapel" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('mesin.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Mesin Apel</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('mach_id') ? 'has-error' : '' }}">
            <label class="col-sm-3">Mesin ID</label>
            <div class="col-sm-9">
              <input type="text" name="mach_id" class="form-control" value="{{ old('mach_id') }}" placeholder="@if($errors->has('mach_id'))
                {{ $errors->first('mach_id')}} @endif Nomor Mesin" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('catatan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Catatan</label>
            <div class="col-sm-9">
              <input type="text" name="catatan" class="form-control" value="{{ old('catatan') }}" placeholder="@if($errors->has('catatan'))
                {{ $errors->first('catatan')}} @endif Catatan" required="">
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
        <h3 class="box-title">Mesin Apel</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahmesinapel">Tambah Mesin Apel</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_mesin" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Mesin ID</th>
              <th>Catatan</th>
              @if (session('status') == 'superuser')
              <th>Aktor</th>
              @endif
              <th>Status</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              @if (session('status') == 'superuser')
              <th></th>
              @endif
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getMesin->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($getMesin as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->mach_id }}</td>
              <td>{{ $key->catatan }}</td>
              @if (session('status') == 'superuser')
              <th>{{ $key->actor }}</th>
              @endif
              <td>@if($key->flag_status == 1)
                Aktif
                @else
                  Tidak Aktif
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
    $("#table_mesin").DataTable();
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#table_mesin').modal('show');
@endif
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_mesin tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_mesin').DataTable();

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
