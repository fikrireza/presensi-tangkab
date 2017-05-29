@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
@endsection

@section('content')
<div class="row">
  <div class="col-md-4 col-md-offset-4">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Tanggal Absen</p>
        </div>
      </div>
      <form action="{{ route('absenhari.skpdStore')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($pegawainya))
        <div class="row">
          <div class="col-xs-12">
            <input type="text" class="form-control" name="start_date" id="start_date" value="{{ $tanggalini }}" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @else
        <div class="row">
          <div class="col-xs-12">
            <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
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
        <h3 class="box-title">Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        @if(isset($pegawainya))
        <table id="table_user" class="table table-bordered table-striped">
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
            @php
              $no=1;
            @endphp
            @foreach ($pegawainya as $key)
              <tr>
                <td>{{$no}}</td>
                <td>{{ $key->nama }}</td>
                <?php
                  $day = $tanggalini;
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
                  $flagAbsen = 0;
                  $no++;
                   ?>
                <td>{{ $dayList[$day] }}</td>
                <td>{{ $tanggalini }}</td>
                @foreach ($absensi as $absen)
                  @if ($absen->fid == $key->fid)
                    @php
                      $flagAbsen++;
                    @endphp
                  <td align="center">{{ $absen->jam_datang == null ? 'x' : $absen->jam_datang }}</td>
                  <td align="center">{{ $absen->jam_pulang == null ? 'x' : $absen->jam_pulang }}</td>
                  @endif

                @endforeach
                @if ($flagAbsen==0)
                  <td align="center">x</td>
                  <td align="center">x</td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>
        @else
          <table id="table_user" class="table table-bordered table-striped">
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
              <tr>
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
<script>
$('#start_date').datepicker({
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
      $('#table_user tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_user').DataTable();

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
