
<link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('bootstrap/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('bootstrap/css/ionicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/iCheck/all.css')}}">
<link rel="stylesheet" href="{{ asset('plugins/morris/morris.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/jvectormap/jquery-jvectormap-1.2.2.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datepicker/datepicker3.css') }}">

<link rel="stylesheet" href="{{ asset('plugins/datatables/dataTables.bootstrap.min.css') }}">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<meta name="csrf-token" content="{{ csrf_token() }}" />

<style media="screen">
#goTop {
  display: none;
  position: fixed;
  bottom: 5px;
  right: 5px;
  z-index: 99;
  border: none;
  outline: none;
  background-color: purple;
  color: white;
  cursor: pointer;
  padding: 7px;
  border-radius: 10px;
}

#goTop:hover {
  background-color: #000;
}
</style>
