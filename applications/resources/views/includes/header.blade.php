        <a href="" class="logo">
          <span class="logo-mini" style="font-size:12px;"><b>Presensi</b></span>
          <span class="logo-lg" style="font-size:18px;">Presensi Online</span>
        </a>

        <nav class="navbar navbar-static-top" role="navigation">
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">

              @if (isset($getunreadintervensi))
                @if ($getunreadintervensi!=0 && Auth::user()->role_id!=3)
                  <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                      <i class="fa fa-bell-o"></i>
                      <span class="label label-warning">{{$getunreadintervensi}}</span>
                    </a>
                    <ul class="dropdown-menu" style="height:100px;">
                      <li class="header"><strong>Notifikasi Persetujuan Intervensi</strong></li>
                      <li>
                        <!-- inner menu: contains the actual data -->
                        <ul class="menu">
                          <li>
                            <a href="{{route('intervensi.kelola')}}">
                              <i class="fa fa-envelope text-aqua"></i> Anda memiliki {{$getunreadintervensi}} intervensi baru
                            </a>
                          </li>
                        </ul>
                      </li>
                      {{-- <li class="footer"><a href="#">View all</a></li> --}}
                    </ul>
                  </li>
                @endif
              @endif

              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="{{ asset('images/userdefault.png') }}" class="user-image" alt="User Image">
                  <span class="hidden-xs">
                    {{ Auth::user()->nama }}
                  </span>
                </a>
                <ul class="dropdown-menu">
                  <li class="user-header">
                    <img src="{{ asset('images/userdefault.png') }}" class="img-circle" alt="User Image">
                    <p>
                      {{ Auth::user()->nama }}
                      <small>{{ session('skpd') }}</small>
                      <small>Login as {{ Auth::user()->role->title }}</small>
                    </p>
                  </li>

                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="{{ route('profil.index')}}" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="{{ url('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
