          <section class="sidebar">
          <div class="user-panel">
            <div class="pull-left image">
              <img src="{{ asset('images/userdefault.png') }}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p>{{ Auth::user()->nip_sapk }}</p>
              <a href="#"><i class="fa fa-circle text-success"></i> {{ Auth::user()->nama }}</a>
              <small></small>
            </div>
          </div>
          <ul class="sidebar-menu">
            <li class="header">MENU UTAMA</li>
            <li class="{{ Route::currentRouteNamed('home') ? 'active' : '' }}">
              <a href="{{ Route('home') }}">
                <i class="fa fa-home"></i> <span>Home</span>
              </a>
            </li>
            @if(session('status') == 'administrator' || session('status') == 'superuser')
            <li class="treeview {{ Route::currentRouteNamed('skpd.index') ? 'active' : '' }}{{ Route::currentRouteNamed('golongan.index') ? 'active' : ''}}{{ Route::currentRouteNamed('jabatan.index') ? 'active' : ''}}{{ Route::currentRouteNamed('struktural.index') ? 'active' : ''}}{{ Route::currentRouteNamed('pegawai.index') ? 'active' : ''}}{{ Route::currentRouteNamed('harilibur.index') ? 'active' : ''}}{{ Route::currentRouteNamed('mutasi.index') ? 'active' : '' }}{{ Route::currentRouteNamed('mutasi.view') ? 'active' : '' }}">
              <a href="#">
                <i class="fa fa-gear"></i> <span>Master & Setup</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="{{ Route::currentRouteNamed('pegawai.index') ? 'active' : '' }}"><a href="{{ route('pegawai.index') }}"><i class="fa fa-circle-o"></i> Pegawai</a></li>
                <li class="{{ Route::currentRouteNamed('skpd.index') ? 'active' : '' }}"><a href="{{ route('skpd.index') }}"><i class="fa fa-circle-o"></i> Skpd</a></li>
                <li class="{{ Route::currentRouteNamed('golongan.index') ? 'active' : ''}}"><a href="{{ route('golongan.index') }}"><i class="fa fa-circle-o"></i> Golongan</a></li>
                <li class="{{ Route::currentRouteNamed('jabatan.index') ? 'active' : ''}}"><a href="{{ route('jabatan.index') }}"><i class="fa fa-circle-o"></i> Jabatan</a></li>
                <li class="{{ Route::currentRouteNamed('struktural.index') ? 'active' : ''}}"><a href="{{ route('struktural.index') }}"><i class="fa fa-circle-o"></i> Struktural/Eselon</a></li>
                <li class="{{ Route::currentRouteNamed('mutasi.index') ? 'active' : '' }}{{ Route::currentRouteNamed('mutasi.view') ? 'active' : '' }}"><a href="{{ route('mutasi.index') }}"><i class="fa fa-circle-o"></i> Mutasi</a></li>
                <li class="{{ Route::currentRouteNamed('harilibur.index') ? 'active' : '' }}"><a href="{{ route('harilibur.index') }}"><i class="fa fa-circle-o"></i> Hari Libur & Cuti Bersama</a></li>
              </ul>
            </li>
            @endif
            @if(session('status') == 'administrator' || session('status') == 'superuser')
            <li class="treeview {{ Route::currentRouteNamed('apel.index') ? 'active' : ''}}{{ Route::currentRouteNamed('apel.mesin') ? 'active' : ''}}">
              <a href="#">
                <i class="fa fa-flag"></i> <span>Manajemen Apel</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="{{ Route::currentRouteNamed('apel.index') ? 'active' : ''}}"><a href="{{ route('apel.index') }}"><i class="fa fa-circle-o"></i> Jadwal Apel</a></a></li>
                <li class="{{ Route::currentRouteNamed('apel.mesin') ? 'active' : ''}}"><a href="{{ route('apel.mesin')}}"><i class="fa fa-circle-o"></i> Daftar Mesin Apel</a></li>
              </ul>
            </li>
            @endif
            @if(session('status') != 'pegawai')
            <li class="treeview {{ Route::currentRouteNamed('user.index') ? 'active' : ''}}{{ Route::currentRouteNamed('user.reset') ? 'active' : ''}}">
              <a href="{{ route('user.index') }}">
                <i class="fa fa-users"></i> <span>Manajemen User</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                @if(session('status') == 'administrator' || session('status') == 'superuser')
                <li class="{{ Route::currentRouteNamed('user.index') ? 'active' : ''}}"><a href="{{ route('user.index')}}"><i class="fa fa-circle-o"></i> Tambah Akun</a></li>
                @endif
                @if(session('status') == 'admin')
                <li class="{{ Route::currentRouteNamed('pegawai.index') ? 'active' : '' }}">
                  <a href="{{ route('pegawai.index') }}">
                    <i class="fa fa-circle-o"></i> <span>Pegawai</span>
                  </a>
                </li>
                @endif
                @if(session('status') == 'administrator' || session('status') == 'admin' || session('status') == 'superuser')
                <li class="{{ Route::currentRouteNamed('user.reset') ? 'active' : ''}}"><a href="{{ route('user.reset')}}"><i class="fa fa-circle-o"></i> Reset Password</a></li>
                @endif
              </ul>
            </li>
            @endif
            @if (session('status') == 'administrator' || session('status') == 'superuser')
              <li class="{{ Route::currentRouteNamed('manajemenintervensi.index') ? 'active' : '' }}">
                <a href="{{ route('manajemenintervensi.index') }}">
                  <i class="fa fa-envelope"></i> <span>Manajemen Intervensi</span>
                </a>
              </li>
            @endif
            @if(session('status') == 'administrator' || session('status') == 'admin' || session('status') == 'pegawai' || session('status') == 'superuser')
            <li class="{{ Route::currentRouteNamed('intervensi.index') ? 'active' : '' }}">
              <a href="{{ route('intervensi.index') }}">
                <i class="fa fa-envelope"></i> <span>Intervensi</span>
              </a>
            </li>
            @endif
            @if(session('status') == 'administrator' || session('status') == 'superuser')
            <li class="treeview {{ Route::currentRouteNamed('absensi.index') ? 'active' : '' }}{{ Route::currentRouteNamed('absensi.filterAdministrator') ? 'active' : '' }}{{ Route::currentRouteNamed('pegawaiapel.detail') ? 'active' : ''}}{{ Route::currentRouteNamed('apel.pegawai') ? 'active' : ''}}{{ Route::currentRouteNamed('pegawaiapel.store') ? 'active' : ''}}{{ Route::currentRouteNamed('absenhari.administrator') ? 'active' : ''}}{{ Route::currentRouteNamed('absenhari.administratorstore') ? 'active' : ''}}">
              <a href="">
                <i class="fa fa-calendar"></i> <span>Absensi</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="{{ Route::currentRouteNamed('absenhari.administrator') ? 'active' : ''}}{{ Route::currentRouteNamed('absenhari.administratorstore') ? 'active' : ''}}">
                  <a href="{{ route('absenhari.administrator') }}"><i class="fa fa-circle-o"></i> <span>Absensi</span></a>
                </li>
                <li class="{{ Route::currentRouteNamed('absensi.index') ? 'active' : '' }}{{ Route::currentRouteNamed('absensi.filterAdministrator') ? 'active' : '' }}">
                  <a href="{{ route('absensi.index') }}"><i class="fa fa-circle-o"></i> <span>Rekap Absensi</span></a>
                </li>
                <li class="{{ Route::currentRouteNamed('apel.pegawai') ? 'active' : ''}}{{ Route::currentRouteNamed('pegawaiapel.detail') ? 'active' : ''}}{{ Route::currentRouteNamed('pegawaiapel.store') ? 'active' : ''}}"><a href="{{ route('apel.pegawai')}}"><i class="fa fa-circle-o"></i> Absen Apel Pegawai</a></li>
              </ul>
            </li>
            @endif
            @if(session('status') == 'admin')
            <li class="treeview {{ Route::currentRouteNamed('absensi.skpd') ? 'active' : ''}}{{ Route::currentRouteNamed('apelskpd') ? 'active' : '' }}{{ Route::currentRouteNamed('apelskpd.store') ? 'active' : '' }}{{ Route::currentRouteNamed('absenhari.skpd') ? 'active' : '' }}{{ Route::currentRouteNamed('absenhari.skpdStore') ? 'active' : '' }}">
              <a href="">
                <i class="fa fa-calendar"></i> <span>Absensi</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li class="{{ Route::currentRouteNamed('absenhari.skpd') ? 'active' : '' }}{{ Route::currentRouteNamed('absenhari.skpdStore') ? 'active' : '' }}">
                  <a href="{{ route('absenhari.skpd') }}"><i class="fa fa-circle-o"></i> <span>Absensi</span></a>
                </li>
                <li class="{{ Route::currentRouteNamed('absensi.skpd') ? 'active' : '' }}">
                  <a href="{{ route('absensi.skpd') }}"><i class="fa fa-circle-o"></i> <span>Rekap Absensi</span></a>
                </li>
                <li class="{{ Route::currentRouteNamed('apelskpd') ? 'active' : '' }}{{ Route::currentRouteNamed('apelskpd.store') ? 'active' : '' }}">
                  <a href="{{ route('apelskpd') }}"><i class="fa fa-circle-o"></i> <span>Absen Apel Pegawai</span></a>
                </li>
              </ul>
            </li>
            @endif
            @if(session('status') == 'pegawai')
            <li class="{{ Route::currentRouteNamed('laporanPegawai') ? 'active' : '' }}">
              <a href="{{ route('laporanPegawai') }}">
                <i class="fa fa-calendar"></i> <span>Absensi</span>
              </a>
            </li>
            @endif
            <li class="{{ Route::currentRouteNamed('mutasi.view.pegawai') ? 'active' : '' }}">
              <a href="{{ route('mutasi.view.pegawai') }}">
                <i class="fa fa-code-fork"></i> <span>Histori Mutasi</span>
              </a>
            </li>
            @if(session('status') != 'pegawai')
            <li class="treeview {{ Route::currentRouteNamed('tpp.index') ? 'active' : ''}}{{ Route::currentRouteNamed('pejabatdokumen.index') ? 'active' : '' }}{{ Route::currentRouteNamed('laporanAdministrator') ? 'active' : '' }}{{ Route::currentRouteNamed('laporanAdmin') ? 'active' : '' }}{{ Route::currentRouteNamed('laporanAdmin.store') ? 'active' : '' }}">
              <a href="">
                <i class="fa fa fa-file"></i> <span>Laporan</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                @if (session('status') == ('administrator') || session('status') == 'superuser')
                <li class="{{ Route::currentRouteNamed('laporanAdministrator') ? 'active' : '' }}">
                  <a href="{{ route('laporanAdministrator') }}">
                    <i class="fa fa-circle-o"></i> <span>Cetak TPP</span>
                  </a>
                </li>
                @endif
                @if (session('status') == ('admin'))
                <li class="{{ Route::currentRouteNamed('laporanAdmin') ? 'active' : '' }}{{ Route::currentRouteNamed('laporanAdmin.store') ? 'active' : '' }}">
                  <a href="{{ route('laporanAdmin') }}">
                    <i class="fa fa-circle-o"></i> <span>Cetak TPP</span>
                  </a>
                </li>
                @endif
                @if(session('status') == 'administrator' || session('status') == 'admin')
                <li class="{{ Route::currentRouteNamed('pejabatdokumen.index') ? 'active' : '' }}">
                  <a href="{{ route('pejabatdokumen.index') }}">
                    <i class="fa fa-circle-o"></i> <span>Pejabat Dokumen</span>
                  </a>
                </li>
                @endif
              </ul>
            </li>
            @endif
          </ul>
        </section>
