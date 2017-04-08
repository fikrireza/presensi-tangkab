@extends('layout.master')

@section('title')
  <title>Presensi Online</title>
@endsection

@section('content')
<div class="col-md-12">
  @if(Session::has('berhasil'))
    <div class="alert alert-success panjang">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Selamat Datang!</h4>
      <p>{{ Session::get('berhasil') }}</p>
    </div>
  @endif
</div>

<div class="row">
  <div class="col-md-12">
    @if (session('status') === 'administrator' || session('status') === 'admin'  || session('status') == 'superuser' || session('status') == 'sekretaris')
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-teal">
        <div class="inner">
          <h3>{{ $jumlahPegawai }}</h3>
          <p>Jumlah Pegawai</p>
        </div>
        {{-- <a href="" class="small-box-footer">Lihat Data Selengkapnya <i class="fa fa-arrow-circle-right"></i></a> --}}
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-yellow">
        <div class="inner">
          <h3>@if ($totalHadir != null)
                {{ $totalHadir }}<sup style="font-size: 20px"></sup>
              @else
                -
            @endif
          </h3>
          <p>Jumlah Hadir</p>
        </div>
        {{-- <a class="small-box-footer">
          <i>Jumlah Hadir</i>
        </a> --}}
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-purple">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. {{ number_format($jumlahTPP[0]->jumlah_tpp,0,',','.') }},-</sup></h3>
          <p>Jumlah TPP</p>
        </div>
      </div>
    </div>
    @endif
    @if (session('status') == 'pegawai')
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-purple">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. {{ number_format($tpp->tpp_dibayarkan,0,',','.') }},-</sup></h3>
          <p>Jumlah TPP</p>
        </div>
      </div>
    </div>
    @endif
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-maroon">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. 0,-</sup></h3>
          <p>Yang Dibayarkan</p>
        </div>
      </div>
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
        @if (session('status') == 'admin')
        <table id="table_absen" class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Hari</th>
              <th>Tanggal</th>
              <th>Jam Datang</th>
              <th>Jam Pulang</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($absensi as $key)
            <tr>
              <td>{{ $no }}</td>
              <?php
                $day = date('d/m/Y');
                $day = explode('/', $day);
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
                 ?>
              <td>{{ $key->nama_pegawai }}</td>
              <td>{{ $dayList[$day] }}</td>
              <td>{{ $today = date('d/m/Y')}}</td>
              <td>@if($key->Jam_Datang != null) {{ $key->Jam_Datang }} @else x @endif</td>
              <td>@if($key->Jam_Pulang != null) {{ $key->Jam_Pulang }} @else x @endif</td>
            </tr>
            <?php $no++; ?>
            @endforeach
          </tbody>
        </table>
        @elseif(session('status') == 'administrator'  || session('status') == 'superuser' || session('status') == 'sekretaris')
        <table id="table_absen" class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>SKPD</th>
              <th>Jumlah Pegawai</th>
              <th>Jumlah Hadir</th>
              <th>Jumlah Absen</th>
              <th>Jumlah Intervensi</th>
              <th>Tanggal Update</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($skpdall as $key)
              <tr>
                <td>{{$no}}</td>
                <td><a href="{{ route('detail.absensi', ['id' => $key->id_skpd])}}">{{$key->nama_skpd}}</a></td>
                <td>
                  @if (!is_null($key->nama_pegawai))
                    {{$key->jumlah_pegawai}}
                  @else
                    0
                  @endif
                </td>
                <td>
                  @php
                    $flagjumlahhadir=0;
                    $jumlahhadir=0;
                  @endphp
                  @foreach ($absensi as $keys)
                    @if ($keys->id == $key->id_skpd)
                      {{$keys->jumlah_hadir}}
                      @php
                        $flagjumlahhadir=1;
                        $jumlahhadir = $keys->jumlah_hadir
                      @endphp
                    @endif
                  @endforeach
                  @if ($flagjumlahhadir==0)
                    {{$flagjumlahhadir}}
                  @endif
                </td>
                <td>
                  @php
                    $count=0;
                  @endphp
                  @foreach ($pegawai as $keys)
                    @if ($key->nama_skpd == $keys->nama_skpd)
                      @php
                        $count++;
                      @endphp
                    @endif
                  @endforeach
                  @php
                    $jumlahabsen = $count - $jumlahhadir;
                    echo $jumlahabsen;
                  @endphp
                </td>
                <td>
                  @php
                    $countintervensi = 0;
                  @endphp
                  @foreach ($jumlahintervensi as $keys)
                    @if ($keys->nama == $key->nama_skpd)
                      @php
                        $countintervensi++;
                      @endphp
                    @endif
                  @endforeach
                  {{$countintervensi}}
                </td>
                <td>
                  @php
                    $flagtanggalupdate=0;
                  @endphp
                  @foreach ($lastUpdate as $update)
                    @if ($update->id == $key->id_skpd)
                      {{ date("d-m-Y H:i:s", strtotime($update->last_update))}}
                      @php
                        $flagtanggalupdate=1;
                      @endphp
                    @endif
                  @endforeach
                  @if ($flagtanggalupdate==0)
                    -
                  @endif
                </td>
              </tr>
              @php
                $no++;
              @endphp
            @endforeach

            {{-- KODE YANG LAMA CUUY, SEBELUM REVISI BANG BANG.. --}}
            {{-- @foreach ($absensi as $key)
              <tr>
                <td>{{ $no }}</td>
                <td><a href="{{route('detail.absensi', $key->id)}}">{{ $key->skpd }}</a></td>
                @foreach ($jumlahPegawaiSKPD as $pegSKPD)
                  @if($key->id == $pegSKPD->skpd_id)
                    <td>{{ $pegSKPD->jumlah_pegawai }}</td>
                  @endif
                @endforeach
                <td>{{ $key->jumlah_hadir }}</td>
                <td>
                  @php
                    $count=0;
                  @endphp
                  @foreach ($pegawai as $keys)
                    @if ($key->skpd == $keys->nama_skpd)
                      @php
                        $count++;
                      @endphp
                    @endif
                  @endforeach
                  @php
                    $jumlahabsen = $count - $key->jumlah_hadir;
                    echo $jumlahabsen;
                  @endphp
                </td>
                <td>
                  @php
                    $countintervensi = 0;
                  @endphp
                  @foreach ($jumlahintervensi as $keys)
                    @if ($keys->nama == $key->skpd)
                      @php
                        $countintervensi++;
                      @endphp
                    @endif
                  @endforeach
                  {{$countintervensi}}
                </td>
                <td>@foreach ($lastUpdate as $update)
                  @if ($update->id == $key->id)
                    {{ $update->last_update }}
                  @endif
                @endforeach</td>
              </tr>
              @php
                $no++;
              @endphp
            @endforeach --}}
          </tbody>
        </table>

        @elseif(session('status') == 'pegawai')
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Jam Datang</th>
                <th>Jam Pulang</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <td></td>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </tfoot>
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

                @foreach ($hariLibur as $lib)
                  @php
                    $holiday = explode('-', $lib->libur);
                    $holiday = $holiday[2]."/".$holiday[1]."/".$holiday[0];
                  @endphp
                  @if($holiday == $tanggal)
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center">{{ $lib->keterangan }}</td>
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
                    <td align="center">@if($absen->jam_datang != null) {{ $absen->jam_datang }} @else x @endif</td>
                    <td align="center">@if($absen->jam_pulang != null) {{ $absen->jam_pulang }} @else x @endif</td>
                  @endif

                  @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center">Libur</td>
                    @break
                  @endif
                @endforeach

                @if ($flag==0)
                  @if ($tanggal > date("d/m/Y"))
                    <td align="center">x</td>
                    <td align="center">x</td>
                  @else
                    <td colspan="2" align="center">Alpa</td>
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
                      <td align="center"><b>{{ $interv->jenis_intervensi}}</b> | {{ $interv->deskripsi }}</td>
                    @endif
                  @endfor
                @endforeach

                @if ($flaginter==0 && $flag==0)
                <td align="center"><span style="color:red;"><b>Alpa</b></span></td>
                @elseif($flaginter==0)
                  @if (($dayList[$day] == 'Sabtu') || ($dayList[$day] == 'Minggu'))
                    @php
                      $flag++;
                    @endphp
                    <td colspan="2" align="center">Libur</td>
                  @else
                    @foreach ($absensi as $absen)
                      @if ($absen->tanggal == $tanggal)
                        @php
                          $flag++;
                        @endphp

                        @if($absen->jam_datang >= '09:01:00')
                          <td align="center"><b>Alpa</b></td>
                        @elseif($absen->jam_pulang == null)
                          <td align="center"><b>Alpa</b></td>
                        @elseif (($absen->jam_datang >= '08:01:00') && ($absen->jam_pulang <= '15:59:00'))
                          <td align="center"><b>Terlambat dan Pulang Cepat</td>
                        @elseif (($absen->jam_datang >= '08:01:00') && ($absen->jam_datang <= '09:00:00') && ($absen->jam_pulang == null))
                          <td align="center"><b>Terlambat dan Pulang Cepat</td>
                        @elseif($absen->jam_datang >= '08:01:00')
                          <td align="center"><b>Terlambat</b></td>
                        @elseif($absen->jam_pulang <= '15:01:00')
                          <td align="center"><b>Pulang Cepat</b></td>
                        @else
                          <td align="center"></td>
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
  $(function(){
    $("#table_absen").DataTable();
  });
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_absen tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_absen').DataTable();

      // Apply the search
      table.columns().every( function () {
          var that = this;

          $( 'input', this.footer() ).on( 'keyup change', function () {
              if ( that.search() !== this.value ) {
                  that
                      .search( this.value )
                      .draw();
              }
          } );
      } );
  } );
</script>
@endsection
