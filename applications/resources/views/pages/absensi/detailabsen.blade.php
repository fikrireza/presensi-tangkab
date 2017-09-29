@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
@endsection

@section('breadcrumb')
  <h1>Detail Absensi</h1>
  <ol class="breadcrumb">
    <li><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Detail Absensi</li>
  </ol>
@endsection

@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Detail Absensi {{$getskpd->nama}}</h3>
        <a href="{{ route('home') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_user" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              {{-- <th>Hari</th> --}}
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
            </tr>
          </tfoot>
          <tbody>
            @php
              $no=1;
            @endphp
            {{-- @foreach ($pegawai as $key)
              <tr>
                <td>{{$no}}</td>
                <td>{{ $key->nama }}</td>
                <td>
                  @php
                    echo date('d/m/Y');
                  @endphp
                </td>
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
            @endforeach --}}
            @foreach ($logBaru as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nama }}</td>
              <td>{{ $key->tanggal }}</td>
              <td>{{ $key->jam_datang ? $key->jam_datang : 'x' }}</td>
              <td>{{ $key->jam_pulang ? $key->jam_pulang : 'x'}}</td>
            </tr>
            @php
              $no++;
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
  $(function () {
    $("#table_user").DataTable();
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
