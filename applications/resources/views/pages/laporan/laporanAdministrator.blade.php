@extends('layout.master')

@section('title')
  <title>Laporan Absensi</title>
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
      <form action="{{ route('laporanAdministrator.store') }}" method="POST">
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
        @if (isset($rekapAbsenPeriode))
          <a href="{{ route('laporan.cetakAdministrator', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR, 'skpd_id'=>$skpd_id]) }}" class="btn btn-block bg-green">Download PDF</a>
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
        <h3 class="box-title">Detil Absensi</h3>
    </div>
      <div class="box-body table-responsive">
        @if(isset($rekapAbsenPeriode))
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
            @php
              $no = 1;
              $sum_totalPot = 0;
              $sum_tppDibayarkan = 0;
            @endphp
            @foreach ($rekapAbsenPeriode as $detailAbsen)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $detailAbsen->nip_sapk }}</td>
              <td>{{ $detailAbsen->nama_pegawai }}</td>
              <td>{{ number_format($detailAbsen->tpp_dibayarkan,0,',','.') }}</td>
              <td>{{ $detailAbsen->Jumlah_Terlambat }}</td>
              <td>@php
                $pot_terlambat = ($detailAbsen->tpp_dibayarkan*60/100)*2/100*$detailAbsen->Jumlah_Terlambat;
                echo number_format($pot_terlambat,0,',','.');
              @endphp</td>
              <td>{{ $detailAbsen->Jumlah_Pulcep }}</td>
              <td>@php
                $pot_pulcep = ($detailAbsen->tpp_dibayarkan*60/100)*2/100*$detailAbsen->Jumlah_Pulcep;
                echo number_format($pot_pulcep,0,',','.');
              @endphp</td>
              <td>0</td>
              <td>0</td>
              @foreach ($potongIntervensi as $intervensi)
                @if ($detailAbsen->nip_sapk == $intervensi->nip_sapk)
                @php
                if($intervensi->Tanggal_Mulai == null){
                  $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Senin, ...)
                  $holidayDays = $hariLibur;

                  $from = new DateTime($start_date);
                  $to = new DateTime($end_date);
                  $interval = new DateInterval('P1D');
                  $periods = new DatePeriod($from, $interval, $to);

                  $days1 = 0;
                  foreach ($periods as $period) {
                    if (!in_array($period->format('N'), $workingDays)) continue;
                    if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
                    if (in_array($period->format('*-m-d'), $holidayDays)) continue;
                    $days1++;
                  }

                  $jumlahAbsen = (int)$days1 - (int)$intervensi->Jumlah_Masuk;
                  print '<td>'.$jumlahAbsen.'</td>';
                  $pot_absen = ($detailAbsen->tpp_dibayarkan*100/100)*3/100*$jumlahAbsen;
                  print '<td>'.number_format($pot_absen,0,',','.').'</td>';
                }else{
                  $tanggal_mulai = $intervensi->Tanggal_Mulai;
                  $tanggal_akhir = $intervensi->Tanggal_Akhir;
                  $mulai = new DateTime($tanggal_mulai);
                  $akhir   = new DateTime($tanggal_akhir);

                  for($i = $mulai; $mulai <= $akhir; $i->modify('+1 day'))
                  {
                    $intervensiHasil[] =  $i->format("Y-m-d");
                  }
                  $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Senin, ...)
                  $holidayDays = array_merge($hariLibur, $intervensiHasil);

                  $from = new DateTime($start_date);
                  $to = new DateTime($end_date);
                  $to->modify('+1 day');
                  $interval = new DateInterval('P1D');
                  $periods = new DatePeriod($from, $interval, $to);

                  $days = 0;
                  foreach ($periods as $period) {
                    if (!in_array($period->format('N'), $workingDays)) continue;
                    if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
                    if (in_array($period->format('*-m-d'), $holidayDays)) continue;
                    $days++;
                  }

                  $jumlahAbsen = (int)$days - (int)$intervensi->Jumlah_Masuk;
                  echo '<td>'.$jumlahAbsen.'</td>';
                  $pot_absen = ($detailAbsen->tpp_dibayarkan*100/100)*3/100*$jumlahAbsen;
                  print '<td>'.number_format($pot_absen,0,',','.').'</td>';

                }
                @endphp
                @endif
              @endforeach
              <td>0</td>
              <td>0</td>
              <td>0</td>
              <td>0</td>
              <td>{{ number_format($pot_pulcep+$pot_absen+$pot_terlambat,0,',','.') }}</td>
              @php
                $sum_totalPot += $pot_pulcep+$pot_absen+$pot_terlambat;
              @endphp
              <td>{{ number_format($detailAbsen->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat),0,',','.') }}</td>
              @php
                $sum_tppDibayarkan += $detailAbsen->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat);
              @endphp
            </tr>
            @php
              $no++
            @endphp
            @endforeach
            <tr height="50px">
              <td valign="middle" colspan="16" align="right"><b>Jumlah</b></td>
              <td valign="middle"><b>{{ number_format($sum_totalPot,0,',','.') }}</b></td>
              <td valign="middle"><b>{{ number_format($sum_tppDibayarkan,0,',','.') }}</b></td>
            </tr>
          </tbody>
        </table>
        @else
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
            <tr>
              <td colspan="18" align="center">Pilih SKPD dan Periode Waktu</td>
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
