@extends('layout.master')

@section('title')
  <title>Jam Kerja SKPD | Presensi Online</title>
@endsection

@section('breadcrumb')
  <h1>Jam Kerja SKPD</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('jadwal-kerja') }}">Jadwal Jam Kerja</a></li>
    <li><a href="{{ route('jadwal-kerja.group') }}">Group Jam Kerja</a></li>
    <li class="active">Jam Kerja</li>
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

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Jam Kerja</h3>
        <a href="{{ route('jadwal-kerja.tambahjam') }}" class="btn bg-green pull-right">Tambah Jam Kerja</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_jadwal_kerja" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Jam Kerja</th>
              <th>Jam Masuk</th>
              <th>Jam Masuk Awal</th>
              <th>Jam Masuk Akhir</th>
              <th>Jam Pulang</th>
              <th>Jam Pulang Awal</th>
              <th>Jam Pulang Akhir</th>
              <th>Toleransi Terlambat (min)</th>
              <th>Toleransi Pulcep (min)</th>
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
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($getJamKerja as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama_jam_kerja }}</td>
              <td>{{ $key->jam_masuk }}</td>
              <td>{{ $key->jam_masuk_awal }}</td>
              <td>{{ $key->jam_masuk_akhir }}</td>
              <td>{{ $key->jam_pulang }}</td>
              <td>{{ $key->jam_pulang_awal }}</td>
              <td>{{ $key->jam_pulang_akhir }}</td>
              <td>{{ $key->toleransi_terlambat }}</td>
              <td>{{ $key->toleransi_pulcep }}</td>
              @if (session('status') == 'superuser')
              <td>{{ $key->actor }}</td>
              @endif
              <td><a href="{{ url('jam-kerja/ubah').'/'. $key->id }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Ubah</a></td>
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
    $("#table_jadwal_kerja").DataTable();
  });
</script>
@endsection
