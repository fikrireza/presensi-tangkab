@extends('layout.master')

@section('title')
  <title>Jadwal Shift - {{$tanggal}} | Presensi Online</title>
@endsection

@section('breadcrumb')
  <h1>Jadwal Shift Pegawai</h1>
  <ol class="breadcrumb">
    <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('shift.jadwal') }}">Jadwal Shift</a></li>
    <li class="active">Jadwal Shift Pegawai</li>
  </ol>
@endsection

@section('content')
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 3000);
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


@if ($getPegawai != null)
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Pilih Jam Kerja dan Pegawai</h3>
      </div>
      <div class="box-body table-responsive">
        <form class="form-horizontal" role="form" action="{{ route('shift.jadwaltanggalStore') }}" method="post">
        {{ csrf_field() }}
        <div class="row">
          <div class="col-md-6 col-md-offset-3">
            <label class="control-label">Pilih Jam Kerja</label>
            <select class="form-control select2" name="jam_kerja_id" required="">
                <option value="">--PILIH--</option>
                @foreach ($getJamKerja as $key)
                <option value="{{ $key->id }}">{{ $key->nama_jam_kerja}} - {{ $key->jam_masuk}} s/d {{ $key->jam_pulang}}</option>
                @endforeach
            </select>
            <input type="hidden" name="tanggal" value="{{ date("Y-m-d", strtotime($tanggal)) }}">
          </div>
        </div>

        <hr />

        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Pilih</th>
              <th>NIP</th>
              <th>Nama</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp
            @foreach($getPegawai as $key)
            <tr>
              <td>{{ $no }}</td>
              <td><input type="checkbox" class="minimal" name="pegawai_fid[]" value="{{$key->fid}}"></td>
              <td>{{ $key->nip_sapk }}</td>
              <td>{{ $key->nama_pegawai }}</td>
            </tr>
            @php
              $no++
            @endphp
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="box-footer">
        <button type="submit" class="btn btn-block bg-purple">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>
@endif

@if ($getPegawaiKerja != null)
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Jadwal Kerja Pegawai</h3>
      </div>
      <div class="box-body table-responsive">
        <table id="table_pegawai" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Jam Kerja</th>
              <th>Jam Masuk</th>
              <th>Jam Pulang</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($getPegawaiKerja as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nip_sapk }}</td>
              <td>{{ $key->nama }}</td>
              <td>{{ $key->nama_jam_kerja }}</td>
              <td>{{ $key->jam_masuk }}</td>
              <td>{{ $key->jam_pulang }}</td>
              <td><a href="{{ route('shift.jadwalUbah', ['id' => $key->id]) }}"><i class="fa fa-edit"></i> Ubah</a></td>
            </tr>
            <?php $no++; ?>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endif


@endsection

@section('script')
<script type="text/javascript">
$(function () {
  $("#table_pegawai").DataTable();
});
</script>

@endsection
