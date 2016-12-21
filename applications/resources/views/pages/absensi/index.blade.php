@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')

<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih SKPD & Periode</p>
        </div>
      </div>
      <form action="{{ route('absensi.filterAdministrator') }}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($rekapAbsenPeriode))
        <div class="row">
          <div class="col-xs-6">
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                @if($key->id == $skpd_id)
                <option value="{{ $key->id }}" selected="">{{ $key->nama }}</option>
                @endif
                <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xs-3">
            <input type="text" class="form-control" name="start_date" id="start_date" value="{{ $start_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
          <div class="col-xs-3">
            <input type="text" class="form-control" name="end_date" id="end_date" value="{{ $end_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @else
          <div class="row">
            <div class="col-xs-6">
              <select name="skpd_id" class="form-control select2">
                <option value="">--PILIH--</option>
                @foreach ($getSkpd as $key)
                  <option value="{{ $key->id }}">{{ $key->nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-xs-3">
              <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
            </div>
            <div class="col-xs-3">
              <input type="text" class="form-control" name="end_date" id="end_date" value="" placeholder="dd/mm/yyyy" required="">
            </div>
          </div>
        @endif
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
        <h3 class="box-title">Detil Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($rekapAbsenPeriode))
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Terlambat</th>
              <th>Pulang Cepat</th>
              <th>Terlambat & Pulang Cepat</th>
              <th>Tanpa Keterangan/Absen</th>
              <th>Tidak Apel</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1
            @endphp
            @foreach ($rekapAbsenPeriode as $detailAbsen)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $detailAbsen->nip_sapk }}</td>
              <td>{{ $detailAbsen->nama_pegawai }}</td>
              <td>{{ $detailAbsen->Jumlah_Terlambat }}</td>
              <td>{{ $detailAbsen->Jumlah_Pulcep }}</td>
              <td>0</td>
              <td>0</td>
              <td>0</td>
            </tr>
            @php
              $no++
            @endphp
            @endforeach
          </tbody>
        </table>
        @else
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Terlambat</th>
              <th>Pulang Cepat</th>
              <th>Terlambat & Pulang Cepat</th>
              <th>Tanpa Keterangan/Absen</th>
              <th>Tidak Apel</th>
            </tr>
          </thead>
          <tbody>
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
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
$(".select2").select2();
$('#start_date').datepicker({
  autoclose: true,
  format: 'dd/mm/yyyy',
  changeMonth: true,
  changeYear: true,
  showButtonPanel: true,
});
$('#end_date').datepicker({
  autoclose: true,
  format: 'dd/mm/yyyy',
  changeMonth: true,
  changeYear: true,
  showButtonPanel: true,
});

</script>
@endsection
