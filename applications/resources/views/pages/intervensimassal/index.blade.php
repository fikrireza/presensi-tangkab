@extends('layout.master')

@section('title')
  <title>Intervensi Massal</title>
@endsection

@section('breadcrumb')
  <h1>Intervensi Massal</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Intervensi Massal</li>
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

<div class="modal modal-default fade" id="modaledit" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('intervensimassal.edit') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data Intervensi Massal</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-md-2 control-label">Jenis Intervensi</label>
            <div class="col-md-10">
              <input name="jenis_intervensi_edit" id="jenis_intervensi_edit" class="form-control" readonly="true" value="{{ old('jenis_intervensi_edit') }}">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label">NIP</label>
            <div class="col-md-10">
              <input name="nip_sapk_edit" id="nip_sapk_edit" class="form-control" readonly="true" value="{{ old('nip_sapk_edit') }}" >
              <input type="hidden" name="id" id="id" value="{{ old('id') }}">
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-2 control-label">Nama</label>
            <div class="col-md-10">
              <input name="nama_edit" id="nama_edit" class="form-control" readonly="true" value="{{ old('nama_edit') }}">
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_mulai_edit') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tanggal Mulai</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_mulai_edit" type="text" name="tanggal_mulai_edit" 
                 value="{{ old('tanggal_mulai_edit') }}" placeholder="@if($errors->has('tanggal_mulai_edit'))
                  {{ $errors->first('tanggal_mulai_edit')}}@endif Tanggal Awal" readonly="true">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('tanggal_akhir_edit') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Tanggal Akhir</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-calendar"></i>
                </div>
                <input class="form-control pull-right" id="tanggal_akhir_edit" type="text" name="tanggal_akhir_edit" 
                 value="{{ old('tanggal_akhir_edit') }}" placeholder="@if($errors->has('tanggal_akhir_edit'))
                  {{ $errors->first('tanggal_akhir_edit')}}@endif Tanggal Akhir" onchange="durationDay()" readonly="true">
              </div>
            </div>
          </div>
          <div class="form-group {{ $errors->has('jumlah_hari_edit') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Jumlah Hari</label>
            <div class="col-sm-10">
              <input type="text" name="jumlah_hari_edit" id="jumlah_hari_edit" class="form-control" value="{{ old('jumlah_hari_edit') }}" placeholder="@if($errors->has('jumlah_hari_edit'))
                {{ $errors->first('jumlah_hari_edit')}} @endif Jumlah Hari" required="" readonly="true">
            </div>
          </div>
          <div class="form-group {{ $errors->has('keterangan_edit') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Keterangan</label>
            <div class="col-sm-10">
              <textarea name="keterangan_edit" class="form-control" id="keterangan_edit" rows="5" cols="40" placeholder="@if($errors->has('keterangan_edit'))
                {{ $errors->first('keterangan_edit')}}@endif Keterangan ">{{ old('keterangan_edit') }}</textarea>
            </div>
          </div>
          <div class="form-group {{ $errors->has('upload_massal') ? 'has-error' : '' }}">
            <label class="col-sm-2 control-label">Upload Document</label>
            <div class="col-sm-10">
              <input type="file" name="upload_massal" class="form-control {{ $errors->has('upload_massal') ? 'has-error' : '' }}" accept=".png, .jpg, .pdf">
              <span style="color:red;">* Biarkan kosong jika tidak ingin diganti.</span>
               @if($errors->has('upload_massal'))
              <span class="help-block">
                <strong>{{ $errors->first('upload_massal')}}
                </strong>
              </span>
              @endif
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Intervensi Massal</h3>
        <a href="{{ route('intervensimassal.create') }}" class="btn bg-blue pull-right">Tambah Massal</a>
      </div>
      <div class="box-body table-responsive">
        <table id="table_mutasi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Jenis Intervensi</th>
              <th>NIP</th>
              <th>Nama Pegawai</th>
              <th>Tanggal Mulai</th>
              <th>Tanggal Akhir</th>
              <th>Keterangan</th>
              <th>Status Intervensi</th>
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
              <th></th>
              <th></th>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($getintervensimassal->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
              @foreach ($getintervensimassal as $key)
              <tr>
                <td>{{ $no }}</td>
                <td>{{ $key->jenis_intervensi }}</td>
                <td>{{ $key->nip_sapk_pegawai }}</td>
                <td>{{ $key->nama }}</td>
                <td>{{ $key->tanggal_mulai }}</td>
                <td>{{ $key->tanggal_akhir }}</td>
                <td>{{ $key->deskripsi }}</td>
                <td>
                  @if($key->flag_status == 0)
                    <small class="label label-info">Belum Disetujui</small>
                  @elseif($key->flag_status == 1)
                    <small class="label label-success">Sudah Disetujui</small>
                  @elseif($key->flag_status == 3)
                    <small class="label label-warning">Dibatalkan</small>
                  @else
                    <small class="label label-danger">Tidak Disetujui</small>
                  @endif
                </td>
                <td><a href="" data-value="{{ $key->id }}" class="btn btn-warning btn-xs edit" data-toggle="modal" data-target="#modaledit"><i class="fa fa-edit"></i> Ubah</a></td>
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
@endsection

@section('script')
<script type="text/javascript">
  $("#table_mutasi").DataTable();
</script>

<script type="text/javascript">
@if ($errors->has('tanggal_mulai_edit') || $errors->has('tanggal_akhir_edit') || $errors->has('keterangan_edit'))
  $('#modaledit').modal('show');
@endif
</script>

<script type="text/javascript">
  $(function(){
    $("#table_mutasi").on("click", "a.edit", function(){
      var a = $(this).data('value');

      $.ajax({
        url: "{{ url('/') }}/intervensi-massal/"+a,
        dataType: 'json',
        success: function(data){
          var id = data.id;

          var jenis_intervensi_edit = data.jenis_intervensi;
          var nip_sapk_edit = data.nip_sapk_pegawai;
          var nama_edit = data.nama;
          var tanggal_mulai_edit = data.tanggal_mulai;
          var tanggal_akhir_edit = data.tanggal_akhir;
          var jumlah_hari_edit = data.jumlah_hari;
          var keterangan_edit = data.deskripsi;


          $('#id').attr('value', id);
          $('#jenis_intervensi_edit').attr('value', jenis_intervensi_edit);
          $('#nip_sapk_edit').attr('value', nip_sapk_edit);
          $('#nama_edit').attr('value', nama_edit);
          $('#tanggal_akhir_edit').attr('value', tanggal_akhir_edit);
          $('#tanggal_mulai_edit').attr('value', tanggal_mulai_edit);
          $('#jumlah_hari_edit').attr('value', jumlah_hari_edit);
          $('#keterangan_edit').val(keterangan_edit);
        }
      });
    });
  });
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
