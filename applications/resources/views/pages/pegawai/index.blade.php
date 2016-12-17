@extends('layout.master')

@section('title')
  <title>Master Pegawai</title>
@endsection

@section('breadcrumb')
  <h1>Master Pegawai</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Pegawai</li>
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
        <h3 class="box-title">Struktural</h3>
        <a href="{{ route('pegawai.create') }}" class="btn bg-blue pull-right">Tambah Pegawai</a>
      </div>
      <div class="box-body">
        <table id="table_pegawai" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>SKPD</th>
              <th>Golongan</th>
              <th>Jabatan</th>
              <th>Struktural</th>
              <th>Finger ID</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @if ($pegawai->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($pegawai as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama_pegawai }}</td>
              <td>{{ $key->nama_skpd }}</td>
              <td>{{ $key->nama_golongan }}</td>
              <td>{{ $key->jabatan }}</td>
              <td>{{ $key->nama_struktural }}</td>
              <td>{{ $key->fid }}</td>
              <td><a href="{{ url('pegawai/edit', $key->id) }}"><i class="fa fa-edit"></i> Ubah</a></td>
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
    $("#table_pegawai").DataTable();
  });
</script>
@endsection
