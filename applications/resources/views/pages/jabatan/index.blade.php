@extends('layout.master')

@section('title')
  <title>Master Jabatan</title>
@endsection

@section('breadcrumb')
  <h1>Master Jabatan</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Jabatan</li>
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
    <form class="form-horizontal" action="{{ route('jabatan.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Jabatan</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('nama') ? 'has-error' : '' }}">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="@if($errors->has('nama')){{ $errors->first('nama')}} @endif Nama" required="">
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

{{-- Modal Edit SKPD --}}
<div class="modal modal-default fade" id="modaleditJabatan" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('jabatan.edit') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data Jabatan</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama_jabatan" class="form-control" id="nama_jabatan" required>
              <input type="hidden" name="id_jabatan" class="form-control" id="id_jabatan" required>
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
        <h3 class="box-title">Jabatan</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahgolongan">Tambah Jabatan</a>
      </div>
      <div class="box-body">
        <table id="table_jabatan" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($jabatan->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($jabatan as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
              <td><a href="" data-value="{{ $key->id }}" class="btn btn-xs btn-warning editJabatan" data-toggle="modal" data-target="#modaleditJabatan"><i class="fa fa-edit"></i> Ubah</a></td>
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
    $("#table_jabatan").DataTable();
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#modaltambahjabatan').modal('show');
@endif
</script>

<script type="text/javascript">
  $(function(){
    $("#table_jabatan").on("click", "a.editJabatan", function(){
      var a = $(this).data('value');
      $.ajax({
        url: "{{ url('/') }}/jabatan/"+a,
        dataType: 'json',
        success: function(data){
          var id_jabatan = data.id;
          var nama_jabatan = data.nama;

          // set
          $('#id_jabatan').attr('value', id_jabatan);
          $('#nama_jabatan').attr('value', nama_jabatan);
        }
      });
    });
  });
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_jabatan tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_jabatan').DataTable();

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
