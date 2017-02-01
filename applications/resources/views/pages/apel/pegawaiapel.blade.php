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
        @if (isset($tanggalApel))
          <a href="{{ route('pegawaiapel.cetak', ['download'=>'pdf', 'apel_id'=>$tanggalApel->id]) }}" class="btn btn-block bg-green">Download PDF</a>
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
        <h3 class="box-title">Jumlah Apel Pegawai Berdasarkan Esselon</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($getAbsenApel))
        <table class="table table-bordered">
          <thead>
            <tr>
              <th rowspan="2" class="text-center">No</th>
              <th rowspan="2" class="text-center">SKPD</th>
              <th rowspan="2" class="text-center">Jumlah Pegawai</th>
              <th colspan="2" class="text-center">5</th>
              <th colspan="2" class="text-center">4</th>
              <th colspan="2" class="text-center">3</th>
              <th colspan="2" class="text-center">2</th>
              <th colspan="2" class="text-center"></th>
              <th rowspan="2" class="text-center">Tidak Apel</th>
            </tr>
            <tr>
              @foreach ($getStruktural as $key)
              <th class="text-center">{{ $key->nama }}</th>
              @endforeach
              <th></th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp
            @foreach ($getSkpd as $skpd)
            <tr>
              <td>{{ $no }}</td>
              <td><a href="{{ route('pegawaiapel.detail', ['skpd' => $skpd->id, 'tanggal_apel' => $tanggalApel->id])}}">{{ $skpd->nama }}</a></td>
              @foreach ($jumlahPegawaiSKPD as $jmlPeg)
              @if($skpd->id == $jmlPeg->skpd_id)
                <td align="center">{{ $jmlPeg->jumlah_pegawai }}</td>
                @php
                  $jumlah_pegawai = $jmlPeg->jumlah_pegawai
                @endphp
              @endif
              @endforeach
              @php
                $ia = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 1) && ($apel->skpd == $skpd->id)){
                    $ia += 1;
                  }
                }
              @endphp
              <td>@if ($ia == 0) - @else {{ $ia }} @endif</td>

              @php
                $ib = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 2) && ($apel->skpd == $skpd->id)){
                    $ib += 1;
                  }
                }
              @endphp
              <td>@if ($ib == 0) - @else {{ $ib }} @endif</td>

              @php
                $ic = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 3) && ($apel->skpd == $skpd->id)){
                    $ic += 1;
                  }
                }
              @endphp
              <td>@if ($ic == 0) - @else {{ $ic }} @endif</td>

              @php
                $id = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 4) && ($apel->skpd == $skpd->id)){
                    $id += 1;
                  }
                }
              @endphp
              <td>@if ($id == 0) - @else {{ $id }} @endif</td>

              @php
                $iia = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 5) && ($apel->skpd == $skpd->id)){
                    $iia += 1;
                  }
                }
              @endphp
              <td>@if ($iia == 0) - @else {{ $iia }} @endif</td>

              @php
                $iib = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 6) && ($apel->skpd == $skpd->id)){
                    $iib += 1;
                  }
                }
              @endphp
              <td>@if ($iib == 0) - @else {{ $iib }} @endif</td>

              @php
                $iic = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 7) && ($apel->skpd == $skpd->id)){
                    $iic += 1;
                  }
                }
              @endphp
              <td>@if ($iic == 0) - @else {{ $iic }} @endif</td>

              @php
                $iid = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 8) && ($apel->skpd == $skpd->id)){
                    $iid += 1;
                  }
                }
              @endphp
              <td>@if ($iid == 0) - @else {{ $iid }} @endif</td>

              @php
                $iiia = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 9) && ($apel->skpd == $skpd->id)){
                    $iiia += 1;
                  }
                }
              @endphp
              <td>@if ($iiia == 0) - @else {{ $iiia }} @endif</td>

              @php
                $iiib = 0;
                foreach ($getAbsenApel as $apel) {
                  if(($apel->struktural == 10) && ($apel->skpd == $skpd->id)){
                    $iiib += 1;
                  }
                }
              @endphp
              <td>@if ($iiib == 0) - @else {{ $iiib }} @endif</td>

              <td align="center" colspan="2">{{ $jumlah_pegawai - ($ia+$ib+$ic+$id+$iia+$iib+$iic+$iid+$iiia+$iiib)}}</td>
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
              <th rowspan="2" class="text-center">No</th>
              <th rowspan="2" class="text-center">SKPD</th>
              <th rowspan="2" class="text-center">Jumlah Pegawai</th>
              <th colspan="2" class="text-center">5</th>
              <th colspan="2" class="text-center">4</th>
              <th colspan="2" class="text-center">3</th>
              <th colspan="2" class="text-center">2</th>
              <th colspan="2" class="text-center"></th>
              <th rowspan="2" class="text-center">Tidak Apel</th>
            </tr>
            <tr>
              @foreach ($getStruktural as $key)
              <th class="text-center">{{ $key->nama }}</th>
              @endforeach
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="17" align="center">Pilih Tanggal Apel</td>
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
