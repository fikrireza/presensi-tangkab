@extends('layout.master')

@section('title')
  <title>Detail Laporan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@endsection

@section('content')

  <div class="modal fade modal-danger" id="myModalSesuai" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Data Sesuai ???</h4>
        </div>
        <div class="modal-body">
          <p>Apakah anda yakin untuk rekap tpp ini sudah sesuai?</p>
        </div>
        <div class="modal-footer">
          <button type="reset" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <a class="btn btn-danger" id="setSesuai">Ya, saya yakin</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="col-md-6">
        <div class="box box-success box-solid">
          <div class="box-header">
            <div class="box-title" style="font-size:15px;">
              Grand Total TPP Dibayarkan
            </div>
            <hr style="margin-top:10px;margin-bottom:5px;">
              <span style="font-size:28px;"><strong>Rp. {{$grandtotaltppdibayarkan}},-</strong></span>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="box box-danger box-solid">
          <div class="box-header">
            <div class="box-title" style="font-size:15px;">
              Grand Total Potongan TPP
            </div>
            <hr style="margin-top:10px;margin-bottom:5px;">
              <span style="font-size:28px;"><strong>Rp. {{$grandtotalpotongantpp}},-</strong></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($getJurnalSesuai->flag_sesuai == 0 )
  <div class="row">
    <div class="col-md-12">
      <a href="" class="btn-lg btn-block bg-maroon sesuai" data-toggle="modal" data-target="#myModalSesuai" data-value="{{ $getJurnalSesuai->id }}">Apakah Sudah Sesuai ???</a>
    </div>
  </div>
  @else
  <div class="row">
    <div class="col-md-12">
      <button class="btn-lg btn-block bg-green sesuai" disabled="">Data Sudah Sesuai.</button>
    </div>
  </div>
  @endif

  <br>

  <div class="row">
    <div class="col-md-12">
      <div class="box box-primary box-solid">
        <div class="box-header">
          <h3 class="box-title">Rekap Absensi {{ $bulan }} | {{ $getSKPDNama->nama }}</h3>
          <a href="{{ route('jurnal.index') }}" class="btn bg-blue pull-right">Kembali</a>
        </div>
        <div class="box-body table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th class="text-center">NIP</th>
                <th class="text-center">Nama</th>
                <th class="text-center">Netto TPP</th>
                <th class="text-center">TERLAMBAT (kali)</th>
                <th class="text-center">POTONGAN (2% dari 60% Netto TPP)</th>
                <th class="text-center">PULANG CEPAT (kali)</th>
                <th class="text-center">POTONGAN (2% dari 60% Netto TPP)</th>
                <th class="text-center">TERLAMBAT & PULANG CEPAT (kali)</th>
                <th class="text-center">POTONGAN (3% dari 60% Netto TPP)</th>
                <th class="text-center">TANPA KETERANGAN (kali)</th>
                <th class="text-center">POTONGAN (3% dari 100% Netto TPP)</th>
                <th class="text-center">TIDAK APEL (kali)</th>
                <th class="text-center">POTONGAN (2.5% dari 60% Netto TPP)</th>
                <th class="text-center">TIDAK APEL 4 KALI (kali)</th>
                <th class="text-center">POTONGAN (25% dari 60% Netto TPP)</th>
                <th class="text-center">TOTAL POTONGAN</th>
                <th class="text-center">TPP DIBAYARKAN</th>
              </tr>
            </thead>
            <tbody>
              @if (!isset($rekaptpp))
                <tr>
                  <td colspan="18" align="center">Pilih Periode Waktu</td>
                </tr>
              @else
                @php
                  $number = 1;
                  $arrpengecualian = array();
                  $flagpengecualiantpp = 0;
                @endphp
                @foreach ($rekaptpp as $key)
                  <tr id="row{{$key['nip']}}">
                    <td>{{$number}}</td>
                    <td>
                      <a href="{{ route('laporan.cetakPegawai', ['download'=>'pdf', 'bulanhitung'=>$bulan, 'nip_sapk'=>$key["nip"]]) }}">{{$key["nip"]}}</a>
                    </td>
                    <td>{{$key["nama"]}}</td>
                    <td>{{$key["tpp"]}}</td>
                    <td>{{$key["telat"]}}</td>
                    <td>{{$key["potongantelat"]}}</td>
                    <td>{{$key["pulangcepat"]}}</td>
                    <td>{{$key["potonganpulangcepat"]}}</td>
                    <td>{{$key["telatpulangcepat"]}}</td>
                    <td>{{$key["potongantelatpulangcepat"]}}</td>
                    <td>{{$key["tidakhadir"]}}</td>
                    <td>{{$key["potongantidakhadir"]}}</td>
                    <td>{{$key["tidakapel"]}}</td>
                    <td>{{$key["potongantidakapel"]}}</td>
                    <td>{{$key["tidakapelempat"]}}</td>
                    <td>{{$key["potongantidakapelempat"]}}</td>
                    <td>{{$key["totalpotongantpp"]}}</td>
                    <td>{{$key["totalterimatpp"]}}</td>
                  </tr>
                  @php
                    $number++;
                  @endphp
                @endforeach
              @endif
            </tbody>
          </table>
          <span>
            <i>* KETERANGAN: Baris yang berwarna hijau adalah pegawai yang dikecualikan dari potongan TPP.</i>
          </span>
        </div>
      </div>
    </div>
  </div>
{{-- @else
  Dalam maintenance..
@endif --}}
@endsection

@section('script')
<script>
$('a.sesuai').click(function(){
  var a = $(this).data('value');
  $('#setSesuai').attr('href', "{{ url('/') }}/terbit/sesuai/"+a);
});
</script>
<script>
@php
  if (isset($pengecualian)) {
    foreach ($pengecualian as $key) {
      @endphp
        $('#row{{$key}}').attr('style', 'background:#abffd8;');
      @php
    }
  }
@endphp


</script>
@endsection
