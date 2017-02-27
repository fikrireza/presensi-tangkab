@extends('layout.master')

@section('title')
  <title>Daftar Apel Pegawai</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')
<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Tanggal Apel</p>
        </div>
      </div>
      <form action="{{ route('apelskpd.store')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        @if(isset($tanggalApel))
        <div class="row">
          <div class="col-xs-12">
            <select name="apel_id" class="form-control select2" required="">
              <option value="">--PILIH--</option>
              @foreach ($getApel as $key)
                @if($key->id == $tanggalApel->id)
                <option value="{{ $key->id }}" selected="">{{ date("d-m-Y", strtotime($key->tanggal_apel)) }} => {{ $key->keterangan }}</option>
                @endif
                <option value="{{ $key->id }}">{{ date("d-m-Y", strtotime($key->tanggal_apel)) }} => {{ $key->keterangan }}</option>
              @endforeach
            </select>
          </div>
        </div>
        @else
          <div class="row">
            <div class="col-xs-12">
              <select name="apel_id" class="form-control select2" required="">
                <option value="">--PILIH--</option>
                @foreach ($getApel as $key)
                <option value="{{ $key->id }}">{{ date("d-m-Y", strtotime($key->tanggal_apel)) }} => {{ $key->keterangan }}</option>
                @endforeach
              </select>
            </div>
          </div>
        @endif
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
        @if (isset($tanggalApel))
          <a href="{{ route('pegawaiapel.detailCetak', ['download' => 'pdf', 'skpd' => $skpd, 'tanggalApel' => $tanggalApel]) }}" class="btn btn-block bg-green">Download PDF</a>
        @endif
      </div>
      </form>
    </div>
  </div>
</div>

@if(isset($tanggalApel))
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Detail Absensi Apel {{$getDetail[0]->nama_skpd}} - {{ $tanggalApelnya }}</h3>
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
@endif
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
