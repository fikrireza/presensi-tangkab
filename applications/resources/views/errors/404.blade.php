@extends('layout.master')

@section('title')
  <title>Halaman Tidak Ada</title>
@endsection


@section('content')
  <div class="error-page">
    <h2 class="headline text-yellow"> 404</h2>
    <div class="error-content">
      <h3><i class="fa fa-warning text-yellow"></i> Oops! Halaman Yang Anda Cari Tidak Ada.</h3>
      <p>
        Halaman yang anda cari tidak tersedia di situs ini.
        <a href="{{ URL::previous() }}">Kembali Ke Halaman Sebelumnya</a>.
      </p>
    </div>
  </div>

@endsection

@section('script')


@endsection
