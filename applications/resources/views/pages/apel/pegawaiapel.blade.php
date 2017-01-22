@extends('layout.master')

@section('title')
  <title>Daftar Apel Pegawai</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')
<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Tanggal Apel</p>
        </div>
      </div>
      <form action="{{ route('pegawaiapel.store')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($tanggalApel))
        <div class="row">
          <div class="col-xs-12">
            <select name="apel_id" class="form-control select2" required="">
              <option value="">--PILIH--</option>
              @foreach ($getApel as $key)
                @if($key->id == $tanggalApel->id)
                <option value="{{ $key->id }}" selected="">{{ date("d-m-Y", strtotime($key->tanggal_apel)) }} => {{ $key->keterangan }}</option>
                @endif
                <option value="{{ $key->id }}">{{ date("d-m-Y", strtotime($key->tanggal_apel)) }} => {{ $key->keterangan }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @else
          <div class="row">
            <div class="col-xs-12">
              <select name="apel_id" class="form-control select2" required="">
                <option value="">--PILIH--</option>
                @foreach ($getApel as $key)
                <option value="{{ $key->id }}">{{ date("d-m-Y", strtotime($key->tanggal_apel)) }} => {{ $key->keterangan }}</option>
                @endforeach
              </select>
            </div>
          </div>
        @endif
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
        @if (isset($pegawainya))
          {{-- <a href="{{ route('laporan.cetakAdministrator', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR, 'skpd_id'=>$skpd_id]) }}" class="btn btn-block bg-green">Download PDF</a> --}}
        @endif
      </div>
      </form>
    </div>
  </div>
</div>


<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Jumlah Apel Pegawai Berdasarkan Golongan</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($getAbsenApel))
        <table class="table table-bordered">
          <thead>
            <tr>
              <th rowspan="2" class="text-center">No</th>
              <th rowspan="2" class="text-center">SKPD</th>
              <th rowspan="2" class="text-center">Jumlah Pegawai</th>
              <th colspan="4" class="text-center">I</th>
              <th colspan="4" class="text-center">II</th>
              <th colspan="4" class="text-center">III</th>
              <th colspan="4" class="text-center">IV</th>
            </tr>
            <tr>
              @foreach ($getGolongan as $key)
              <th class="text-center">{{ strtoupper($key->nama) }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp
            @foreach ($getSkpd as $skpd)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $skpd->nama }}</td>
              @foreach ($jumlahPegawaiSKPD as $jmlPeg)
              @if($skpd->id == $jmlPeg->skpd_id)
                <td align="center">{{ $jmlPeg->jumlah_pegawai }}</td>
              @endif
              @endforeach
              @php
                $ia = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 16) && ($apel->skpd == $skpd->id)){
                    $ia += 1;
                  }
                }
              @endphp
              <td>@if ($ia == 0) - @else {{ $ia }} @endif</td>

              @php
                $ib = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 15) && ($apel->skpd == $skpd->id)){
                    $ib += 1;
                  }
                }
              @endphp
              <td>@if ($ib == 0) - @else {{ $ib }} @endif</td>

              @php
                $ic = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 14) && ($apel->skpd == $skpd->id)){
                    $ic += 1;
                  }
                }
              @endphp
              <td>@if ($ic == 0) - @else {{ $ic }} @endif</td>

              @php
                $id = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 13) && ($apel->skpd == $skpd->id)){
                    $id += 1;
                  }
                }
              @endphp
              <td>@if ($id == 0) - @else {{ $id }} @endif</td>

              @php
                $iia = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 12) && ($apel->skpd == $skpd->id)){
                    $iia += 1;
                  }
                }
              @endphp
              <td>@if ($iia == 0) - @else {{ $iia }} @endif</td>

              @php
                $iib = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 11) && ($apel->skpd == $skpd->id)){
                    $iib += 1;
                  }
                }
              @endphp
              <td>@if ($iib == 0) - @else {{ $iib }} @endif</td>

              @php
                $iic = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 10) && ($apel->skpd == $skpd->id)){
                    $iic += 1;
                  }
                }
              @endphp
              <td>@if ($iic == 0) - @else {{ $iic }} @endif</td>

              @php
                $iid = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 9) && ($apel->skpd == $skpd->id)){
                    $iid += 1;
                  }
                }
              @endphp
              <td>@if ($iid == 0) - @else {{ $iid }} @endif</td>

              @php
                $iiia = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 8) && ($apel->skpd == $skpd->id)){
                    $iiia += 1;
                  }
                }
              @endphp
              <td>@if ($iiia == 0) - @else {{ $iiia }} @endif</td>

              @php
                $iiib = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 7) && ($apel->skpd == $skpd->id)){
                    $iiib += 1;
                  }
                }
              @endphp
              <td>@if ($iiib == 0) - @else {{ $iiib }} @endif</td>

              @php
                $iiic = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 6) && ($apel->skpd == $skpd->id)){
                    $iiic += 1;
                  }
                }
              @endphp
              <td>@if ($iiic == 0) - @else {{ $iiic }} @endif</td>

              @php
                $iiid = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 5) && ($apel->skpd == $skpd->id)){
                    $iiid += 1;
                  }
                }
              @endphp
              <td>@if ($iiid == 0) - @else {{ $iiid }} @endif</td>

              @php
                $iva = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 4) && ($apel->skpd == $skpd->id)){
                    $iva += 1;
                  }
                }
              @endphp
              <td>@if ($iva == 0) - @else {{ $iva }} @endif</td>

              @php
                $ivb = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 3) && ($apel->skpd == $skpd->id)){
                    $ivb += 1;
                  }
                }
              @endphp
              <td>@if ($ivb == 0) - @else {{ $ivb }} @endif</td>

              @php
                $ivc = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 2) && ($apel->skpd == $skpd->id)){
                    $ivc += 1;
                  }
                }
              @endphp
              <td>@if ($ivc == 0) - @else {{ $ivc }} @endif</td>

              @php
                $ivd = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->golongan == 1) && ($apel->skpd == $skpd->id)){
                    $ivd += 1;
                  }
                }
              @endphp
              <td>@if ($ivd == 0) - @else {{ $ivd }} @endif</td>
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
              <th class="text-center">No</th>
              <th class="text-center">SKPD</th>
              @foreach ($getGolongan as $key)
                <th class="text-center">{{ strtoupper($key->nama) }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="18" align="center">Pilih Tanggal Apel</td>
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
</script>
@endsection
