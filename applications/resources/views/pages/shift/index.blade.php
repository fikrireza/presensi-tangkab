@extends('layout.master')

@section('title')
  <title>Shift SKPD</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')

@if(Session::has('berhasil'))
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 2000);
</script>
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
  <div class="col-md-8 col-md-offset-2">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih SKPD Shift</p>
        </div>
      </div>
      <form action="{{ route('shift.skpd') }}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        <div class="row">
          <div class="col-xs-12">
            <select name="skpd_id" class="form-control select2" required="">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
              <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
      </div>
      </form>
    </div>
  </div>
</div>


<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">SKPD Shift</h3>
      </div>
      <div class="box-body table-responsive">
        <table id="table_shift" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>SKPD</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($skpdShift->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($skpdShift as $key)
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
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
$(".select2").select2();
$(function () {
  $("#table_shift").DataTable();
});
</script>
@endsection
