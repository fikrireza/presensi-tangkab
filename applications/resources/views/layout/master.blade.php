<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    @yield('title')
    @include('includes.head')
    @yield('headscript')
  </head>
  <body class="layout-boxed sidebar-mini skin-purple-light">
    <div class="wrapper">
      <header class="main-header">
        @include('includes.header')
      </header>

      <aside class="main-sidebar">
        @include('includes.sidebar')
      </aside>

      <div class="content-wrapper">
        <section class="content-header">
        <h4><span>{{ session('skpd') }}</span></h4>
        @yield('breadcrumb')
        </section>

        <section class="content">
          @yield('content')
        </section>
        <button onclick="topFunc()" id="goTop" title="Go to top">Top</button>
      </div>

      <footer class="main-footer">
        @include('includes.footer')
      </footer>

    </div><!-- ./wrapper -->
    @include('includes.bottomscript')
    <script type="text/javascript">
      window.onscroll = function() {scrollFunction()};

      function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            document.getElementById("goTop").style.display = "block";
        } else {
            document.getElementById("goTop").style.display = "none";
        }
      }

      function topFunc() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      }
    </script>
    @yield('script')
  </body>
</html>
