@extends('layout.master')

@section('title')
  <title>Detail Laporan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@endsection

@section('content')
{{-- <div class="row">
  <div class="col-md-6 col-md-offset-3">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih Periode</p>
        </div>
      </div>

      <form action="{{ route('jurnal.getJurnal')}}" method="POST">
      {{ csrf_field() }}
      <div class="box-body">
        <div class="row">
          <div class="col-xs-6">
            <input type="text" class="form-control" name="start_bulan" id="start_bulan" placeholder="mm/yyyy" required=""
              @if (isset($getStartBulan))
                value="{{$getStartBulan}}"
              @endif
            >
          </div>
          <div class="col-xs-6">
            <input type="text" class="form-control" name="end_bulan" id="end_bulan" placeholder="mm/yyyy" required=""
              @if (isset($getEndBulan))
                value="{{$getEndBulan}}"
              @endif
            >
          </div>
        </div>

      </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-block bg-purple" value="Pilih">
      </div>
      </form>
    </div>
  </div>
</div> --}}


{{-- @if (isset($getStartBulan)) --}}
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Jurnal TPP 2017</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th class="text-center">SKPD</th>
              <th class="text-center">Januari</th>
              <th class="text-center">Februari</th>
              <th class="text-center">Maret</th>
              <th class="text-center">April</th>
              <th class="text-center">Mei</th>
              <th class="text-center">Juni</th>
              <th class="text-center">Juli</th>
              <th class="text-center">Agustus</th>
              <th class="text-center">September</th>
              <th class="text-center">Oktober</th>
              <th class="text-center">November</th>
              <th class="text-center">Desember</th>
              <th class="text-center">Total</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no = 1;
            @endphp
            <tr>
              <td colspan="2" class="text-center"><b>Total Dibayarkan</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($januari), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($februari), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($maret), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($april), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($mei), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($juni), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($juli), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($agustus), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($september), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($oktober), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($november), 0, ',', '.') }}</b></td>
              <td bgcolor="yellow"><b>{{ number_format(round($desember), 0, ',', '.') }}</b></td>
              <td bgcolor="pink"><b><u>{{ number_format(round($grandTotal), 0, ',', '.') }}</u></b></td>
            </tr>
            @foreach ($getJurnal as $jurnal)
              <tr>
                <td>{{ $no }}</td>
                <td><b>{{ $jurnal->nama }}</b></td>
                <td>{{ number_format(round($jurnal->tpp_januari), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_februari), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_maret), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_april), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_mei), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_juni), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_juli), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_agustus), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_september), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_oktober), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_november), 0, ',', '.') }}</td>
                <td>{{ number_format(round($jurnal->tpp_desember), 0, ',', '.') }}</td>
                @php
                  $grand = $jurnal->tpp_januari+$jurnal->tpp_februari+$jurnal->tpp_maret+$jurnal->tpp_april+$jurnal->tpp_mei+$jurnal->tpp_juni+$jurnal->tpp_juli+$jurnal->tpp_agustus+$jurnal->tpp_september+$jurnal->tpp_oktober+$jurnal->tpp_november+$jurnal->tpp_desember;
                @endphp
                <td bgcolor="yellow">{{ number_format(round($grand), 0, ',', '.') }}</td>
              </tr>
              @php
              $no++
              @endphp
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
{{-- @endif --}}


@endsection

@section('script')

@endsection
