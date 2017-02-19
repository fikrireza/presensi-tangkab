@extends('layout.master')

@section('title')
  <title>History Mutasi</title>
@endsection

@section('breadcrumb')
  <h1>History Mutasi</h1>
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

<div class="row">
  <div class="col-md-12">
    <!-- Box Comment -->
    @if($empty == "Tidak Kosong")
    <div class="box box-widget">
      <div class="box-header with-border">
        <div class="user-block">
          <img class="img-circle" src="{{ asset('images/userdefault.png') }}" alt="User Image">
          <span class="username"><a href="#">{{$getmutasi[0]->pegawai->nama}}</a></span>
          <span class="description"> {{ \Carbon\Carbon::parse($getmutasi[0]->pegawai->created_at)->format('d-M-y')}}</span>
        </div>
      </div>
      <!-- /.box-header -->
      @foreach($getmutasi as $key)
        <div class="box-body">
          <!-- post text -->
          <p><b>SKPD Lama</b> : {{$key->skpd_old->nama}}</p>
          <p><b>SKPD Baru</b> : {{$key->skpd_new->nama}}</p>
          <p><b>Tanggal Mutasi</b> : {{ \Carbon\Carbon::parse($key->tanggal_mutasi)->format('d-M-y')}}</p>
          <p><b>TPP Yang Dibayarkan</b> : Rp. {{ number_format($key->tpp_dibayarkan,0,',','.') }},-</p>
          <p><b>Nomor SK</b> : {{$key->nomor_sk}}</p>
          <p><b>Tanggal SK</b> : {{ \Carbon\Carbon::parse($key->tanggal_sk)->format('d-M-y')}}</p>
          <!-- Attachment -->
          <a target="_blank" href="{{ asset('\..\documents').'/'.$key->upload_sk}}" download="{{$key->upload_sk}}" class="link-black text-sm">
              @if (strpos($key->upload_sk, '.pdf'))
                <img width="5%" src="{{ asset('dist\img\pdf.png') }}" alt="..." class="margin">
              @elseif(strpos($key->upload_sk, '.png'))
                <img width="5%" src="{{ asset('dist\img\png.png') }}" alt="..." class="margin">
              @elseif(strpos($key->upload_sk, '.jpg'))
                <img width="5%" src="{{ asset('dist\img\jpg.png') }}" alt="..." class="margin">
              @elseif(strpos($key->upload_sk, '.docx'))
                <img width="5%" src="{{ asset('dist\img\doc.png') }}" alt="..." class="margin">
              @elseif(strpos($key->upload_sk, '.xlsx'))
                <img width="5%" src="{{ asset('dist\img\doc.png') }}" alt="..." class="margin">
              @endif
            </a>
          <div class="attachment-block" style="border:1px solid #00a65a;margin-top:5px;">
             
            <h4 class="attachment-heading"><b>Keterangan</b></h4>
              <div class="attachment-text">
                {{$key->keterangan}}
              </div>
              <!-- /.attachment-text -->
            <!-- /.attachment-pushed -->
          </div>
          <!-- /.attachment-block -->
        </div>
        <hr/>
      <!-- /.box-body -->
      @endforeach
    </div>
    <div class="pull-justify">
      {{ $getmutasi->links() }}
    </div>
    @else
      <div class="callout callout-info">
      <h4>Pemberitahuan!</h4>

      <p>Anda belum pernah dimutasikan ke SKPD lain.</p>
    </div>
    @endif
    <!-- /.box -->
  </div>
</div>

@endsection


