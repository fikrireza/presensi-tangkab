<!DOCTYPE html>
<html>
  <head>
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
        @yield('breadcrumb')
        </section>

        <section class="content">
          @yield('content')
        </section>
      </div>

      <footer class="main-footer">
        @include('includes.footer')
      </footer>

    </div><!-- ./wrapper -->
    @include('includes.bottomscript')
    @yield('script')
  </body>
</html>
