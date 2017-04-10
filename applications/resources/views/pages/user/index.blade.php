@extends('layout.master')

@section('title')
  <title>Kelola User</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')

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

  <div class="modal fade" id="myModalHapus" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Hapus Level Akses Akun</h4>
        </div>
        <div class="modal-body">
          <p>Apakah anda yakin untuk menghapus akses akun ini?</p>
        </div>
        <div class="modal-footer">
          <button type="reset" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <a class="btn btn-danger" id="sethapus">Ya, saya yakin</a>
        </div>
      </div>
    </div>
  </div>

<div class="row">
  <!-- START FORM-->
  <div class="col-md-4">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
          <h3 class="box-title">Tambah Akun</h3>
      </div>
      <div class="box-body">
        <form class="form-horizontal" method="post" action="{{ route('user.create') }}">
        {{ csrf_field() }}
        <div class="col-md-14 {{ $errors->has('role_id') ? 'has-error' : '' }}">
          <label class="control-label">Level Akses</label>
          <select class="form-control select2" name="role_id" id="role_id" required="">
            <option value="-- Pilih --">-- Pilih --</option>
            @if(session('status') == 'superuser')
            <option value="1" {{ old('role_id')=="1" ? 'selected' : '' }} >Administrator BKPPD</option>
            <option value="2" {{ old('role_id')=="2" ? 'selected' : '' }} >Admin SKPD</option>
            <option value="5" {{ old('role_id')=="5" ? 'selected' : '' }} >Sekretaris/TU</option>
            <option value="6" {{ old('role_id')=="6" ? 'selected' : '' }} >Admin BPKAD</option>
            @elseif(session('status') == 'administrator')
            <option value="1" {{ old('role_id')=="1" ? 'selected' : '' }} >Administrator BKPPD</option>
            <option value="2" {{ old('role_id')=="2" ? 'selected' : '' }} >Admin SKPD</option>
            <option value="5" {{ old('role_id')=="5" ? 'selected' : '' }} >Sekretaris/TU</option>
            <option value="6" {{ old('role_id')=="6" ? 'selected' : '' }} >Admin BPKAD</option>
            @elseif(session('status') == 'admin')
            <option value="2" {{ old('role_id')=="2" ? 'selected' : '' }} >Admin SKPD</option>
            @endif
          </select>
          @if($errors->has('role_id'))
            <span class="help-block">
              <i>* {{$errors->first('role_id')}}</i>
            </span>
          @endif
        </div>
        <div id="skpdoption" class="col-md-14 {{ $errors->has('pegawai_id') ? 'has-error' : '' }}">
          <label class="control-label">Nama Pegawai</label>
          <select class="form-control select2" name="pegawai_id" required="">
            <option value="-- Pilih --">-- Pilih --</option>
            @foreach($getpegawai as $key)
              <option value="{{ $key->pegawai_id }}" {{ old('pegawai_id')==$key->pegawai_id ? 'selected' : '' }}>{{ $key->nama_pegawai }} - {{ $key->nama_skpd }}</option>
            @endforeach
          </select>
          @if($errors->has('pegawai_id'))
            <span class="help-block">
              <i>* {{$errors->first('pegawai_id')}}</i>
            </span>
          @endif
        </div>
      </div>
      <div class="box-footer">
         <button type="submit" class="btn bg-purple pull-right btn-sm">Simpan</button>
      </div>
    </form>
    </div>
  </div>
  <!-- END FORM-->
  <!-- START TABLE-->
  <div class="col-md-8">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Seluruh Akun</h3>
      </div>
      <div class="box-body table-responsive">
        <table id="table_user" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width:10px;">No</th>
              <th>Level Akses</th>
              <th>Pegawai</th>
              <th>SKPD</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
          @if($getuser->isEmpty())
          {{-- <tr>
            <td colspan="7" class="text-muted" style="text-align:center;">Akun Pengelola Belum Ada.</td>
          </tr> --}}
          @else
          <?php $no = 1;?>
          @foreach($getuser as $key)
          <tr>
            <td>{{ $no }}.</td>
            <td>{{ $key->title }}</td>
            <td>{{ $key->nama_pegawai }}</td>
            <td>{{ $key->nama_skpd }}</td>
            <td>
              @if (Auth::user()->pegawai_id != $key->pegawai_id)
                  <a href="" class="btn btn-xs btn-danger hapus" data-toggle="modal" data-target="#myModalHapus" data-value="{{ $key->pegawai_id }}"><i class="fa fa-remove"></i> Hapus</a>
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
  <!-- START TABLE-->
</div>
@endsection

@section('script')
  <script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
  <script>
    $(".select2").select2();
    $(function () {
      $("#table_user").DataTable();
    });
    $('a.hapus').click(function(){
        var a = $(this).data('value');
        $('#sethapus').attr('href', "{{ url('/') }}/users/delete/"+a);
      });
  </script>
  <script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_user tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_user').DataTable();

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
