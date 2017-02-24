@extends('layout.master')

@section('title')
  <title>Master Pengecualian</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Master Pengecualian</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Pengecualian</li>
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
<script>
  window.setTimeout(function() {
    $(".alert-warning").fadeTo(500, 0).slideUp(500, function(){
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

@if(Session::has('gagal'))
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-warning">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Perhatian!</h4>
      <p>{{ Session::get('gagal') }}</p>
    </div>
  </div>
</div>
@endif

<div class="modal modal-default fade" id="modaltambah" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('pengecualian.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tambah Pengecualian</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('nip_sapk') ? 'has-error' : '' }}">
            <label class="col-md-3">Pegawai</label>
            <div class="col-md-9">
              <select class="form-control select2" name="nip_sapk" style="width:100%;">
                <option value="">-- PILIH --</option>
                @foreach ($pegawai as $key)
                <option value="{{ $key->nip_sapk }}" {{ old('nip_sapk') == $key->nip_sapk ? 'selected' : ''}}>{{ $key->nama }}</option>
                @endforeach
              </select>
               @if($errors->has('nip_sapk'))
                  <span class="help-block">
                    <strong>{{ $errors->first('nip_sapk')}}
                    </strong>
                  </span>
                @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('catatan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Catatan</label>
            <div class="col-sm-9">
              <textarea name="catatan" class="form-control" rows="5" cols="40" placeholder="@if($errors->has('catatan'))
                {{ $errors->first('catatan')}}@endif Catatan ">{{ old('catatan') }}</textarea>
               @if($errors->has('catatan'))
                  <span class="help-block">
                    <strong>{{ $errors->first('catatan')}}
                    </strong>
                  </span>
                @endif
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <button type="submit" class="btn btn-danger">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal modal-default fade" id="modaledit" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('pengecualian.edit') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data Pengecualian</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="col-md-3">Pegawai</label>
             
            <div class="col-md-9">
              
              <select class="form-control select2" name="nip_sapk_edit" id="nip_sapk_edit" style="width:100%;">
                <option value="">-- PILIH --</option>
                <?php
                  foreach ($pegawai as $key){
                    echo "<option value=$key->nip_sapk>$key->nama</option>";
                  }
                ?>
              </select>
              <input type="hidden" name="id" id="id">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3">Catatan</label>
            <div class="col-sm-9">
              <textarea name="catatan_edit" id="catatan_edit" class="form-control" rows="5" cols="40" placeholder="Catatan "></textarea>
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
        <h3 class="box-title">Pengecualian</h3>
        <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambah">Tambah Pengecualian</a>
      </div>
      <div class="box-body">
        <table id="table_penegcualian" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Catatan</th>
              <th>Action</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td></td>
              <th></th>
              <th></th>
              <th></th>
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            <?php $no = 1; ?>
            @if ($pengecualian->isEmpty())
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
            </tr>
            @else
              @foreach ($pengecualian as $key)
              <tr>
                <td>{{ $no }}</td>
                <td>{{ $key->nip_sapk }}</td>
                <td>{{ $key->nama }}</td>
                <td>{{ $key->catatan }}</td>
                <td><a href="" data-value="{{ $key->id }}" class="edit" data-toggle="modal" data-target="#modaledit"><i class="fa fa-edit"></i> Ubah</a></td>
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
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
  $(".select2").select2();
  $(function () {
    $("#table_jabatan").DataTable();
  });
</script>

<script type="text/javascript">
@if (count($errors) > 0)
  $('#modaltambah').modal('show');
@endif
</script>

<script type="text/javascript">
  $(function(){
    $("#table_penegcualian").on("click", "a.edit", function(){
      var a = $(this).data('value');
      $.ajax({
        url: "{{ url('/') }}/pengecualian/"+a,
        dataType: 'json',
        success: function(data){
          var id = data.id;
          
          var nip_sapk_edit = data.nip_sapk;
          var catatan_edit = data.catatan;
          alert(nip_sapk_edit);
          // set
          $('#nip_sapk_edit').attr('value', nip_sapk_edit);
          $('#catatan_edit').val(catatan_edit);
        }
      });
    });
  });
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_penegcualian tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );
   
      // DataTable
      var table = $('#table_penegcualian').DataTable();
   
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
