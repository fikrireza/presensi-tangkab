@extends('layout.master')

@section('title')
  <title>Profil</title>
@stop

@section('content')

@if(Session::has('firsttimelogin'))
<div class="col-md-12">
  <div class="alert alert-success panjang">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-check"></i> Selamat Datang!</h4>
    <p>{{ Session::get('firsttimelogin') }}</p>
  </div>
</div>
@endif

@if(Session::has('erroroldpass'))
<div class="col-md-12">
  <div class="alert alert-warning alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h4><i class="icon fa fa-info"></i> Informasi</h4>
    {{ Session::get('erroroldpass') }}
  </div>
</div>
@endif

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
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
              <input name="pegawai_id" type="hidden" class="form-control" value="{{ Auth::user()->pegawai_id }}">
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
            <button type="submit" class="btn bg-purple pull-right">Ubah Password</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@stop
