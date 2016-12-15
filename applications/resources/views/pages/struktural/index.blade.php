@extends('layout.master')

@section('title')
  <title>Master Struktural</title>
@endsection

@section('breadcrumb')
  <h1>Master Struktural/Eselon</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Struktural</li>
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
<div class="modal modal-default fade" id="modaltambahstruktural" role="dialog">
  <div class="modal-dialog" style="width:600px;">
    <form class="form-horizontal" action="{{ route('struktural.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Struktural</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('nama') ? 'has-error' : '' }}">
            <div class="col-sm-1"></div>
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-6">
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

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Struktural</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahstruktural">Tambah Struktural</a>
      </div>
      <div class="box-body">
        <table id="table_struktural" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            @if ($struktural->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($struktural as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
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
    $("#table_struktural").DataTable();
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#modalstruktural').modal('show');
@endif
</script>
@endsection
