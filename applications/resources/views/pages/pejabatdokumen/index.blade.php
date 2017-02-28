@extends('layout.master')

@section('title')
  <title>Pejabat Dokumen</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('breadcrumb')
  <h1>Pejabat Dokumen</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Pejabat Dokumen</li>
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

<div class="modal fade" id="modalflagedit" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Ubah Status Pejabat</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk mengubah status pejabat ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger  btn-flat" id="setflagedit">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

{{-- Modal Tambah Pejabat Dokumen--}}
<div class="modal modal-default fade" id="modaltambahpejabat" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('pejabatdokumen.post') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Pejabat Dokumen</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('pegawai_id') ? 'has-error' : '' }}">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <select name="pegawai_id" class="form-control select2" required="" style="width:100%;">
                <option value="">--Pilih--</option>
                @foreach ($pegawai as $key)
                  <option value="{{$key->id}}" {{ old($key->id) == $key->id ? 'selected' : ''}}>{{$key->nip_sapk}} - {{$key->nama}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group {{ $errors->has('pangkat') ? 'has-error' : '' }}">
            <label class="col-sm-3">Pangkat</label>
            <div class="col-sm-9">
              <input type="text" name="pangkat" class="form-control" value="{{ old('pangkat')}}" placeholder="@if($errors->has('pangkat')){{ $errors->first('pangkat')}} @endif Pangkat" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('jabatan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Jabatan</label>
            <div class="col-sm-9">
              <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan')}}" placeholder="@if($errors->has('jabatan')){{ $errors->first('jabatan')}} @endif Jabatan" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('posisi_ttd') ? 'has-error' : '' }}">
            <label class="col-sm-3">Posisi TTD</label>
            <div class="col-sm-9">
              <select name="posisi_ttd" class="form-control" required="">
                <option value="">--Pilih--</option>
                <option value="1" {{ old('posisi_ttd') == '1' ? 'selected' : ''}}>Kanan</option>
                <option value="2"  {{ old('posisi_ttd') == '2' ? 'selected' : ''}}>Kiri</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Tidak</button>
          <button type="submit" class="btn bg-purple">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit Pejabat --}}
