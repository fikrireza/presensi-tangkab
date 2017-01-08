<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:36px;">REKAP ABSENSI {{ strtoupper($nip_sapk) }}</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:30px;">PERIODE TANGGAL {{ $start_dateR }} S/D {{ $end_dateR }}</h3>
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
            $flag = 0;
            $flaginter = 0;

            $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
            $date_to = strtotime($end_date); // Convert date to a UNIX timestamp

            for ($i=$date_from; $i<=$date_to; $i+=86400) {

            @endphp
            <tr style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $no }}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $tanggal = date("d/m/Y", $i) }}</td>
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
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $dayList[$day] }}</td>
              @foreach ($hariLibur as $libur)
                @if ($libur->libur == date("Y-m-d", $i))
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $libur->keterangan }}</td>
                @endif
              @endforeach
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
                  $flaginter++;
                  @endphp
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Libur</td>
                  @break
                @else
                @for($tglInterv = $mulai; $mulai <= $akhir; $tglInterv->modify('+1 day'))
                  @if ($tanggal == $tglInterv->format("d/m/Y"))
                    @php
                    $flag++;
                    $flaginter++;
                    @endphp
                  <td colspan="2" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">{{ $interv->deskripsi }}</td>
                  @endif
                @endfor
                @endif
              @endforeach
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 28px;" align="center">
                @php
                  $flagmasuk=0;
                @endphp
                @foreach ($rekapAbsenPeriode as $keys)
                  @if ($keys->Tanggal_Log == $tanggal)
                    @php
                      $jammasuk = 100000;
                      $jamlog = (int) str_replace(':','',$keys->Jam_Log);
                    @endphp
                    @if ($jamlog<$jammasuk)
                      @php
                        $flagmasuk=1;
                      @endphp
                      {{ $keys->Jam_Log }}
                    @endif
                  @endif
                @endforeach
                @if ($flagmasuk==0)
                  x
                @endif
              </td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 28px;" align="center">
                @php
                  $flagpulang=0;
                @endphp
                @foreach ($rekapAbsenPeriode as $keys)
                  @if ($keys->Tanggal_Log == $tanggal)
                    @php
                      $jampulang = 140000;
                      $jamlog = (int)str_replace(':','',$keys->Jam_Log);
                    @endphp
                    @if ($jamlog>$jampulang)
                      @php
                        $flagpulang=1;
                      @endphp
                      {{$keys->Jam_Log}}
                    @endif
                  @endif
                @endforeach
                @if ($flagpulang==0)
                  x
                @endif
              </td>
            </tr>
            @php
            $no++;
            }
            @endphp
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
