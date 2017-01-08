@extends('layout.master')

@section('title')
  <title>Detail Laporan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@endsection

@section('content')

<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Periode</p>
        </div>
      </div>
      <form action="{{ route('laporanPegawai.store')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($rekapAbsenPeriode))
        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="start_date" id="start_date" value="{{ $start_dateR }}" placeholder="dd/mm/yyyy" required="">
            <input type="hidden" name="nip_sapk" value="{{ Auth::user()->nip_sapk }}" />
          </div>
          <div class="col-xs-6">
            <input type="text" class="form-control" name="end_date" id="end_date" value="{{ $end_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @else
        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
            <input type="hidden" name="nip_sapk" value="{{ Auth::user()->nip_sapk }}" />
          </div>
          <div class="col-xs-6">
            <input type="text" class="form-control" name="end_date" id="end_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @endif
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
        @if (isset($rekapAbsenPeriode))
          <a href="{{ route('laporan.cetakPegawai', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR, 'nip_sapk'=>$nip_sapk]) }}" class="btn btn-block bg-green">Download PDF</a>
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
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($rekapAbsenPeriode))
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th class="text-center">Tanggal</th>
              <th class="text-center">Hari</th>
              <th class="text-center">Jam Datang</th>
              <th class="text-center">Jam Pulang</th>
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
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $tanggal = date("d/m/Y", $i) }}</td>
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
              <td>{{ $dayList[$day] }}</td>
              @foreach ($hariLibur as $libur)
                @if ($libur->libur == date("Y-m-d", $i))
                  <td colspan="2" align="center">{{ $libur->keterangan }}</td>
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
                  <td colspan="2" align="center">Libur</td>
                  @break
                @else
                @for($tglInterv = $mulai; $mulai <= $akhir; $tglInterv->modify('+1 day'))
                  @if ($tanggal == $tglInterv->format("d/m/Y"))
                    @php
                    $flag++;
                    $flaginter++;
                    @endphp
                  <td colspan="2" align="center">{{ $interv->deskripsi }}</td>
                  @endif
                @endfor
                @endif
              @endforeach
              <td>
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
              <td>
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

        @else
        <table class="table table-bordered">
          <thead>
            <tr>
              <tr>
                <th class="text-center">No</th>
                <th class="text-center">Tanggal</th>
                <th class="text-center">Hari</th>
                <th class="text-center">Jam Datang</th>
                <th class="text-center">Jam Pulang</th>
              </tr>
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