<div class="modal modal-default fade" id="modaleditPejabat" role="dialog">
  <div class="modal-dialog">
    <form class="form-horizontal" action="{{ route('pejabatdokumen.edit') }}" method="post">
      {{ csrf_field() }}
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Data Pejabat Dokumen</h4>
        </div>
        <div class="modal-body">
          <div class="form-group {{ $errors->has('pegawai_id') ? 'has-error' : '' }}">
            <label class="col-sm-3">Nama</label>
            <div class="col-sm-9">
              <select name="pegawai_id" class="form-control select2" required="" style="width:100%;">
                @foreach ($pegawai as $key)
                  <option value="{{$key->id}}" id="pegawai_id{{$key->id}}" {{ old($key->id) == $key->id ? 'selected' : ''}}>{{$key->nip_sapk}} - {{$key->nama}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group  {{ $errors->has('jabatan') ? 'has-error' : '' }}">
            <label class="col-sm-3">Jabatan</label>
            <div class="col-sm-9">
              <input type="text" name="jabatan" class="form-control" id="jabatan" value="{{ old('jabatan') }}" placeholder="@if($errors->has('jabatan')){{ $errors->first('jabatan')}} @endif Singkatan" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('pangkat') ? 'has-error' : '' }}">
            <label class="col-sm-3">Pangkat</label>
            <div class="col-sm-9">
              <input type="text" name="pangkat" class="form-control" id="pangkat" value="{{ old('pangkat') }}" placeholder="@if($errors->has('pangkat')){{ $errors->first('pangkat')}} @endif Pangkat" required="">
            </div>
          </div>
          <div class="form-group {{ $errors->has('posisi_ttd') ? 'has-error' : '' }}">
            <label class="col-sm-3">Posisi TTD</label>
            <div class="col-sm-9">
              <select class="form-control" name="posisi_ttd">
                <option value="1" id="posisi_ttd1">Kanan</option>
                <option value="2" id="posisi_ttd2">Kiri</option>
              </select>
              <input type="hidden" name="pejabatdokumen_id" id="pejabatdokumen_id" />
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
        <h3 class="box-title">Pejabat Dokumen</h3>
        @if ($limit < 2)
          <a href="#" class="btn bg-blue pull-right" data-toggle="modal" data-target="#modaltambahpejabat">Tambah Pejabat Dokumen</a>
        @endif
      </div>
      <div class="box-body table-responsive">
        <table id="table_pejabat" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>NIP</th>
              <th>Nama</th>
              <th>Jabatan</th>
              <th>Pangkat</th>
              <th>Posisi</th>
              <th>Status</th>
              <th>Action</th>
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
              <td></td>
            </tr>
          </tfoot>
          <tbody>
            @if($pejabat->isEmpty())
            <tr>
              <td colspan="7" align="center">SKPD Anda Belum Mempunyai Pejabat Dokumen</td>
            </tr>
            @else
            @php
              $no = 1;
            @endphp
            @foreach ($pejabat as $key)
            <tr>
              <td>{{ $no }}</td>
              <td>{{ $key->nip_sapk }}</td>
              <td>{{ strtoupper($key->nama) }}</td>
              <td>{{ strtoupper($key->jabatan) }}</td>
              <td>{{ strtoupper($key->pangkat) }}</td>
              <td>@if($key->posisi_ttd == 1) Kanan @else Kiri @endif</td>
              <td>@if ($key->flag_status == 1)
                <span data-toggle="tooltip" title="Non Aktifkan">
                  <a href="#" class="btn btn-xs btn-danger btn-flat flagedit" data-toggle="modal" data-target="#modalflagedit" data-value="{{$key->id}}"><i class="fa fa-heartbeat"></i></a>
                </span>
              @else
                <span data-toggle="tooltip" title="Aktifkan">
                  <a href="#" class="btn btn-xs btn-success btn-flat flagedit" data-toggle="modal" data-target="#modalflagedit" data-value="{{$key->id}}"><i class="fa fa-heart"></i></a>
                </span>
              @endif</td>
              <td><a href="" data-value="{{ $key->id }}" class="editPejabat" data-toggle="modal" data-target="#modaleditPejabat"><i class="fa fa-edit"></i> Ubah</td>
            </tr>
            @php
              $no++;
            @endphp
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
<script type="text/javascript">
 $(".select2").select2();
@if (count($errors) > 0)
  $('#modaltambahpejabat').modal('show');
@endif
</script>
<script type="text/javascript">
  $(document).ready(function() {
      // Setup - add a text input to each footer cell
      $('#table_pejabat tfoot th').each( function () {
          var title = $(this).text();
          $(this).html( '<input type="text" class="form-control" style="border:1px solid #3598DC; width:100%" />' );
      } );

      // DataTable
      var table = $('#table_pejabat').DataTable();

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

<script type="text/javascript">
  $(function(){
    $('a.flagedit').click(function(){
        var a = $(this).data('value');
        $('#setflagedit').attr('href', '{{url('pejabat/flagstatus/')}}/'+a);
      });

    $('.editPejabat').click(function(){
      var a = $(this).data('value');
      $.ajax({
        url: "{{ url('/') }}/pejabat-dokumen/"+a,
        dataType: 'json',
        success: function(data){
          var pejabatdokumen_id = data.id;
          var pegawai_id = data.pegawai_id;
          var jabatan = data.jabatan;
          var pangkat = data.pangkat;
          var posisi_ttd = data.posisi_ttd;

          // set
          $('#pejabatdokumen_id').attr('value', pejabatdokumen_id);
          $('option#pegawai_id'+pegawai_id).attr('selected', true);
          $('#jabatan').attr('value', jabatan);
          $('#pangkat').attr('value', pangkat);
          $('option#posisi_ttd'+posisi_ttd).attr('selected', true);
        }
      });
    });
  });
</script>

@endsection
