@extends('layout.master')

@section('title')
  <title>Detail Laporan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@endsection

@section('content')
{{-- Dalam maintenance.. --}}
<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Periode</p>
        </div>
      </div>

      <form action="{{ route('laporanAdmin.store')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">

        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
          <div class="col-xs-6">
            <input type="text" class="form-control" name="end_date" id="end_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>

      </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-block bg-purple" value="Pilih">

          {{-- <a href="{{ route('laporan.cetakAdmin', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR]) }}" class="btn btn-block bg-green">Download PDF</a> --}}

      </div>
      </form>
    </div>
  </div>
</div>


<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th class="text-center">NIP</th>
              <th class="text-center">Nama</th>
              <th class="text-center">Netto TPP</th>
              <th class="text-center">TERLAMBAT (kali)</th>
              <th class="text-center">POTONGAN (2% dari 60% Netto TPP)</th>
              <th class="text-center">PULANG CEPAT (kali)</th>
              <th class="text-center">POTONGAN (2% dari 60% Netto TPP)</th>
              <th class="text-center">TERLAMBAT & PULANG CEPAT (kali)</th>
              <th class="text-center">POTONGAN (3% dari 60% Netto TPP)</th>
              <th class="text-center">TANPA KETERANGAN (kali)</th>
              <th class="text-center">POTONGAN (3% dari 100% Netto TPP)</th>
              <th class="text-center">TIDAK APEL (kali)</th>
              <th class="text-center">POTONGAN (2.5% dari 60% Netto TPP)</th>
              <th class="text-center">TIDAK APEL 4 KALI (kali)</th>
              <th class="text-center">POTONGAN (25% dari 60% Netto TPP)</th>
              <th class="text-center">TOTAL POTONGAN</th>
              <th class="text-center">TPP DIBAYARKAN</th>
            </tr>
          </thead>
          <tbody>
            @if (!isset($dataabsensi))
              <tr>
                <td colspan="18" align="center">Pilih Periode Waktu</td>
              </tr>
            @else
              @php
              $number = 1;
              $arrpengecualian = array();
              $flagpengecualiantpp = 0;
              @endphp
              @foreach ($dataabsensi as $key)
                <tr id="row{{$number}}">
                  <td align="center">{{$number}}</td>
                  @php
                    $flagpotongantpp = 0;
                    $tracker = 0;
                    $potongantppindex = [4,6,8,10,12,14];
                    $nettotpp = 0;
                  @endphp
                  @foreach ($key as $k)
                      @if ($tracker==0 && in_array($k, $pengecualian))
                        @php
                          $arrpengecualian[] = "row".$number;
                          $flagpengecualiantpp = 1;
                        @endphp
                      @endif
                      <td align="center">{{$k}}</td>
                      @if (in_array($tracker, $potongantppindex))
                        @php
                          $flagpotongantpp = $flagpotongantpp + $k;
                        @endphp
                      @endif
                      @if ($tracker==2)
                        @php
                          $nettotpp = $k;
                        @endphp
                      @endif
                      @php
                        $tracker++;
                      @endphp
                  @endforeach
                  <td align="center">
                    @if ($flagpengecualiantpp == 1)
                      @php
                        $flagpotongantpp = 0;
                      @endphp
                    @endif
                    {{$flagpotongantpp}}
                  </td>
                  <td align="center">
                    @php
                      $totaltppdibayar = $nettotpp - $flagpotongantpp;
                    @endphp
                    {{$totaltppdibayar}}
                  </td>
                </tr>
                @php
                $number++;
                @endphp
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
  @foreach ($arrpengecualian as $key)
    $("#{{$key}}").attr('style', 'background:#c4ffd1;');
  @endforeach

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
