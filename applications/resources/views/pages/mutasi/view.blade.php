@extends('layout.master')

@section('title')
  <title>History Mutasi</title>
@endsection

@section('breadcrumb')
  <h1>History Mutasi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">History Mutasi</li>
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
          <span class="username">{{$getmutasi[0]->pegawai->nip_sapk}} - {{$getmutasi[0]->pegawai->nama}}</span>
          <span class="description"> {{ \Carbon\Carbon::parse($getmutasi[0]->pegawai->created_at)->format('d-M-y')}}</span>
        </div>
      </div>
      <!-- /.box-header -->
      @foreach($getmutasi as $key)
        <div class="box-body">
          <!-- post text -->
          <table class="table">
            <tr>
              <td><b>SKPD Lama</b></td>
              <td>:</td>
              <td>{{$key->skpd_old->nama}}</td>
            </tr>
            <tr>
              <td><b>SKPD Baru</b></td>
              <td>:</td>
              <td>{{$key->skpd_new->nama}}</td>
            </tr>
            <tr>
              <td><b>Tanggal Mutasi</b></td>
              <td>:</td>
              <td>{{ \Carbon\Carbon::parse($key->tanggal_mutasi)->format('d-M-Y')}}</td>
            </tr>
            <tr>
              <td><b>TPP Yang Dibayarkan</b></td>
              <td>:</td>
              <td>Rp. {{ number_format($key->tpp_dibayarkan,0,',','.') }},-</td>
            </tr>
            <tr>
              <td><b>Nomor SK</b></td>
              <td>:</td>
              <td>{{$key->nomor_sk}}</td>
            </tr>
            <tr>
              <td><b>Tanggal SK</b></td>
              <td>:</td>
              <td>{{ \Carbon\Carbon::parse($key->tanggal_sk)->format('d-M-Y')}}</td>
            </tr>
          </table>
          <div class="attachment-block" style="border:1px solid #00a65a;margin-top:5px;">
            <h4 class="attachment-heading"><b>Keterangan</b></h4>
              <div class="attachment-text">
                {{$key->keterangan}}
              </div>
          </div>
          <!-- Attachment -->
          @if ($key->upload_sk != "")
            @foreach(explode('//', $key->upload_sk) as $info) 
              @if($info != null)
                 <a target="_blank" href="{{ asset('\..\documents').'/'.$info}}" download="{{$info}}" class="link-black text-sm">
                  @if(strpos($info, '.png'))
                    <img width="5%" src="{{ asset('dist\img\png.png') }}" alt="..." class="margin">
                  @elseif(strpos($info, '.jpg'))
                    <img width="5%" src="{{ asset('dist\img\jpg.png') }}" alt="..." class="margin">
                  @elseif(strpos($info, '.docx'))
                    <img width="5%" src="{{ asset('dist\img\doc.png') }}" alt="..." class="margin">
                  @elseif(strpos($info, '.xlsx'))
                    <img width="5%" src="{{ asset('dist\img\doc.png') }}" alt="..." class="margin">
                  @endif
                </a>
              @endif
            @endforeach
          @endif
          @if ($key->upload_sk != "")
            @foreach(explode('//', $key->upload_sk) as $info) 
              @if($info != null)
                 <a target="_blank" class="link-black text-sm">
                  @if (strpos($info, '.pdf'))
                    <div class="row">
                      <div class="col-md-12">
                          <div class="panel panel-primary">
                              <div class="panel-heading">
                                <div class="pull-left">{{$info}}</div>
                                <br>
                                </div>
                                <div class="panel-body">
                                    <div>
                                        <embed src="{{ asset('\..\documents').'/'.$info}}" type="application/pdf" width="100%" height="500px"/>
                                    </div>
                                </div>
                          </div>
                      </div>
                  </div>
                  @endif
                </a>
              @endif
            @endforeach
          @endif
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
      <div class="callout callout-success">
      <h4>Pemberitahuan!</h4>

      <p>Anda belum pernah dimutasikan ke SKPD lain.</p>
    </div>
    @endif
    <!-- /.box -->
  </div>
</div>

@endsection
