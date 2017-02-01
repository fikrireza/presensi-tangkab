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
              $pot_absen = 0;
              $sum_totalPot = 0;
              $sum_tppDibayarkan = 0;
              $sum_GrandTotalPot = 0;
              $sum_GrandTppDibayarkan = 0;
            @endphp
            @foreach ($pegawainya as $pegawai)
            <tr style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ $no }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ $pegawai->nip_sapk }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ $pegawai->nama }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{ number_format($pegawai->tpp_dibayarkan,0,',','.') }}</td>

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
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">{{ $jumlah_telat }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
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
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">{{ $jumlah_cepat }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
                $pot_pulcep = ($pegawai->tpp_dibayarkan*60/100)*2/100*$jumlah_cepat;
                echo number_format($pot_pulcep,0,',','.');
              @endphp</td>

              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">{{ $tot_pulcep_telat }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
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

                  $jumlah_masuknya = (int)$jmlMasuk->Jumlah_Masuk - (count($intervensiHasil) + count($hariApel));
                  $jumlahAbsen = (int)$days - $jumlah_masuknya;

                  echo '<td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">'.$jumlahAbsen.'</td>';
                  $pot_absen = ($pegawai->tpp_dibayarkan*100/100)*3/100*$jumlahAbsen;
                  print '<td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">'.number_format($pot_absen,0,',','.').'</td>';
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
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">{{ $tidak_apel }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
              $tot_tidak_apel = ($pegawai->tpp_dibayarkan*60/100)*2.5/100*$tidak_apel;
              echo number_format($tot_tidak_apel,0,',','.');
              @endphp</td>
              @else
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">0</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">0</td>
              @endif
              @if ($tidak_apel >= 4)
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">{{ $tidak_apel }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">@php
                $tot_tidak_apel = ($pegawai->tpp_dibayarkan*60/100)*25/100*$tidak_apel;
                echo number_format($tot_tidak_apel,0,',','.');
              @endphp</td>
              @else
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">0</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="center">0</td>
              @endif
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">{{ number_format($pot_pulcep+$pot_absen+$pot_terlambat+$tot_tidak_apel+$pot_tot_pulcep_telat,0,',','.') }}</td>
              @php
                $sum_GrandTotalPot += $pot_pulcep+$pot_absen+$pot_terlambat+$tot_tidak_apel+$pot_tot_pulcep_telat;
              @endphp
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right">{{ number_format($pegawai->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat),0,',','.') }}</td>
              @php
                $sum_GrandTppDibayarkan += $pegawai->tpp_dibayarkan - ($pot_pulcep+$pot_absen+$pot_terlambat+$tot_tidak_apel+$pot_tot_pulcep_telat);
              @endphp
            </tr>
            @php
              $no++
            @endphp
            @endforeach
            <tr height="50px">
              <td valign="middle" colspan="16" align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;"><b>Jumlah</b></td>
              <td valign="middle" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right"><b>{{ number_format($sum_GrandTotalPot,0,',','.') }}</b></td>
              <td valign="middle" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right"><b>{{ number_format($sum_GrandTppDibayarkan,0,',','.') }}</b></td>
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
