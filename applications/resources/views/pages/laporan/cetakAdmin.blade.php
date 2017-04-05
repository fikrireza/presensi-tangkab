<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:18px;">DAFTAR POTONGAN TPP PNS {{ strtoupper($nama_skpd->nama) }} ABSENSI ELEKTRONIK</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:16px;">PERIODE TANGGAL {{ $start_dateR }} S/D {{ $end_dateR }}</h3>
      </div>
      <div class="box-body">
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
                <tr id="row{{$number}}" style="border: 1px solid black;border-collapse: collapse;font-size: 16px;">
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
                $flagpengecualiantpp = 0;
                @endphp
              @endforeach
            @endif
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
