<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:33px;">REKAP ABSENSI APEL</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:29px;">PERIODE TANGGAL {{ $tanggalApelnya }}</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">
          <tr>
            <th rowspan="2" width="40px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">No</th>
            <th rowspan="2" width="200px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">SKPD</th>
            <th rowspan="2" width="80px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">Jumlah Pegawai</th>
            <th colspan="2" width="30px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">5</th>
            <th colspan="2" width="30px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">4</th>
            <th colspan="2" width="30px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">3</th>
            <th colspan="2" width="30px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">2</th>
            <th colspan="2" width="30px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;"></th>
            <th rowspan="2" width="80px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">Tidak Apel</th>
          </tr>
          <tr align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">
            @foreach ($getStruktural as $key)
            <th width="80px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">{{ $key->nama }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @php
            $no = 1;
          @endphp
          @foreach ($getSkpd as $skpd)
          <tr align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">{{ $no }}</td>
            <td align="left" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">{{ $skpd->nama }}</td>
            @foreach ($jumlahPegawaiSKPD as $jmlPeg)
            @if($skpd->id == $jmlPeg->skpd_id)
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">{{ $jmlPeg->jumlah_pegawai }}</td>
              @php
                $jumlah_pegawai = $jmlPeg->jumlah_pegawai;
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
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($ia == 0) - @else {{ $ia }} @endif</td>

            @php
              $ib = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 2) && ($apel->skpd == $skpd->id)){
                  $ib += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($ib == 0) - @else {{ $ib }} @endif</td>

            @php
              $ic = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 3) && ($apel->skpd == $skpd->id)){
                  $ic += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($ic == 0) - @else {{ $ic }} @endif</td>

            @php
              $id = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 4) && ($apel->skpd == $skpd->id)){
                  $id += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($id == 0) - @else {{ $id }} @endif</td>

            @php
              $iia = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 5) && ($apel->skpd == $skpd->id)){
                  $iia += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($iia == 0) - @else {{ $iia }} @endif</td>

            @php
              $iib = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 6) && ($apel->skpd == $skpd->id)){
                  $iib += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($iib == 0) - @else {{ $iib }} @endif</td>

            @php
              $iic = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 7) && ($apel->skpd == $skpd->id)){
                  $iic += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($iic == 0) - @else {{ $iic }} @endif</td>

            @php
              $iid = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 8) && ($apel->skpd == $skpd->id)){
                  $iid += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($iid == 0) - @else {{ $iid }} @endif</td>

            @php
              $iiia = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 9) && ($apel->skpd == $skpd->id)){
                  $iiia += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($iiia == 0) - @else {{ $iiia }} @endif</td>

            @php
              $iiib = 0;
              foreach ($getAbsenApel as $apel) {
                if(($apel->struktural == 10) && ($apel->skpd == $skpd->id)){
                  $iiib += 1;
                }
              }
            @endphp
            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">@if ($iiib == 0) - @else {{ $iiib }} @endif</td>

            <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 20px;">{{ $jumlah_pegawai - ($ia+$ib+$ic+$id+$iia+$iib+$iic+$iid+$iiia+$iiib)}}</td>
          </tr>
          @php
            $no++
          @endphp
          @endforeach
        </table>
      </div>
    </div>
  </div>
</div>
