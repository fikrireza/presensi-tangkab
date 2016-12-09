@extends('layout.master')

@section('title')
  <title>Absensi</title>
@endsection

@section('breadcrumb')
  <h1>Absensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Absensi</li>
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
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Hari</th>
              <th>Tanggal</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @if ($absensi->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($absensi as $key)
            @if($key->Jam_Log > '15:00:00')
            <tr class="table-active">
            @endif
              <td>{{ $no }}</td>
              <td>{{ date('l', strtotime($key->Tanggal_Log)) }}</td>
              <td>{{ $key->Tanggal_Log }}</td>
              <td>{{ $key->Jam_Log }}</td>
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
