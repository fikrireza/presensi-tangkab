@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')

<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih SKPD & Periode</p>
        </div>
      </div>
      <form action="{{ route('absensi.filterAdministrator') }}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($pegawainya))
        <div class="row">
          <div class="col-xs-6">
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                @if($key->id == $skpd_id)
                <option value="{{ $key->id }}" selected="">{{ $key->nama }}</option>
                @endif
                <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xs-3">
            <input type="text" class="form-control" name="start_date" id="start_date" value="{{ $start_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
          <div class="col-xs-3">
            <input type="text" class="form-control" name="end_date" id="end_date" value="{{ $end_dateR }}" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @else
          <div class="row">
            <div class="col-xs-6">
              <select name="skpd_id" class="form-control select2">
                <option value="">--PILIH--</option>
                @foreach ($getSkpd as $key)
                  <option value="{{ $key->id }}">{{ $key->nama }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-xs-3">
              <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
            </div>
            <div class="col-xs-3">
              <input type="text" class="form-control" name="end_date" id="end_date" value="" placeholder="dd/mm/yyyy" required="">
            </div>
          </div>
        @endif
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
        <h3 class="box-title">Detil Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($pegawainya))
        <table id="table_absensi" class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Terlambat</th>
              <th>Pulang Cepat</th>
              <th>Terlambat & Pulang Cepat</th>
              <th>Tanpa Keterangan/Absen</th>
              <th>Tidak Apel</th>
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
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            @php
              $no = 1;
              $pot_absen = 0;
              $sum_totalPot = 0;
              $sum_tppDibayarkan = 0;
              $sum_GrandTotalPot = 0;
              $sum_GrandTppDibayarkan = 0;
            @endphp
            @foreach ($pegawainya as $pegawai)
            <tr>
              <td>{{ $no }}</td>
              <td><a href="{{ route('laporan.cetakPegawai', ['download'=>'pdf', 'start_date'=>$start_dateR, 'end_date'=>$end_dateR, 'nip_sapk'=>$pegawai->nip_sapk]) }}">{{ $pegawai->nip_sapk }}</a></td>
              <td>{{ $pegawai->nama }}</td>

              {{--  HITUNG TERLAMBAT dan PULANG CEPAT --}}
              @php
                $tot_pulcep_telat = 0;
                foreach ($total_telat_dan_pulcep as $tot) {
                  $pecah = explode("-", $tot);
                  if ($pegawai->fid == $pecah[0]) {
                    $tot_pulcep_telat += 1;
                  }
                }
              @endphp

              {{-- HITUNG DATANG TERLAMBAT --}}
              @php
              $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
              $date_to = strtotime($end_date); // Convert date to a UNIX timestamp
              $jam_masuk = array();
              for ($i=$date_from; $i<=$date_to; $i+=86400) {
                $tanggalini = date('d/m/Y', $i);

                foreach ($absensi as $key) {
                  if(!in_array(date('Y-m-d', $i), $hariApel)) { /* Ignore Hari Apel */
                    if($tanggalini == $key->tanggal_log){
                      if ($pegawai->fid == $key->fid) {
                        $jammasuk1 = 80000;
                        $jammasuk2 = 100000;
                        $jamlog = (int) str_replace(':','',$key->jam_log);
                        if( ($jamlog > $jammasuk1) && ($jamlog <= $jammasuk2)){
                          $jam_masuk[] = $key->fid.'-'.$tanggalini;
                        }
                      }
                    }
                  }
                }
              }
              $jumlah_telat = array_unique($jam_masuk);
              $jumlah_telat = count($jumlah_telat);
              @endphp
              <td>{{ $jumlah_telat }}</td>

              {{--  HITUNG PULANG CEPAT --}}
              @php
              $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
              $date_to = strtotime($end_date); // Convert date to a UNIX timestamp
              $jam_pulang = array();
              for ($i=$date_from; $i<=$date_to; $i+=86400) {
                $tanggalini = date('d/m/Y', $i);

                foreach ($absensi as $key) {
                  if($tanggalini == $key->tanggal_log){
                    if ($pegawai->fid == $key->fid) {
                      $jampulang1 = 140000;
                      $jampulang2 = 160000;
                      $jamlog = (int) str_replace(':','',$key->jam_log);
                      if(($jamlog >= $jampulang1) && ($jamlog < $jampulang2)){
                        $jam_pulang[] = $key->fid.'-'.$tanggalini;
                      }
                    }
                  }
                }
              }
              $jumlah_cepat = array_unique($jam_pulang);
              $jumlah_cepat = count($jumlah_cepat);
              @endphp
              <td>{{ $jumlah_cepat }}</td>

              <td>{{ $tot_pulcep_telat }}</td>

              {{-- Menghitung Jumlah Intervensi --}}
              @php
                $intervensiHasil = array();
              @endphp
              @foreach ($intervensi as $ijin)
                @php
                if($pegawai->pegawai_id == $ijin->pegawai_id){
                    $tanggal_mulai = $ijin->tanggal_mulai;
                    $tanggal_akhir = $ijin->tanggal_akhir;
                    $mulai = new DateTime($tanggal_mulai);
                    $akhir   = new DateTime($tanggal_akhir);

                    for($i = $mulai; $mulai <= $akhir; $i->modify('+1 day'))
                    {
                      $intervensiHasil[] =  $i->format("Y-m-d");
                    }
                  }
                @endphp
              @endforeach

              {{-- Menghitung Jumlah Bolos --}}
              @foreach ($jumlahMasuk as $jmlMasuk)
                @if ($pegawai->nip_sapk == $jmlMasuk->nip_sapk)
                  @php
                  $workingDays = [1, 2, 3, 4, 5]; # date format = N (1 = Senin, ...)
                  $holidayDays = array_merge($hariLibur, $intervensiHasil, $hariApel);

                  $from = new DateTime($start_date);
                  $to = new DateTime($end_date);
                  $to->modify('+1 day');
                  $interval = new DateInterval('P1D');
                  $periods = new DatePeriod($from, $interval, $to);

                  $days = 0;
                  foreach ($periods as $period) {
                    if (!in_array($period->format('N'), $workingDays)) continue;
                    if (in_array($period->format('Y-m-d'), $holidayDays)) continue;
                    if (in_array($period->format('*-m-d'), $holidayDays)) continue;
                    $days++;
                  }

                  // $intervensi = count($intervensiHasil);
                  $jumlah_masuknya = (int)$jmlMasuk->Jumlah_Masuk - (count($intervensiHasil) + count($hariApel));
                  $jumlahAbsen = (int)$days - $jumlah_masuknya;

                  echo '<td>'.$jumlahAbsen.'</td>';
                  @endphp
                @endif
              @endforeach

              {{--  MENGHITUNG TIDAK APEL --}}
              @php
              $date_from = strtotime($start_date); // Convert date to a UNIX timestamp
              $date_to = strtotime($end_date); // Convert date to a UNIX timestamp
              $tidak_apel = 0;
              for ($i=$date_from; $i<=$date_to; $i+=86400) {
                $tanggalini = date('d/m/Y', $i);

                foreach ($absensi as $key) {
                  if(in_array(date('Y-m-d', $i), $hariApel)) { /* Hanya Hari Apel */
                    if($tanggalini == $key->tanggal_log){
                      if ($pegawai->fid == $key->fid) {
                        $jamapel1 = 80000;
                        $jamapel2 = 100000;
                        $jamlog = (int) str_replace(':','',$key->jam_log);
                        if( ($jamlog > $jamapel1) && ($jamlog <= $jamapel2)){
                          $tidak_apel += 1;
                        }
                      }
                    }
                  }
                }
              }
              @endphp

              <td>{{ $tidak_apel }}</td>
            </tr>
            @php
              $no++
            @endphp
            @endforeach
          </tbody>
        </table>
        @else
        <table id="table_absensi" class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Terlambat</th>
              <th>Pulang Cepat</th>
              <th>Terlambat & Pulang Cepat</th>
              <th>Tanpa Keterangan/Absen</th>
              <th>Tidak Apel</th>
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
              <th></th>
            </tr>
          </tfoot>
          <tbody>
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
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
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
$(".select2").select2();
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
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_absensi tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );
   
      // DataTable
      var table = $('#table_absensi').DataTable();
   
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
