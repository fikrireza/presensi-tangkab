@extends('layout.master')

@section('title')
  <title>Reset Password</title>
@endsection

@section('breadcrumb')
  <h1>Reset Password</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('user.index') }}">Kelola User</a></li>
    <li class="active">Reset Password</li>
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

<div class="modal fade" id="myModalReset" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Reset Password Akun Ini</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk reset password akun ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger" id="setreset">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Semua Karyawan</h3>
      </div>
      <div class="box-body table-responsive">
        <table id="table_user" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>SKPD</th>
              <th>Level Akses</th>
              <th>Reset</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getuser->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($getuser as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nip_sapk }}</td>
              <td>{{ $key->nama_pegawai }}</td>
              <td>{{ $key->nama_skpd }}</td>
              <td>{{ $key->title }}</td>
              <td>
                <a href="" class="btn btn-xs btn-danger reset" data-toggle="modal" data-target="#myModalReset" data-value="{{ $key->pegawai_id }}"><i class="fa fa-refresh"></i> Reset Password</a>
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
  $(function () {
    $("#table_user").DataTable();
  });
  $('a.reset').click(function(){
    var a = $(this).data('value');
    $('#setreset').attr('href', "{{ url('/') }}/users/reset/"+a);
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
