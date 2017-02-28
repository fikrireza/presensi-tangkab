@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Tanggal Absen</p>
        </div>
      </div>
      <form action="{{ route('absenhari.administratorstore')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($pegawainya))
        <div class="row">
          <div class="col-xs-8">
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                <option value="{{ $key->id }}" @if($key->id == $skpd_id) selected="" @endif>{{ $key->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xs-4">
            <input type="text" class="form-control" name="start_date" id="start_date" value="{{ $tanggalini }}" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
        @else
        <div class="row">
          <div class="col-xs-8">
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xs-4">
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
                   ?>
                <td>{{ $dayList[$day] }}</td>
                <td>{{ $tanggalini }}</td>
                <td>
                  @php
                    $flagmasuk=0;
                  @endphp
                  @foreach ($absensi as $keys)
                    @if ($keys->fid == $key->fid)
                      @php
                        $jammasuk_upper = 100000;
                        $jammasuk_lower = 70000;
                        $jamlog = (int) str_replace(':','',$keys->jam_log);
                      @endphp
                      @if ($jamlog<$jammasuk_upper && $jamlog>$jammasuk_lower)
                        @php
                          $flagmasuk=1;
                        @endphp
                        {{$keys->jam_log}}
                        @php
                          break;
                        @endphp
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
                  @foreach ($absensi as $keys)
                    @if ($keys->fid == $key->fid)
                      @php
                        $jampulang_upper = 140000;
                        $jamlog = (int)str_replace(':','',$keys->jam_log);
                      @endphp
                      @if ($jamlog>$jampulang_upper)
                        @php
                          $flagpulang=1;
                        @endphp
                        {{$keys->jam_log}}
                        @php
                          break;
                        @endphp
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
              @endphp
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
