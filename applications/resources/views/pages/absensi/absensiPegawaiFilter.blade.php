@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
@endsection

@section('content')

<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Bulan</p>
        </div>
      </div>
      <form action="{{ route('absensi.filterMonth') }}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="pilih_bulan" id="pilih_bulan" value="{{ $month }}" placeholder="mm" required="">
          </div>
        </div>
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
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
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Hari</th>
              <th>Jam Datang</th>
              <th>Jam Pulang</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp
            @foreach ($tanggalBulan as $tanggal)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $tanggal }}</td>
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

              @php
                $flag=0;
              @endphp
              @foreach ($absensi as $absen)

                @foreach ($absen as $key)
                  @if ($key->Tanggal_Log == $tanggal)
                    @php
                      $flag++;
                    @endphp
                    <td align="center">@if($key->Jam_Datang != null) {{ $key->Jam_Datang }} @else x @endif</td>
                    <td align="center">@if($key->Jam_Pulang != null) {{ $key->Jam_Pulang }} @else x @endif</td>
                  @endif
                @endforeach

                @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                  @php
                    $flag++;
                  @endphp
                  <td colspan="2" align="center">Libur</td>
                  @break
                @endif

                @foreach ($hariLibur as $libur)
                  @php
                  $holiday = explode('-', $libur->libur);
                  $holiday = $holiday[2]."/".$holiday[1]."/".$holiday[0];
                  @endphp
                  @if($holiday == $tanggal)
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center">{{ $libur->keterangan }}</td>
                  @endif
                @endforeach

                @foreach ($intervensi as $interv)
                  @php
                  $mulai = explode('-', $interv->tanggal_mulai);
                  $mulai = $mulai[2]."/".$mulai[1]."/".$mulai[0];
                  $akhir = explode('-', $interv->tanggal_akhir);
                  $akhir = $akhir[2]."/".$akhir[1]."/".$akhir[0];
                  @endphp
                  @if($tanggal == $mulai)
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center">{{ $interv->deskripsi }}</td>
                  @endif
                  @if($tanggal == $akhir)
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center">{{ $interv->deskripsi }}</td>
                  @endif
                @endforeach
              @endforeach
              @if ($flag==0)
                <td></td>
                <td></td>
              @endif
            </tr>
            @php
              $no++
            @endphp
            @endforeach
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
$('#pilih_bulan').datepicker({
    autoclose: true,
    format: 'mm',
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    viewMode: "months",
    minViewMode: "months"
   });
</script>
@endsection
