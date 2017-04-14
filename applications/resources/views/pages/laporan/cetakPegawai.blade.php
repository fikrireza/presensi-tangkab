<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:33px;">REKAP ABSENSI {{ strtoupper($nip_sapk) }} - {{ $fid->nama }}</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:29px;">PERIODE TANGGAL {{ $start_dateR }} s/d {{ $end_dateR }}</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">
          <thead>
            <tr style="border: 1px solid black;border-collapse: collapse;font-size: 16px;">
              <th width="60px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">No</th>
              <th width="150px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Tanggal</th>
              <th width="120px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Hari</th>
              <th width="150px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Jam Datang</th>
              <th width="150px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Jam Pulang</th>
              <th width="450px" class="text-center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp

            @foreach ($tanggalBulan as $tanggal)
            <tr align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">{{ $no }}</td>
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">{{ $tanggal }}</td>
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
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">{{ $dayList[$day] }}</td>

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
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">{{ $lib->keterangan }}</td>
                @endif
              @endforeach

              @php
                  $flaginter = 0;
              @endphp

              @foreach ($absensi as $absen)
                @if ($absen->tanggal == $tanggal && $flaginter == 0)
                  @php
                    $flag++;
                  @endphp
                  <td  align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">@if($absen->jam_datang != null) {{ $absen->jam_datang }} @else x @endif</td>
                  <td  align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">@if($absen->jam_pulang != null) {{ $absen->jam_pulang }} @else x @endif</td>
                @endif

                @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                  @php
                    $flag++;
                  @endphp
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Libur</td>
                  @break
                @endif
              @endforeach

              @if ($flag==0)
                @if ($tanggal > date("d/m/Y"))
                  <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">x</td>
                  <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">x</td>
                @else
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Alpa</td>
                @endif
              @endif

              @foreach ($intervensi as $interv)
                @php
                $mulai = explode('-', $interv->tanggal_mulai);
                $mulai = $mulai[2]."/".$mulai[1]."/".$mulai[0];
                $akhir = explode('-', $interv->tanggal_akhir);
                $akhir = $akhir[2]."/".$akhir[1]."/".$akhir[0];

                $mulai = new DateTime($interv->tanggal_mulai);
                $akhir   = new DateTime($interv->tanggal_akhir);

                @endphp

                @for($i = $mulai; $mulai <= $akhir; $i->modify('+1 day'))
                  @if ($tanggal == $i->format("d/m/Y"))
                      @php
                      $flag++;
                      $flaginter++;
                      @endphp
                    <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>{{ $interv->jenis_intervensi}}</b> | {{ $interv->deskripsi }}</td>
                  @endif
                @endfor
              @endforeach

              @if ($flaginter==0 && $flag==0)
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><span style="color:red;"><b>Alpa</b></span></td>
              @elseif($flaginter==0)
                @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                  @php
                    $flag++;
                  @endphp
                  <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;">Libur</td>
                @else
                  @foreach ($absensi as $absen)
                    @if ($absen->tanggal == $tanggal)
                      @php
                        $flag++;
                      @endphp

                      @if(($absen->jam_datang >= '09:01:00') || ($absen->jam_datang == null))
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>Alpa</b></td>
                      @elseif($absen->jam_pulang == null)
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>Alpa</b></td>
                      @elseif (($absen->jam_datang >= '08:01:00') && ($absen->jam_pulang <= '15:59:00'))
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>Terlambat dan Pulang Cepat</td>
                      @elseif (($absen->jam_datang >= '08:01:00') && ($absen->jam_datang <= '09:00:00') && ($absen->jam_pulang == null))
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>Terlambat dan Pulang Cepat</td>
                      @elseif($absen->jam_datang >= '08:01:00')
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>Terlambat</b></td>
                      @elseif($absen->jam_pulang <= '15:01:00')
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"><b>Pulang Cepat</b></td>
                      @else
                        <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 24px;"></td>
                      @endif
                    @endif
                  @endforeach
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
