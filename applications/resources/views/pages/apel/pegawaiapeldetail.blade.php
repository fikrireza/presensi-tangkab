@extends('layout.master')

@section('title')
  <title>Detail Absensi Apel</title>
@endsection

@section('breadcrumb')
  <h1>Detail Absensi Apel</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Detail Absensi Apel</li>
  </ol>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Detail Absensi Apel {{$getDetail[0]->nama_skpd}} - {{ $tanggalApel }}</h3>
        <a href="{{ route('pegawaiapel.detailCetak', ['download' => 'pdf', 'skpd' => $skpd, 'tanggalApel' => $tanggalApelnya]) }}" class="btn bg-green pull-right">Cetak</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_user" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Struktural</th>
              <th>Jam Absen</th>
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
            @foreach ($getDetail as $key)
              <tr>
                <td>{{$no}}</td>
                <td>{{ $key->nip_sapk }}</td>
                <td>{{ $key->pegawai }}</td>
                <td>{{ $key->struktural }}</td>
                <td>@if ($key->Jam_Log == null) x @else {{ $key->Jam_Log }} @endif</td>
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
