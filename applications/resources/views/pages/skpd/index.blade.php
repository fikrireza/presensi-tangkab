@extends('layout.master')

@section('title')
  <title>Master SKPD</title>
@endsection

@section('breadcrumb')
  <h1>Master SKPD</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">SKPD</li>
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


{{-- Modal Tambah Skpd--}}
<div class="modal modal-default fade" id="modaltambahskpd" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('skpd.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah SKPD</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('nama') ? 'has-error' : '' }}">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="@if($errors->has('nama'))
                {{ $errors->first('nama')}} @endif Nama" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('singkatan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Singkatan</label>
            <div class="col-sm-9">
              <input type="text" name="singkatan" class="form-control" value="{{ old('singkatan') }}" placeholder="@if($errors->has('singkatan'))
                {{ $errors->first('singkatan')}} @endif Singkatan">
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
<div class="modal modal-default fade" id="modaleditSKPD" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('skpd.edit') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data SKPD</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <input type="text" name="nama_skpd" class="form-control" id="nama_skpd" required>
              <input type="hidden" name="id_skpd" class="form-control" id="id" required>
              <input type="hidden" name="user_id" class="form-control" value="1" required>
            </div>
          </div>
          <div class="form-group {{ $errors->has('singkatan_skpd') ? 'has-error' : '' }}">
            <label class="col-sm-3">Singkatan</label>
            <div class="col-sm-9">
              <input type="text" name="singkatan_skpd" class="form-control" id="singkatan_skpd" value="{{ old('singkatan_skpd') }}" placeholder="@if($errors->has('singkatan_skpd'))
                {{ $errors->first('singkatan_skpd')}} @endif Singkatan">
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

{{-- Modal NonAktif SKPD --}}
<div class="modal fade" id="myModalNonAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Non Aktif SKPD?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk me-non Aktifkan SKPD ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setnonaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal Aktif SKPD --}}
<div class="modal fade" id="myModalAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Aktifkan SKPD?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk Aktifkan SKPD ini?</p>
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
        <h3 class="box-title">SKPD</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahskpd">Tambah SKPD</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_skpd" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Singkatan</th>
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
              @if(session('status') == 'superuser')
              <th></th>
              @endif
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($skpd as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
              <td>@if($key->singkatan == null) - @else {{ $key->singkatan }} @endif</td>
              @if(session('status') == 'superuser')
              <td>{{ $key->actor }}</td>
              @endif
              <td>
              @if ($key->status == 1)
                <a href="" data-value="{{ $key->id }}" class="btn btn-xs btn-warning editSKPD" data-toggle="modal" data-target="#modaleditSKPD"><i class="fa fa-edit"></i> Ubah</a>
              @if (session('status') == 'superuser')
                <a href="" class="btn btn-xs btn-danger nonaktif" data-toggle="modal" data-target="#myModalNonAktif" data-value="{{ $key->id }}">NonAktif</a>
              @endif
              @else
              @if (session('status') == 'superuser')
                <a href="" class="btn btn-xs btn-primary aktif" data-toggle="modal" data-target="#myModalAktif" data-value="{{ $key->id }}">Aktifkan</a>
              @endif
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
<script>
  $(function () {
    $("#table_skpd").DataTable();
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#modaltambahskpd').modal('show');
@endif
$('a.nonaktif').click(function(){
  var a = $(this).data('value');
  $('#setnonaktif').attr('href', "{{ url('/') }}/skpd/non/"+a);
});
$('a.aktif').click(function(){
  var a = $(this).data('value');
  $('#setaktif').attr('href', "{{ url('/') }}/skpd/aktif/"+a);
});
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_skpd tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_skpd').DataTable();

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

<script type="text/javascript">
  $(function(){
    $("#table_skpd").on("click", "a.editSKPD", function(){
      var a = $(this).data('value');
      $.ajax({
        url: "{{ url('/') }}/skpd/"+a,
        dataType: 'json',
        success: function(data){
          var id = data.id;
          var nama_skpd = data.nama;
          var singkatan_skpd = data.singkatan;

          // set
          $('#id').attr('value', id);
          $('#nama_skpd').attr('value', nama_skpd);
          $('#singkatan_skpd').attr('value', singkatan_skpd);
        }
      });
    });
  });
</script>

@endsection
