@extends('layout.master')

@section('title')
  <title>Profil</title>
@stop

@section('content')

<div class="col-md-12">
  @if(Session::has('firsttimelogin'))
    <div class="alert alert-success panjang">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Selamat Datang!</h4>
      <p>{{ Session::get('firsttimelogin') }}</p>
    </div>
  @endif
</div>

<div class="row">
  @if(Session::has('messagefilled'))
  <div class="alert alert-info alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-info"></i> Informasi</h4>
    {{ Session::get('messagefilled') }}
  </div>
  @endif
  <div class="col-md-9">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title">Ubah Password</h3>
      </div>

      <form class="form-horizontal" action="" method="post">
        {{ csrf_field() }}
        <div class="box-body">
          <div class="form-group {{ $errors->has('oldpass') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Password Lama</label>
            <div class="col-sm-9">
              <input name="oldpass" type="password" class="form-control" placeholder="Password Lama" @if(!$errors->has('oldpass'))
                value="{{ old('oldpass') }}"@endif>
              <input name="id" type="hidden" class="form-control" value="{{ $profiles->id }}">
              @if($errors->has('oldpass'))
                <span class="help-block">
                  <strong>{{ $errors->first('oldpass') }}
                  </strong>
                </span>
              @endif
              @if(Session::has('erroroldpass'))
                <span class="help-block">
                  <strong>{{ Session::get('erroroldpass') }}
                  </strong>
                </span>
              @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('newpass') ? 'has-error' : '' }} ">
            <label class="col-sm-3 control-label">Password Baru</label>
            <div class="col-sm-9">
              <input name="newpass" type="password" class="form-control" placeholder="Password Baru Minimal 8 Karakter" @if(!$errors->has('newpass'))
                value="{{ old('newpass') }}"@endif>
              @if($errors->has('newpass'))
                <span class="help-block">
                  <strong>{{ $errors->first('newpass') }}
                  </strong>
                </span>
              @endif
            </div>
          </div>
          <div class="form-group {{ $errors->has('newpass_confirmation') ? 'has-error' : '' }}">
            <label class="col-sm-3 control-label">Konfirmasi Password Baru</label>
            <div class="col-sm-9">
              <input name="newpass_confirmation" type="password" class="form-control" placeholder="Konfirmasi Password Baru"
              @if(!$errors->has('newpass_confirmation'))
                value="{{ old('newpass_confirmation') }}"@endif>
              @if($errors->has('newpass_confirmation'))
                <span class="help-block">
                  <strong>{{ $errors->first('newpass_confirmation') }}
                  </strong>
                </span>
              @endif
            </div>
          </div>
          <div class="box-footer">
            <button type="submit" class="btn btn-info pull-right">Ubah Password</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('script')
  <script src="{{ asset('/plugins/iCheck/icheck.min.js') }}"></script>
  <script>
  $('input[type="radio"].minimal').iCheck({
          radioClass: 'iradio_minimal-blue'
  });
  </script>
  </script>
@stop
