@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
@endsection

@section('breadcrumb')
  <h1>Detail Absensi</h1>
  <ol class="breadcrumb">
    <li><a href=""><i class="fa fa-dashboard"></i>Dashboard</a></li>
    <li class="active">Detail Absensi</li>
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

<div class="modal fade" id="myModalReset" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Reset Password Akun Ini</h4>
      </div>
      <div class="modal-body">
        <p>Apakah anda yakin untuk reset password akun ini?</p>
      </div>
      <div class="modal-footer">
        <button type="reset" class="btn btn-default pull-left btn-flat" data-dismiss="modal">Tidak</button>
        <a class="btn btn-danger btn-flat" id="setreset">Ya, saya yakin</a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Detail Absensi {{$getskpd->nama}}</h3>
      </div>
      <div class="box-body">
        <table id="table_user" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              {{-- <th>Hari</th> --}}
              <th>Tanggal</th>
              <th>Jam Datang</th>
              <th>Jam Pulang</th>
            </tr>
          </thead>
          <tbody>
            @php
              $no=1;
            @endphp
            @foreach ($pegawai as $key)
              <tr>
                <td>{{$no}}</td>
                <td>{{ $key->nama }}</td>
                <td>
                  @php
                    echo date('d/m/Y');
                  @endphp
                </td>
                <td>
                  @php
                    $flagmasuk=0;
                  @endphp
                  @foreach ($absensi as $keys)
                    @if ($keys->fid == $key->fid)
                      @php
                        $jammasuk_upper = 100000;
                        $jammasuk_lower = 70000;
                        $jamlog = (int) str_replace(':','',$keys->jam_log);
                      @endphp
                      @if ($jamlog<$jammasuk_upper && $jamlog>$jammasuk_lower)
                        @php
                          $flagmasuk=1;
                        @endphp
                        {{$keys->jam_log}}
                        @php
                          break;
                        @endphp
                      @endif
                    @endif
                  @endforeach
                  @if ($flagmasuk==0)
                    x
                  @endif
                </td>
                <td>
                  @php
                    $flagpulang=0;
                  @endphp
                  @foreach ($absensi as $keys)
                    @if ($keys->fid == $key->fid)
                      @php
                        $jampulang_upper = 140000;
                        $jamlog = (int)str_replace(':','',$keys->jam_log);
                      @endphp
                      @if ($jamlog>$jampulang_upper)
                        @php
                          $flagpulang=1;
                        @endphp
                        {{$keys->jam_log}}
                        @php
                          break;
                        @endphp
                      @endif
                    @endif
                  @endforeach
                  @if ($flagpulang==0)
                    x
                  @endif
                </td>
              </tr>
              @php
                $no++;
              @endphp
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script>
  $(function () {
    $("#table_user").DataTable();
  });
</script>
@endsection
