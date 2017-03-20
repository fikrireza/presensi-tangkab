@extends('layout.master')

@section('title')
  <title>Master Mutasi</title>
@endsection

@section('breadcrumb')
  <h1>Master Mutasi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Mutasi</li>
  </ol>
@endsection

@section('content')
<script>
  window.setTimeout(function() {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
    });
  }, 2000);
</script>

@if(Session::has('berhasil'))
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-success">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Berhasil!</h4>
      <p>{{ Session::get('berhasil') }}</p>
    </div>
  </div>
</div>
@endif


@if(session('status') == 'administrator')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">SKPD</h3>
      </div>
      <div class="box-body table-responsive">
        <table id="table_mutasi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>SKPD</th>
              <th style="width: 10%">Aksi</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getskpd->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($getskpd as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->skpd_nama_last }}</td>
              <td>
                <a class="btn btn-xs btn-primary" href="{{ url('mutasi/viewall', $key->skpd_new_last) }}"><i class="fa fa-eye"></i> Lihat</a>
              </td>
            </tr>
            <?php $no++; ?>
            @endforeach
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@elseif(session('status') == 'admin')
<div class="row">
  <div class="col-md-12">
    <div class="callout callout-success">
      <p><b>Nama SKPD</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{$getskpdterkait->nama}}</p>
      @if($getskpdterkait->singkatan == "")
        <p><b>Singkatan</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: -</p>
      @else
        <p><b>Singkatan</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{$getskpdterkait->singkatan}}</p>
      @endif
    </div>
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Mutasi</h3>
      </div>
      <div class="box-body table-responsive">
        <table id="table_mutasi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Asal SKPD</th>
              <th>Jumlah Mutasi</th>
              <th style="width: 10%">Aksi</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getmutasi->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
            @foreach ($getmutasi as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nip_sapk }}</td>
              <td>{{ $key->nama_pegawai }}</td>
              <td>{{ $key->nama_skpd }}</td>
              <td style="text-align: center">{{ $key->jumlahmutasi }}</td>
              <td>
                <a href="{{ url('mutasi/view', $key->pegawai_id) }}"><i class="fa fa-eye"></i> Lihat</a>
              </td>
            </tr>
            <?php $no++; ?>
            @endforeach
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@section('script')
<script type="text/javascript">
  $("#table_mutasi").DataTable();
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_mutasi tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_mutasi').DataTable();

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
