@extends('layout.master')

@section('title')
  <title>Tindak Intervensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Tindak Intervensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li><a href="{{ route('intervensi.kelola.post') }}">Kelola Intervensi</a></li>
    <li class="active">Tindak Intervensi</li>
  </ol>
@endsection

@section('content')

<div class="modal fade" id="myModalApprove" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Setujui Intervensi ?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk setujui intervensi ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setApprove">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="myModalDecline" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Tolak Intervensi ?</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk tolak intervensi ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setDecline">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <h3 class="box-title" style="line-height:30px;">Tindak Intervensi</h3>
        <a href="{{ route('intervensi.kelola') }}" class="btn bg-blue pull-right">Kembali</a>
      </div>
      <form class="form-horizontal" role="form" action="{{ route('pegawai.post') }}" method="post">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group">
            <label class="col-sm-3 control-label">Nama</label>
            <div class="col-sm-9">
              {{ $intervensi->nama_pegawai}}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Jenis Intervensi</label>
            <div class="col-sm-9">
              {{ $intervensi->jenis_intervensi}}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Tanggal Mulai</label>
            <div class="col-sm-9">
              {{ $intervensi->tanggal_mulai}}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Tanggal Akhir</label>
            <div class="col-sm-9">
              {{ $intervensi->tanggal_akhir}}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Jumlah Hari</label>
            <div class="col-sm-9">
              {{ $intervensi->jumlah_hari}}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Keterangan</label>
            <div class="col-sm-9">
              {{ $intervensi->deskripsi}}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Berkas</label>
            <div class="col-sm-9">
              @if ($intervensi->berkas == '-')
                Tidak ada berkas
              @else
                <a href="{{ url('documents', $intervensi->berkas)}}" download>Download</a>
              @endif
            </div>
          </div>
        </div>
        <div class="box-footer">
          <a href="" class="btn bg-red pull-right decline" data-toggle="modal" data-target="#myModalDecline" data-value="{{ $intervensi->id }}">Tolak</a>
          <a href="" class="btn bg-purple pull-right approve" data-toggle="modal" data-target="#myModalApprove" data-value="{{ $intervensi->id }}">Setujui</a>
          {{-- <button type="submit" class="btn bg-purple pull-right">Approve</button> --}}
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
$('a.approve').click(function(){
  var a = $(this).data('value');
  $('#setApprove').attr('href', "{{ url('/') }}/intervensi/kelola/approve/"+a);
});
$('a.decline').click(function(){
  var a = $(this).data('value');
  $('#setDecline').attr('href', "{{ url('/') }}/intervensi/kelola/decline/"+a);
});
</script>
@endsection
