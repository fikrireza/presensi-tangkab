<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:33px;">REKAP ABSENSI {{ strtoupper($nip_sapk) }} - {{ $fid->nama }}</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:29px;">PERIODE TANGGAL {{ $start_dateR }} s/d {{ $end_dateR }}</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">
          <thead>
            <tr style="border: 1px solid black;border-collapse: collapse;font-size: 16px;">
              <th width="80px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">No</th>
              <th width="200px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Tanggal</th>
              <th width="200px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Hari</th>
              <th width="250px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Jam Datang</th>
              <th width="250px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Jam Pulang</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp

            @foreach ($tanggalBulan as $tanggal)
            <tr align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $no }}</td>
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $tanggal }}</td>
              @php
              $day = explode('/', $tanggal);
              $day = $day[1]."/".$day[0]."/".$day[2];
              $day = date('D', strtotime($day));

              $dayList = array(
                'Sun' => 'Minggu',
                'Mon' => 'Senin',
                'Tue' => 'Selasa',
                'Wed' => 'Rabu',
                'Thu' => 'Kamis',
                'Fri' => 'Jum&#039;at',
                'Sat' => 'Sabtu'
              );
              @endphp
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $dayList[$day] }}</td>

              @php
                $flag=0;
              @endphp

              @foreach ($hariLibur as $lib)
                @php
                  $holiday = explode('-', $lib->libur);
                  $holiday = $holiday[2]."/".$holiday[1]."/".$holiday[0];
                @endphp
                @if($holiday == $tanggal)
                  @php
                    $flag++;
                  @endphp
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $lib->keterangan }}</td>
                @endif
              @endforeach

              @php
                  $flaginter = 0;
              @endphp

              @foreach ($intervensi as $interv)
                @php
                $mulai = explode('-', $interv->tanggal_mulai);
                $mulai = $mulai[2]."/".$mulai[1]."/".$mulai[0];
                $akhir = explode('-', $interv->tanggal_akhir);
                $akhir = $akhir[2]."/".$akhir[1]."/".$akhir[0];

                $mulai = new DateTime($interv->tanggal_mulai);
                $akhir   = new DateTime($interv->tanggal_akhir);

                @endphp
                @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                  @php
                  $flag++;
                  @endphp
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Libur</td>
                  @break
                @else
                @for($i = $mulai; $mulai <= $akhir; $i->modify('+1 day'))
                  @if ($tanggal == $i->format("d/m/Y"))
                      @php
                      $flag++;
                      $flaginter++;
                      @endphp
                    <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $interv->deskripsi }}</td>
                  @endif
                @endfor
                @endif
              @endforeach

              @foreach ($absensi as $absen)

                @foreach ($absen as $key)
                  @if ($key->Tanggal_Log == $tanggal && $flaginter == 0)
                    @php
                      $flag++;
                    @endphp
                    <td  align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">@if($key->Jam_Datang != null) {{ $key->Jam_Datang }} @else x @endif</td>
                    <td  align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">@if($key->Jam_Pulang != null) {{ $key->Jam_Pulang }} @else x @endif</td>
                  @endif
                @endforeach

                @if ($intervensi == null)
                  @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Libur</td>
                    @break
                  @endif
                @endif


              @endforeach

              @if ($flag==0)
                @if ($tanggal > date("d/m/Y"))
                  <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">x</td>
                  <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">x</td>
                @else
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Alpa</td>
                @endif
              @endif
            </tr>
            @php
              $no++
            @endphp
            @endforeach
          </tr>
          @php
          $no++
          @endphp
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
