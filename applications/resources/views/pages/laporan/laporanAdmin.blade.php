@extends('layout.master')

@section('title')
  <title>Detail Laporan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@endsection

@section('content')
Dalam maintenance..
{{-- <div class="row">
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
        @if(isset($pegawainya))
        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="start_date" id="start_date" value="{{ $start_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
          <div class="col-xs-6">
            <input type="text" class="form-control" name="end_date" id="end_date" value="{{ $end_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @else
        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
          <div class="col-xs-6">
            <input type="text" class="form-control" name="end_date" id="end_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @endif
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
        @if(isset($pegawainya))
          <a href="{{ route('laporan.cetakAdmin', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR]) }}" class="btn btn-block bg-green">Download PDF</a>
        @endif
      </div>
      </form>
    </div>
  </div>
</div> --}}

<!--
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($pegawainya))
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
              $pot_absen = 0;
              $sum_totalPot = 0;
              $sum_tppDibayarkan = 0;
              $sum_GrandTotalPot = 0;
              $sum_GrandTppDibayarkan = 0;
            @endphp
            @foreach ($pegawainya as $pegawai)
            <tr>
              <td>{{ $no }}</td>
              <td><a href="{{ route('laporan.cetakPegawai', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR, 'nip_sapk'=>$pegawai->nip_sapk]) }}">{{ $pegawai->nip_sapk }}</a></td>
              <td>{{ $pegawai->nama }}</td>
              <td>{{ number_format($pegawai->tpp_dibayarkan,0,',','.') }}</td>

              {{--  HITUNG TERLAMBAT dan PULANG CEPAT --}}
              @php
                $tot_pulcep_telat = 0;
                foreach ($total_telat_dan_pulcep as $tot) {
                  $pecah = explode("-", $tot);
                  if ($pegawai->fid == $pecah[0]) {
                    $tot_pulcep_telat += 1;
                  }
                }
              @endphp

              {{-- HITUNG DATANG TERLAMBAT --}}
              @php
              $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
              $date_to = strtotime($end_date); // Convert date to a UNIX timestamp
              $jam_masuk = array();
              for ($i=$date_from; $i<=$date_to; $i+=86400) {
                $tanggalini = date('d/m/Y', $i);

                foreach ($absensi as $key) {
                  if(!in_array(date('Y-m-d', $i), $hariApel)) { /* Ignore Hari Apel */
                    if($tanggalini == $key->tanggal_log){
                      if ($pegawai->fid == $key->fid) {
                        $jammasuk1 = 80000;
                        $jammasuk2 = 100000;
                        $jamlog = (int) str_replace(':','',$key->jam_log);
                        if( ($jamlog > $jammasuk1) && ($jamlog <= $jammasuk2)){
                          $jam_masuk[] = $key->fid.'-'.$tanggalini;
                        }
                      }
                    }
                  }
                }
              }
              $jumlah_telat = array_unique($jam_masuk);
              $jumlah_telat = count($jumlah_telat);
              @endphp
              <td>{{ $jumlah_telat }}</td>
              <td>@php
                $pot_terlambat = ($pegawai->tpp_dibayarkan*60/100)*2/100*$jumlah_telat;
                echo number_format($pot_terlambat,0,',','.');
              @endphp</td>

              {{--  HITUNG PULANG CEPAT --}}
              @php
              $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
              $date_to = strtotime($end_date); // Convert date to a UNIX timestamp
              $jam_pulang = array();
              for ($i=$date_from; $i<=$date_to; $i+=86400) {
                $tanggalini = date('d/m/Y', $i);

                foreach ($absensi as $key) {
                  if($tanggalini == $key->tanggal_log){
                    if ($pegawai->fid == $key->fid) {
                      $jampulang1 = 140000;
                      $jampulang2 = 160000;
                      $jamlog = (int) str_replace(':','',$key->jam_log);
                      if(($jamlog >= $jampulang1) && ($jamlog < $jampulang2)){
                        $jam_pulang[] = $key->fid.'-'.$tanggalini;
                      }
                    }
                  }
                }
              }
              $jumlah_cepat = array_unique($jam_pulang);
              $jumlah_cepat = count($jumlah_cepat);
              @endphp
              <td>{{ $jumlah_cepat }}</td>
              <td>@php
                $pot_pulcep = ($pegawai->tpp_dibayarkan*60/100)*2/100*$jumlah_cepat;
                echo number_format($pot_pulcep,0,',','.');
              @endphp</td>

              <td>{{ $tot_pulcep_telat }}</td>
              <td>@php
                $pot_tot_pulcep_telat = ($pegawai->tpp_dibayarkan*60/100)*3/100*$tot_pulcep_telat;
                echo number_format($pot_tot_pulcep_telat,0,',','.');
              @endphp</td>

              {{-- Menghitung Jumlah Intervensi --}}
              @php
                $intervensiHasil = array();
              @endphp
              @foreach ($intervensi as $ijin)
                @php
                if($pegawai->pegawai_id == $ijin->pegawai_id){
                    $tanggal_mulai = $ijin->tanggal_mulai;
                    $tanggal_akhir = $ijin->tanggal_akhir;
                    $mulai = new DateTime($tanggal_mulai);
                    $akhir   = new DateTime($tanggal_akhir);

                    for($i = $mulai; $mulai <= $akhir; $i->modify('+1 day'))
                    {
                      $intervensiHasil[] =  $i->format("Y-m-d");
                    }
                  }
                @endphp
              @endforeach

              {{-- Menghitung Jumlah Bolos --}}
              @foreach ($jumlahMasuk as $jmlMasuk)
                @if ($pegawai->nip_sapk == $jmlMasuk->nip_sapk)
                  @php
                  $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Senin, ...)
                  $holidayDays = array_merge($hariLibur, $intervensiHasil, $hariApel);

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

                  // $intervensi = count($intervensiHasil);
                  $jumlah_masuknya = (int)$jmlMasuk->Jumlah_Masuk - (count($intervensiHasil) + count($hariApel));
                  $jumlahAbsen = (int)$days - $jumlah_masuknya;

                  echo '<td>'.$jumlahAbsen.'</td>';
                  $pot_absen = ($pegawai->tpp_dibayarkan*100/100)*3/100*$jumlahAbsen;
                  print '<td>'.number_format($pot_absen,0,',','.').'</td>';
                  @endphp
                @endif
              @endforeach

              {{--  MENGHITUNG TIDAK APEL --}}
              @php
              $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
              $date_to = strtotime($end_date); // Convert date to a UNIX timestamp
              $tidak_apel = 0;
              for ($i=$date_from; $i<=$date_to; $i+=86400) {
                $tanggalini = date('d/m/Y', $i);

                foreach ($absensi as $key) {
                  if(in_array(date('Y-m-d', $i), $hariApel)) { /* Hanya Hari Apel */
                    if($tanggalini == $key->tanggal_log){
                      if ($pegawai->fid == $key->fid) {
                        $jamapel1 = 80000;
                        $jamapel2 = 100000;
                        $jamlog = (int) str_replace(':','',$key->jam_log);
                        if( ($jamlog > $jamapel1) && ($jamlog <= $jamapel2)){
                          $tidak_apel += 1;
                        }
                      }
                    }
                  }
                }
              }
              @endphp
              @if ($tidak_apel < 4)
              <td>{{ $tidak_apel }}</td>
              <td>@php
              $tot_tidak_apel = ($pegawai->tpp_dibayarkan*60/100)*2.5/100*$tidak_apel;
              echo number_format($tot_tidak_apel,0,',','.');
              @endphp</td>
              @else
              <td>0</td>
              <td>0</td>
              @endif
              @if ($tidak_apel >= 4)
              <td>{{ $tidak_apel }}</td>
              <td>@php
                $tot_tidak_apel = ($pegawai->tpp_dibayarkan*60/100)*25/100*$tidak_apel;
                echo number_format($tot_tidak_apel,0,',','.');
              @endphp</td>
              @else
              <td>0</td>
              <td>0</td>
              @endif
              <td>{{ number_format($pot_pulcep+$pot_absen+$pot_terlambat+$tot_tidak_apel+$pot_tot_pulcep_telat,0,',','.') }}</td>
              @php
                $sum_GrandTotalPot += $pot_pulcep+$pot_absen+$pot_terlambat+$tot_tidak_apel+$pot_tot_pulcep_telat;
              @endphp
              <td>{{ number_format($pegawai->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat),0,',','.') }}</td>
              @php
                $sum_GrandTppDibayarkan += $pegawai->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat+$tot_tidak_apel+$pot_tot_pulcep_telat);
              @endphp
            </tr>
            @php
              $no++
            @endphp
            @endforeach
            <tr height="50px">
              <td valign="middle" colspan="16" align="right"><b>Jumlah</b></td>
              <td valign="middle"><b>{{ number_format($sum_GrandTotalPot,0,',','.') }}</b></td>
              <td valign="middle"><b>{{ number_format($sum_GrandTppDibayarkan,0,',','.') }}</b></td>
            </tr>
          </tbody>
        </table>

        @php
        $ttd_now = date('F Y');
        @endphp
        <table width="1500px">
          <tr height="30px">
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td width="200px"></td>
            <td width="300px">
            @foreach ($pejabatDokumen as $pejabat)
            @if ($pejabat->posisi_ttd == 2)
              <table>
                <tr>
                  <td class="text-center" width="400px">Mengetahui : </td>
                </tr>
                <tr height="20px">
                  <td></td>
                </tr>
                <tr>
                  <td class="text-center" width="400px">{{ strtoupper($pejabat->jabatan)}}</td>
                </tr>
                <tr height="100px">
                  <td></td>
                </tr>
                <tr>
                  <td class="text-center"><u>{{ strtoupper($pejabat->nama)}}</u></td>
                </tr>
                <tr>
                  <td class="text-center">{{ $pejabat->pangkat }}</td>
                </tr>
                <tr>
                  <td class="text-center">NIP : {{ $pejabat->nip_sapk }}</td>
                </tr>
              </table>
            @endif
            @endforeach
            </td>
            <td width="250px"></td>
            <td width="300px">
            @foreach ($pejabatDokumen as $pejabat)
            @if ($pejabat->posisi_ttd == 1)
            <table>
              <tr>
                <td class="text-center" width="400px">Tigaraksa,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      {{ $ttd_now }} </td>
              </tr>
              <tr height="20px">
                <td></td>
              </tr>
              <tr>
                <td class="text-center" width="400px">{{ strtoupper($pejabat->jabatan)}}</td>
              </tr>
              <tr height="100px">
                <td></td>
              </tr>
              <tr>
                <td class="text-center"><u>{{ strtoupper($pejabat->nama)}}</u></td>
              </tr>
              <tr>
                <td class="text-center">{{ $pejabat->pangkat }}</td>
              </tr>
              <tr>
                <td class="text-center">NIP : {{ $pejabat->nip_sapk }}</td>
              </tr>
            </table>
            @endif
            @endforeach
            </td>
          </tr>
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
              <td colspan="18" align="center">Pilih Periode Waktu</td>
            </tr>
          </tbody>
        </table>
        @endif
      </div>
    </div>
  </div>
</div>
-->
@endsection

@section('script')
<script>
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
