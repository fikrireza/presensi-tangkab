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
    @if (session('status') === 'administrator' || session('status') === 'admin')
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
          <h3>65<sup style="font-size: 20px"></sup></h3>
          <p>Jumlah Hadir</p>
        </div>
        {{-- <a class="small-box-footer">
          <i>Jumlah Hadir</i>
        </a> --}}
      </div>
    </div>
    @endif
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
          <h3><sup style="font-size: 20px">Rp. 12.345.678,-</sup></h3>
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
      <div class="box-body">
        @if (session('status') == 'administrator')
          <table id="table_absen" class="table table-bordered">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama SKPD</th>
                <th>Jumlah Hadir</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; ?>
              @foreach ($absensi as $key)
              <tr>
                <td>{{ $no }}</td>
                <td>{{ $key->skpd }}</td>
                <td>{{ $key->jumlah_hadir }}</td>
              </tr>
              <?php $no++; ?>
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
@endsection
