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
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-maroon">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. {{ number_format($jumlahTPPDibayarkan[0]->jumlah_tpp,0,',','.')}},-</sup></h3>
          <p>Yang Dibayarkan</p>
        </div>
      </div>
    </div>
    @endif
    @if (session('status') == 'pegawai' || session('status') == 'bpkad')
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-purple">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. {{ number_format($tpp->tpp_dibayarkan,0,',','.') }},-</sup></h3>
          <p>Jumlah TPP</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-maroon">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. 0,-</sup></h3>
          <p>Yang Dibayarkan</p>
        </div>
      </div>
    </div>
    @endif
    @if (session('status') == 'bpkad')
    <div class="col-lg-3 col-md-3 col-xs-12">
      <div class="small-box bg-maroon">
        <div class="inner">
          <h3><sup style="font-size: 20px">Rp. {{ number_format($jumlahTPPDibayarkan[0]->jumlah_tpp,0,',','.')}},-</sup></h3>
          <p>Yang Dibayarkan</p>
        </div>
      </div>
    </div>
    @endif
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
              <td>@if($key->jam_datang != null) {{ $key->jam_datang }} @else x @endif</td>
              <td>@if($key->jam_pulang != null) {{ $key->jam_pulang }} @else x @endif</td>
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
              {{-- <th>Tanggal Update</th> --}}
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
              {{-- <th></th> --}}
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @foreach ($totalBaru as $key)
            <tr>
              <td>{{ $no }}</td>
              <td><a href="{{ route('detail.absensi', ['id' => $key->id])}}">{{$key->nama_skpd}}</a></td>
              <td>{{ $key->jumlah_pegawai }}</td>
              <td>{{ $key->jumlah_hadir }}</td>
              <td>{{ $key->jumlah_bolos }}</td>
              <td>{{ $key->jumlah_intervensi }}</td>
              {{-- <td>{{ $key->last_update }}</td> --}}
            </tr>
            @php
              $no++
            @endphp
            @endforeach
          </tbody>
        </table>
        @elseif(session('status') == 'pegawai' || session('status') == 'bpkad')
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
                  @if ($absen->tanggal == $tanggal)
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
                    <td align="center">x</td>
                    <td align="center">x</td>
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
                    <td align="center">Libur</td>
                  @else
                    @foreach ($absensi as $absen)
                      @if ($absen->tanggal == $tanggal)
                        @php
                          $flag++;
                        @endphp

                        @php
                        if (!in_array($absen->tanggal, $tanggalapel)) {
                          $tglnew = explode('/', $absen->tanggal);
                          $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];
                          // --- CHECK FRIDAY DATE ---
                          if ((date('N', strtotime($tglformat)) != 5)) {
                            $lower_telatdtg = 80100;
                            $upper_telatdtg = 90100;
                            $lower_plgcepat = 150000;
                            $upper_plgcepat = 160000;
                            $batas_jamdtg = 70000;
                            $batas_jamplg = 190000;

                            if (in_array($absen->tanggal, $ramadhanformatslash)) {
                              $upper_plgcepat = 150000;
                            }

                            $rawjamdtg = $absen->jam_datang;
                            $jamdtg = str_replace(':', '', $rawjamdtg);
                            $rawjamplg = $absen->jam_pulang;
                            $jamplg = str_replace(':', '', $rawjamplg);

                            if ($absen->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'> Alpa </td>";
                              }
                            } else if ($absen->jam_pulang==null || $jamplg > $batas_jamplg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'> Alpa</td>";
                              }
                            } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || $jamplg < $upper_plgcepat)) {
                              $intertelat = 0;
                              $interpulcep = 0;
                              $interbebas = 0;
                              if (in_array($absen->tanggal, $tanggalintervensibebas)) $interbebas++;
                              if (in_array($absen->tanggal, $tanggalintervensitelat)) $intertelat++;
                              if (in_array($absen->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                              if ($interbebas==0) {
                                if ($intertelat==0 && $interpulcep==0) {
                                  echo "<td align='center'> Terlambat & Pulang Cepat </td>";
                                } else if ($intertelat!=0 && $interpulcep==0) {
                                  echo "<td align='center'> Pulang Cepat </td>";
                                } else if ($intertelat==0 && $interpulcep!=0) {
                                  echo "<td align='center'> Terlambat </td>";
                                }
                              }
                            } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'> Terlambat </td>";
                              }
                            } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'> Pulang Cepat </td>";
                              }
                            }
                          } else {
                            $lower_telatdtg = 73100;
                            $upper_telatdtg = 83100;
                            $lower_plgcepat = 150000;
                            $upper_plgcepat = 160000;
                            $batas_jamdtg = 63000;
                            $batas_jamplg = 190000;

                            if (in_array($absen->tanggal, $ramadhanformatslash)) {
                              $lower_telatdtg = 80100;
                              $upper_telatdtg = 90100;
                              $upper_plgcepat = 153000;
                            }

                            $rawjamdtg = $absen->jam_datang;
                            $jamdtg = str_replace(':', '', $rawjamdtg);
                            $rawjamplg = $absen->jam_pulang;
                            $jamplg = str_replace(':', '', $rawjamplg);

                            if ($absen->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'> Alpa </td>";
                              }
                            } else if ($absen->jam_pulang==null || $jamplg > $batas_jamplg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'> Alpa </td>";
                              }
                            } else if (($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) && (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || $jamplg < $upper_plgcepat)) {
                              $intertelat = 0;
                              $interpulcep = 0;
                              $interbebas = 0;
                              if (in_array($absen->tanggal, $tanggalintervensibebas)) $interbebas++;
                              if (in_array($absen->tanggal, $tanggalintervensitelat)) $intertelat++;
                              if (in_array($absen->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                              if ($interbebas==0) {
                                if ($intertelat==0 && $interpulcep==0) {
                                  echo "<td align='center'> Terlambat & Pulang Cepat </td>";
                                } else if ($intertelat!=0 && $interpulcep==0) {
                                  echo "<td align='center'> Pulang Cepat </td>";
                                } else if ($intertelat==0 && $interpulcep!=0) {
                                  echo "<td align='center'> Terlambat </td>";
                                }
                              }
                            } else if ($jamdtg > $lower_telatdtg && $jamdtg < $upper_telatdtg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'> Terlambat </td>";
                              }
                            } else if (($jamplg > $lower_plgcepat && $jamplg < $upper_plgcepat) || (($jamdtg > $batas_jamdtg && $jamdtg < $lower_telatdtg) && $jamplg < $upper_plgcepat)) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'> Pulang Cepat </td>";
                              }
                            }
                          }
                        } else {
                          $tglnew = explode('/', $absen->tanggal);
                          $tglformat = $tglnew[2].'-'.$tglnew[1].'-'.$tglnew[0];

                          $maxjamdatang = 83100;
                          $upper_telatdtg = 90100;
                          $lower_plgcepat = 150000;
                          $upper_plgcepat = 160000;
                          $batas_jamdtg = 70000;
                          $batas_jamplg = 190000;

                          if (in_array($absen->tanggal, $ramadhanformatslash)) {
                            $upper_plgcepat = 150000;
                          }


                          $rawjamdtg = $absen->jam_datang;
                          $jamdtg = str_replace(':', '', $rawjamdtg);
                          $rawjamplg = $absen->jam_pulang;
                          $jamplg = str_replace(':', '', $rawjamplg);

                          if (in_array($absen->mach_id, $mesinapel)) {
                            if ($absen->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'>Alpa</td>";
                              }
                            } else if ($absen->jam_pulang==null || $jamplg > $batas_jamplg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'>Alpa</td>";
                              }
                            } else if ($jamdtg > $maxjamdatang && $jamplg > $upper_plgcepat) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'>Tidak Apel</td>";
                              }
                            } else if ($jamdtg > $maxjamdatang && $jamplg < $upper_plgcepat) {
                              $intertelat = 0;
                              $interpulcep = 0;
                              $interbebas = 0;
                              if (in_array($absen->tanggal, $tanggalintervensibebas)) $interbebas++;
                              if (in_array($absen->tanggal, $tanggalintervensitelat)) $intertelat++;
                              if (in_array($absen->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                              if ($interbebas==0) {
                                if ($intertelat==0 && $interpulcep==0) {
                                  echo "<td align='center'>Terlambat dan Pulang Cepat</td>";
                                } else if ($intertelat!=0 && $interpulcep==0) {
                                  echo "<td align='center'>Pulang Cepat</td>";
                                } else if ($intertelat==0 && $interpulcep!=0) {
                                  echo "<td align='center'>Terlambat</td>";
                                }
                              }
                            } else if ((($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg < $upper_plgcepat) || (($jamdtg < $maxjamdatang && $jamdtg > $batas_jamdtg) && $jamplg==null)) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'>Pulang Cepat</td>";
                              }
                            }
                          } else {
                            if ($absen->jam_datang==null || $jamdtg < $batas_jamdtg || $jamdtg > $upper_telatdtg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'>Alpa</td>";
                              }
                            } else if ($absen->jam_pulang==null || $jamplg > $batas_jamplg) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensipulcep))) {
                                echo "<td align='center'>Alpa</td>";
                              }
                            } else if (($jamdtg <= $maxjamdatang || $jamdtg >= $maxjamdatang) && $jamplg > $upper_plgcepat) {
                              if ((!in_array($absen->tanggal, $tanggalintervensibebas)) && (!in_array($absen->tanggal, $tanggalintervensitelat))) {
                                echo "<td align='center'>Tidak Apel</td>";
                              }
                            } else if ($jamdtg > $maxjamdatang && $jamplg < $upper_plgcepat) {
                              $intertelat = 0;
                              $interpulcep = 0;
                              $interbebas = 0;
                              if (in_array($absen->tanggal, $tanggalintervensibebas)) $interbebas++;
                              if (in_array($absen->tanggal, $tanggalintervensitelat)) $intertelat++;
                              if (in_array($absen->tanggal, $tanggalintervensipulcep)) $interpulcep++;
                              if ($interbebas==0) {
                                if ($intertelat==0 && $interpulcep==0) {
                                  echo "<td align='center'>Terlambat dan Pulang Cepat</td>";
                                } else if ($intertelat!=0 && $interpulcep==0) {
                                  echo "<td align='center'>Pulang Cepat</td>";
                                } else if ($intertelat==0 && $interpulcep!=0) {
                                  echo "<td align='center'>Terlambat</td>";
                                }
                              }
                            }
                          }
                        }
                        @endphp
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
