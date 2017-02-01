<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div class="row">
  <div class="col-md-12">
    <h2 style="font-size:33px;">REKAP ABSENSI APEL {{$getDetail[0]->nama_skpd}}</h2>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title" style="font-size:29px;">PERIODE TANGGAL {{ $tanggalApel }}</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">
          <tr align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">
            <th width="40px" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">No</th>
            <th width="250px" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">NIP</th>
            <th width="420px" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Nama</th>
            <th width="30px" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Struktural</th>
            <th width="50px" align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">Jam Absen</th>
          </tr>
          @php
            $no=1;
          @endphp
          @foreach ($getDetail as $key)
            <tr align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 28px;">
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 25px;">{{$no}}</td>
              <td style="border: 1px solid black;border-collapse: collapse;font-size: 25px;">{{ $key->nip_sapk }}</td>
              <td align="left" style="border: 1px solid black;border-collapse: collapse;font-size: 25px;">&nbsp;{{ $key->pegawai }}</td>
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 25px;">{{ $key->struktural }}</td>
              <td align="center" style="border: 1px solid black;border-collapse: collapse;font-size: 25px;">@if ($key->Jam_Log == null) x @else {{ $key->Jam_Log }} @endif</td>
            </tr>
            @php
              $no++;
            @endphp
          @endforeach
        </table>
      </div>
    </div>
  </div>
</div>
