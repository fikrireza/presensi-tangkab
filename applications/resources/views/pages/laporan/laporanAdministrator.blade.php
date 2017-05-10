@extends('layout.master')

@section('title')
  <title>Laporan Absensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="box box-primary box-solid">
        <div class="box-header with-border">
          <div class="box-title">
            <p>Pilih Periode</p>
          </div>
        </div>

        <form action="{{ route('laporanAdministrator.store')}}" method="POST">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="row">
            <div class="col-xs-8">
            @if (isset($rekaptpp))
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                @if($key->id == $skpd_id)
                <option value="{{ $key->id }}" selected="">{{ $key->nama }}</option>
                @endif
                <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
            @else
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
            @endif
          </div>
            <div class="col-xs-4">
              <input type="text" class="form-control" name="pilih_bulan" id="pilih_bulan" @if (isset($rekaptpp)) value="{{$bulan}}" @endif placeholder="Klik disini." required="">
            </div>
          </div>
        </div>
        <div class="box-footer">
          <input type="submit" class="btn btn-block bg-purple" value="Pilih">
            @if (isset($rekaptpp))
              <a href="{{ route('laporan.cetakAdministrator', ['download'=>'pdf', 'skpd_id' => $skpd_id, 'pilih_bulan'=>$bulan]) }}" class="btn btn-block bg-green">Download PDF</a>
            @endif
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
                  <tr id="row{{$key['nip']}}"
                    @if ($key['status']==3)
                      style="background:#ffabc1;"
                    @endif
                  >
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

@endsection

@section('script')
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
$(".select2").select2();
$('#pilih_bulan').datepicker({
  autoclose: true,
  format: 'mm/yyyy',
  changeMonth: true,
  changeYear: true,
  showButtonPanel: true,
  viewMode: "months",
  minViewMode: "months"
});

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
