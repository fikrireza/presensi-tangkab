<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Presensi Online | Kabupaten Tangerang</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('bootstrap/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('bootstrap/css/ionicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('dist/css/AdminLTE.css')}}">
    <link rel="stylesheet" href="{{asset('bootstrap/css/custom9tins.css')}}">
    <link rel="stylesheet" href="{{asset('bootstrap/css/9tins.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/iCheck/square/blue.css')}}">
</head>
<style type="text/css">
  .borderPurple {
   border-style: solid;
   border-color: #605CA8;
   border-width: 4px;
}
</style>
<body>

<div class="container">
  <script>
    window.setTimeout(function() {
      $(".alert-danger").fadeTo(1000, 0).slideUp(500, function(){
          $(this).remove();
      });
    }, 2000);
  </script>

  @if(Session::has('messageloginfailed'))
  <div class="row">
    <div class="col-md-12">
      <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-ban"></i> Login Gagal!</h4>
        <p>{{ Session::get('messageloginfailed') }}</p>
      </div>
    </div>
  </div>
  @endif

	<div class="left">
    <div class="login-box">
        <div class="login-logo">
          <img src="{{asset('images/logologinkabtangerang.png')}}" alt="Presensi Online" />
          &nbsp;&nbsp;<b>Presensi Online</b>
        </div>
      </div>
	</div>
	<div class="right">
    <div class="login-box-body borderPurple">
      <p class="login-box-msg">Silahkan Login</p>
      <form action="{{ url('login') }}" method="post">
        {{ csrf_field() }}
        <div class="form-group has-feedback {{ $errors->has('nip_sapk') ? 'has-error' : '' }}">
          <input name="nip_sapk" type="text" class="form-control" placeholder="NIP" value="{{ old('nip_sapk') }}">
          <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
          <input name="password" type="password" class="form-control" placeholder="Password" value="{{ old('password') }}">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <button type="submit" class="btn btn-primary btn-block btn-flat">Log In</button>
          </div>
        </div>
      </form>
    </div>
	</div>
	<div class="footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.0
    </div>
		<h4><strong>Copyright © 2017 <a href="">Presensi Online</a>.</strong> All rights reserved.</h4>
	</div>
</div>

</body>
</html>
