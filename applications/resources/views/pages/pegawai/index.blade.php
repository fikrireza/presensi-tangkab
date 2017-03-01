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
        <h3 class="box-title">Pegawai</h3>
        <a href="{{ route('pegawai.create') }}" class="btn bg-blue pull-right">Tambah Pegawai</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_pegawai" class="table table-bordered table-striped">
      		<thead>
      			<tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>SKPD</th>
              <th>Golongan</th>
              <th>Jabatan</th>
              <th>Struktural</th>
              <th>Finger ID</th>
              <th style="width: 10%">Aksi</th>
      			</tr>
      		</thead>
      	</table>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
$(function() {
    var table = $("#table_pegawai").DataTable({
        processing: true,
        serverSide: true,
        "responsive": true,
        ajax: "{{ url('getPegawai') }}",
        columns: [
          { data: 'no' },
          { data: 'nip_sapk' },
          { data: 'fid' },
          { data: 'nama_pegawai' },
          { data: 'jabatan' },
          { data: 'nama_skpd' },
          { data: 'nama_golongan' },
          { data: 'nama_struktural' },
          { data: 'action', 'searchable': false, 'orderable':false }
       ]
    });
});
</script>
@endsection
