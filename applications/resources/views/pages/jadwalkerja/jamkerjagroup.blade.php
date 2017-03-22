@extends('layout.master')

@section('title')
  <title>Group Kerja</title>
@endsection

@section('breadcrumb')
  <h1>Group Kerja</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('jadwal-kerja') }}">Jadwal Jam Kerja</a></li>
    <li class="active">Group Jam Kerja</li>
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

{{-- Modal NonAktif Group Kerja --}}
<div class="modal fade" id="myModalNonAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Non Aktif Group Kerja?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk me-non Aktifkan Group Kerja ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setnonaktif">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal Aktif Group Kerja --}}
<div class="modal fade" id="myModalAktif" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Aktifkan Group Kerja?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk Aktifkan Group Kerja ini?</p>
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
        <h3 class="box-title">Group Kerja</h3>
        <a href="{{ route('jadwal-kerja.tambahgroup') }}" class="btn bg-blue pull-right">Tambah Group Kerja</a>
        <a href="{{ route('jadwal-kerja.jam') }}" class="btn bg-green pull-right">Lihat Jam Kerja</a>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Group Kerja</th>
              <th>Nama Jam Kerja</th>
              <th>Jadwal Kerja</th>
              @if (session('status') == 'superuser')
              <th>Aktor</th>
              @endif
              <th>Aksi</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <td></td>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($getJamGroup as $key)
            <tr>
              <td>{{ $no }}</td>
              <td><a href="{{ url('jadwal-kerja-group/lihat')."/". $key->group_id }}">{{ $key->nama_group }}</a></td>
              <td>{{ $key->nama_jam }}</td>
              <td>{{ $key->jam_masuk }} s/d {{ $key->jam_pulang }}</td>
              @if (session('status') == 'superuser')
              <td>{{ $key->actor }}</td>
              @endif
              <td>@if ($key->flag_status == 1)
              @if (session('status') == 'administrator' || session('status') == 'superuser')
                <a href="" class="btn btn-xs btn-danger nonaktif" data-toggle="modal" data-target="#myModalNonAktif" data-value="{{ $key->id }}">NonAktif</a>
              @endif
              @else
              @if (session('status') == 'administrator' || session('status') == 'superuser')
                <a href="" class="btn btn-xs btn-primary aktif" data-toggle="modal" data-target="#myModalAktif" data-value="{{ $key->id }}">Aktifkan</a>
              @endif
              @endif</td>
            </tr>
            <?php $no++; ?>
            @endforeach
          </tbody>
        </table>
        <p><span class="help-block">*Klik Nama Group Untuk Menambah Jam Kerja</span></p>
      </div>
    </div>
  </div>
</div>


@endsection

@section('script')
<script>
$(function () {
  $("#table_jadwal_kerja").DataTable();
});

$('a.nonaktif').click(function(){
  var a = $(this).data('value');
  $('#setnonaktif').attr('href', "{{ url('/') }}/jadwal-kerja-group/non/"+a);
});
$('a.aktif').click(function(){
  var a = $(this).data('value');
  $('#setaktif').attr('href', "{{ url('/') }}/jadwal-kerja-group/aktif/"+a);
});
</script>
@endsection
