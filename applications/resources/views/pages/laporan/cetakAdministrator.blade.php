<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>


<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:18px;">DAFTAR POTONGAN TPP PNS {{ strtoupper($nama_skpd->nama) }} ABSENSI ELEKTRONIK</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:16px;">PERIODE TANGGAL {{ $start_dateR }} S/D {{ $end_dateR }}</h3>
    </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered" style="border: 1px solid black;border-collapse: collapse;font-size: 16px;">
          <thead>
            <tr style="border: 1px solid black;border-collapse: collapse;font-size: 16px;">
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">No</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">NIP</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">Nama</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">Netto TPP</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TERLAMBAT (kali)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">POTONGAN (2% dari 60% Netto TPP)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">PULANG CEPAT (kali)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">POTONGAN (2% dari 60% Netto TPP)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TERLAMBAT & PULANG CEPAT (kali)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">POTONGAN (3% dari 60% Netto TPP)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TANPA KETERANGAN (kali)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">POTONGAN (3% dari 100% Netto TPP)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TIDAK APEL (kali)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">POTONGAN (2.5% dari 60% Netto TPP)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TIDAK APEL 4 KALI (kali)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">POTONGAN (25% dari 60% Netto TPP)</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TOTAL POTONGAN</th>
              <th class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">TPP DIBAYARKAN</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
              $sum_totalPot = 0;
              $sum_tppDibayarkan = 0;
            @endphp
            @foreach ($rekapAbsenPeriode as $detailAbsen)
            <tr style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ $no }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ $detailAbsen->nip_sapk }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ $detailAbsen->nama_pegawai }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">{{ number_format($detailAbsen->tpp_dibayarkan,0,',','.') }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">@if ($detailAbsen->Jumlah_Terlambat == 0) - @else {{ $detailAbsen->Jumlah_Terlambat }} @endif</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
                $pot_terlambat = ($detailAbsen->tpp_dibayarkan*60/100)*2/100*$detailAbsen->Jumlah_Terlambat;
                echo number_format($pot_terlambat,0,',','.');
              @endphp</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">@if ($detailAbsen->Jumlah_Pulcep == 0) - @else {{ $detailAbsen->Jumlah_Pulcep }} @endif</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
                $pot_pulcep = ($detailAbsen->tpp_dibayarkan*60/100)*2/100*$detailAbsen->Jumlah_Pulcep;
                echo number_format($pot_pulcep,0,',','.');
              @endphp</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">-</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">0</td>
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
                  print '<td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">'.$jumlahAbsen.'</td>';
                  $pot_absen = ($detailAbsen->tpp_dibayarkan*100/100)*3/100*$jumlahAbsen;
                  print '<td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">'.number_format($pot_absen,0,',','.').'</td>';
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
                  echo '<td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">'.$jumlahAbsen.'</td>';
                  $pot_absen = ($detailAbsen->tpp_dibayarkan*100/100)*3/100*$jumlahAbsen;
                  print '<td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">'.number_format($pot_absen,0,',','.').'</td>';

                }
                @endphp
                @endif
              @endforeach
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">-</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">0</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">-</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">0</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">{{ number_format($pot_pulcep+$pot_absen+$pot_terlambat,0,',','.') }}</td>
              @php
                $sum_totalPot += $pot_pulcep+$pot_absen+$pot_terlambat;
              @endphp
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">{{ number_format($detailAbsen->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat),0,',','.') }}</td>
              @php
                $sum_tppDibayarkan += $detailAbsen->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat);
              @endphp
            </tr>
            @php
              $no++
            @endphp
            @endforeach
            <tr height="50px">
              <td valign="middle" colspan="16" align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;"><b>Jumlah</b></td>
              <td valign="middle" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right"><b>{{ number_format($sum_totalPot,0,',','.') }}</b></td>
              <td valign="middle" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right"><b>{{ number_format($sum_tppDibayarkan,0,',','.') }}</b></td>
            </tr>
          </tbody>
        </table>

        @php
        $ttd_now = date('F Y');
        @endphp
        <table width="1500px" style="font-size:15px;">
          <tr height="30px">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="200px">&nbsp;</td>
            <td width="300px">
            @foreach ($pejabatDokumen as $pejabat)
            @if ($pejabat->posisi_ttd == 2)
              <table>
                <tr>
                  <td align="center" width="400px">Mengetahui : </td>
                </tr>
                <tr height="20px">
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td align="center" width="400px">{{ strtoupper($pejabat->jabatan)}}</td>
                </tr>
                <tr height="100px">
                  <td>&nbsp;</td>
                </tr>
                <tr height="100px">
                  <td>&nbsp;</td>
                </tr>
                <tr height="100px">
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td align="center"><u>{{ strtoupper($pejabat->nama)}}</u></td>
                </tr>
                <tr>
                  <td align="center">{{ $pejabat->pangkat }}</td>
                </tr>
                <tr>
                  <td align="center">NIP : {{ $pejabat->nip_sapk }}</td>
                </tr>
              </table>
            @endif
            @endforeach
            </td>
            <td width="250px">&nbsp;</td>
            <td width="300px">
            @foreach ($pejabatDokumen as $pejabat)
            @if ($pejabat->posisi_ttd == 1)
            <table>
              <tr>
                <td align="center" width="400px">Tigaraksa,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      {{ $ttd_now }} </td>
              </tr>
              <tr height="20px">
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="center" width="400px">{{ strtoupper($pejabat->jabatan)}}</td>
              </tr>
              <tr height="100px">
                <td>&nbsp;</td>
              </tr>
              <tr height="100px">
                <td>&nbsp;</td>
              </tr>
              <tr height="100px">
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td align="center"><u>{{ strtoupper($pejabat->nama)}}</u></td>
              </tr>
              <tr>
                <td align="center">{{ $pejabat->pangkat }}</td>
              </tr>
              <tr>
                <td align="center">NIP : {{ $pejabat->nip_sapk }}</td>
              </tr>
            </table>
            @endif
            @endforeach
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
