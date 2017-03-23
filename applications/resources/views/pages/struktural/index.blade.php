@extends('layout.master')

@section('title')
  <title>Master Struktural</title>
@endsection

@section('breadcrumb')
  <h1>Master Struktural/Eselon</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Struktural</li>
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
<div class="modal modal-default fade" id="modaltambahstruktural" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('struktural.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Struktural</h4>
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

{{-- Modal NonAktif Struktural --}}
<div class="modal fade" id="myModalNonAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Non Aktif Struktural?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk me-non Aktifkan Struktural ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setnonaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal Aktif Struktural --}}
<div class="modal fade" id="myModalAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Aktifkan Struktural?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk Aktifkan Struktural ini?</p>
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
        <h3 class="box-title">Struktural</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahstruktural">Tambah Struktural</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_struktural" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width: 10%">No</th>
              <th>Nama</th>
              @if(session('status') == 'superuser')
              <th>Aksi</th>
              @endif
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th></th>
              <th></th>
              @if(session('status') == 'superuser')
              <th></th>
              @endif
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($struktural->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              @if(session('status') == 'superuser')
              <td></td>
              @endif
            </tr>
            @else
            @foreach ($struktural as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
              @if(session('status') == 'superuser')
              <td>
              @if ($key->status == 1)
              <span data-toggle="tooltip" title="NonAktif Struktural">
                <a href="" class="btn btn-danger btn-xs nonaktif" data-toggle="modal" data-target="#myModalNonAktif" data-value="{{ $key->id }}">NonAktif</a>
              </span>
              @else
              <span data-toggle="tooltip" title="Aktif Struktural">
                <a href="" class="btn btn-primary btn-xs aktif" data-toggle="modal" data-target="#myModalAktif" data-value="{{ $key->id }}">Aktifkan</a>
              </span>
              @endif
              </td>
              @endif
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
    $("#table_struktural").DataTable();
  });
  $('a.nonaktif').click(function(){
    var a = $(this).data('value');
    $('#setnonaktif').attr('href', "{{ url('/') }}/struktural/non/"+a);
  });
  $('a.aktif').click(function(){
    var a = $(this).data('value');
    $('#setaktif').attr('href', "{{ url('/') }}/struktural/aktif/"+a);
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#modalstruktural').modal('show');
@endif
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_struktural tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_struktural').DataTable();

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
