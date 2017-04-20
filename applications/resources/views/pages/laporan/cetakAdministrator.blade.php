<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>


<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:18px;">DAFTAR POTONGAN TPP PNS {{ strtoupper($nama_skpd->nama) }} ABSENSI ELEKTRONIK</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:16px;">PERIODE {{ $bulan }}</h3>
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
              $number = 1;
              $arrpengecualian = array();
              $flagpengecualiantpp = 0;
            @endphp
            @foreach ($rekaptpp as $key)
              <tr id="row{{$key['nip']}}" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">
                <td>{{$number}}</td>
                <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["nip"]}}</td>
                <td style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["nama"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["tpp"]}}</td>
                <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["telat"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["potongantelat"]}}</td>
                <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["pulangcepat"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["potonganpulangcepat"]}}</td>
                <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["telatpulangcepat"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["potongantelatpulangcepat"]}}</td>
                <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["tidakhadir"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["potongantidakhadir"]}}</td>
                <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["tidakapel"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["potongantidakapel"]}}</td>
                <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["tidakapelempat"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["potongantidakapelempat"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["totalpotongantpp"]}}</td>
                <td align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;">{{$key["totalterimatpp"]}}</td>
              </tr>
              @php
                $number++;
              @endphp
            @endforeach
          <tr height="50px">
            <td valign="middle" colspan="16" align="right" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;"><b>Jumlah</b></td>
            <td valign="middle" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right"><b>{{ $grandtotalpotongantpp  }}</b></td>
            <td valign="middle" style="border: 1px solid black;border-collapse: collapse;font-size: 15px;" align="right"><b>{{ $grandtotaltppdibayarkan }}</b></td>
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
