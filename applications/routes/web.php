<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/home/detail-absensi/{id}', 'HomeController@detailabsensi')->name('detail.absensi')->middleware('administrator');

// Pegawai
Route::get('pegawai', 'PegawaiController@index')->name('pegawai.index');
Route::get('getPegawai', 'PegawaiController@getPegawai');
Route::get('pegawai/create', 'PegawaiController@create')->name('pegawai.create');
Route::post('pegawai', 'PegawaiController@store')->name('pegawai.post');
Route::get('pegawai/edit/{id}', 'PegawaiController@edit')->name('pegawai.edit');
Route::post('pegawai/edit', 'PegawaiController@editStore')->name('pegawai.editStore');

// Mutasi
Route::get('mutasi', 'MutasiController@index')->name('mutasi.index');
Route::get('mutasi/create/{id}', 'MutasiController@create')->name('mutasi.create');
Route::post('mutasi/create', 'MutasiController@createStore')->name('mutasi.createStore');
Route::get('mutasi/view/{id}', 'MutasiController@view')->name('mutasi.view');
Route::get('mutasi/viewall/{id}', 'MutasiController@viewAll')->name('mutasi.viewall');
Route::get('mutasi/view', 'MutasiController@viewPegawai')->name('mutasi.view.pegawai');

// SKPD
Route::get('skpd', 'SkpdController@index')->name('skpd.index')->middleware('administrator');
Route::post('skpd', 'SkpdController@store')->name('skpd.post')->middleware('administrator');
Route::get('skpd/{id}', 'SkpdController@bind');
Route::post('skpd/edit', 'SkpdController@edit')->name('skpd.edit')->middleware('administrator');
Route::get('skpd/non/{id}', 'SkpdController@nonAktif')->name('skpd.nonaktif');
Route::get('skpd/aktif/{id}', 'SkpdController@aktif')->name('skpd.aktif');

// Golongan
Route::get('golongan', 'GolonganController@index')->name('golongan.index')->middleware('administrator');
Route::post('golongan', 'GolonganController@store')->name('golongan.post')->middleware('administrator');
Route::get('golongan/non/{id}', 'GolonganController@nonAktif')->name('golongan.nonaktif')->middleware('administrator');
Route::get('golongan/aktif/{id}', 'GolonganController@aktif')->name('golongan.aktif')->middleware('administrator');

// Jabatan
Route::get('jabatan', 'JabatanController@index')->name('jabatan.index')->middleware('administrator');
Route::post('jabatan', 'JabatanController@store')->name('jabatan.post')->middleware('administrator');
Route::get('jabatan/{id}', 'JabatanController@bind');
Route::post('jabatan/edit', 'JabatanController@edit')->name('jabatan.edit')->middleware('administrator');

// Pengecualian TPP
Route::get('pengecualian', 'PengecualianController@index')->name('pengecualian.index')->middleware('administrator');
Route::post('pengecualian', 'PengecualianController@store')->name('pengecualian.post')->middleware('administrator');
Route::get('pengecualian/{id}', 'PengecualianController@bind');
Route::post('pengecualian/edit', 'PengecualianController@edit')->name('pengecualian.edit')->middleware('administrator');

// Struktural
Route::get('struktural', 'StrukturalController@index')->name('struktural.index')->middleware('administrator');
Route::post('struktural', 'StrukturalController@store')->name('struktural.post')->middleware('administrator');
Route::get('struktural/non/{id}', 'StrukturalController@nonAktif')->name('struktural.nonaktif')->middleware('administrator');
Route::get('struktural/aktif/{id}', 'StrukturalController@aktif')->name('struktural.aktif')->middleware('administrator');

// Hari Libur
Route::get('harilibur', 'HariLiburController@index')->name('harilibur.index')->middleware('administrator');
Route::post('harilibur', 'HariLiburController@store')->name('harilibur.post')->middleware('administrator');
Route::get('harilibur/{id}', 'HariLiburController@bind');
Route::post('harilibur/edit', 'HariLiburController@edit')->name('harilibur.edit')->middleware('administrator');

// Intervensi
Route::get('intervensi', 'IntervensiController@index')->name('intervensi.index');
Route::post('intervensi', 'IntervensiController@store')->name('intervensi.post');
Route::get('intervensi/bind/{id}', 'IntervensiController@bind');
Route::post('intervensi/edit', 'IntervensiController@edit')->name('intervensi.edit');
Route::get('intervensi/batal/{id}', 'IntervensiController@batal')->name('intervensi.batal');
Route::get('intervensi/kelola', 'IntervensiController@kelola')->name('intervensi.kelola');
Route::get('intervensi/kelola/{id}', 'IntervensiController@kelolaAksi')->name('intervensi.kelola.aksi');
Route::post('intervensi/kelola', 'IntervensiController@kelolaPost')->name('intervensi.kelola.post');
Route::get('intervensi/kelola/approve/{id}', 'IntervensiController@kelolaApprove');
Route::get('intervensi/kelola/decline/{id}', 'IntervensiController@kelolaDecline');
Route::get('intervensi/skpd/{id}', 'IntervensiController@skpd')->name('intervensi.skpd');
Route::get('intervensi/reset-status/{id}', 'IntervensiController@resetStatus')->name('intervensi.resetstatus');
Route::get('intervensi/download-surat-ijin/{id}', 'IntervensiController@suratIjin')->name('intervensi.suratijin');
Route::get('intervensi/preview-surat-ijin', 'IntervensiController@previewSuratIjin')->name('intervensi.previewsuratijin');

// Manajemen Intervensi
Route::get('manajemen-intervensi', 'ManajemenIntervensiController@index')->name('manajemenintervensi.index');
Route::post('manajemen-intervensi', 'ManajemenIntervensiController@store')->name('manajemenintervensi.store');

// Revisi Intervensi
Route::get('revisi-intervensi', 'RevisiIntervensiController@index')->name('revisiintervensi.index');
Route::get('revisi-intervensi/create', 'RevisiIntervensiController@create')->name('revisiintervensi.create');
Route::post('revisi-intervensi/caripegawai', 'RevisiIntervensiController@caripegawai')->name('revisiintervensi.caripegawai');
Route::post('revisi-intervensi/createStore', 'RevisiIntervensiController@createStore')->name('revisiintervensi.createStore');


// Absensi Administrator
Route::get('absensi', 'AbsensiController@index')->name('absensi.index')->middleware('administrator');
Route::post('absensi', 'AbsensiController@filterAdministrator')->name('absensi.filterAdministrator')->middleware('administrator');
// Absensi Pegawai
Route::get('absensi-detail', 'AbsensiController@detailPegawai')->name('absensi.pegawai')->middleware('pegawai');
Route::post('absensi-detail', 'AbsensiController@filterMonth')->name('absensi.filterMonth')->middleware('pegawai');
// Absensi SKPD
Route::get('absensi-skpd', 'AbsensiController@absenSKPD')->name('absensi.skpd')->middleware('admin');
Route::post('absensi-skpd', 'AbsensiController@filterAdmin')->name('absensi.filterAdmin')->middleware('admin');

Route::get('absen-skpd', 'AbsensiController@absenHariSKPD')->name('absenhari.skpd')->middleware('admin');
Route::post('absen-skpd', 'AbsensiController@absenHariSKPDStore')->name('absenhari.skpdStore')->middleware('admin');

Route::get('absen-harian', 'AbsensiController@absenHariAdministrator')->name('absenhari.administrator')->middleware('administrator');
Route::post('absen-harian', 'AbsensiController@absenHariAdministratorStore')->name('absenhari.administratorstore')->middleware('administrator');


// Manajemen User
Route::get('users', 'UserController@index')->name('user.index');
Route::post('users', 'UserController@store')->name('user.create');
Route::get('users/delete/{id}', 'UserController@delete');
Route::get('users/reset', 'UserController@reset')->name('user.reset');
Route::get('users/reset/{id}', 'UserController@resetPassword');

Route::get('profil', 'UserController@profil')->name('profil.index');


// Manajemen Apel
Route::get('apel', 'ApelController@index')->name('apel.index')->middleware('administrator');
Route::post('apel', 'ApelController@store')->name('apel.post')->middleware('administrator');
Route::get('apel/{id}', 'ApelController@bind');
Route::post('apel/edit', 'ApelController@edit')->name('apel.edit')->middleware('administrator');
Route::get('mesinapel', 'ApelController@mesin')->name('apel.mesin')->middleware('administrator');
Route::post('mesinapel', 'ApelController@mesinPost')->name('mesin.post')->middleware('administrator');
Route::get('apel-pegawai', 'ApelController@pegawaiapel')->name('apel.pegawai')->middleware('administrator');
Route::post('apel-pegawai', 'ApelController@pegawaiapelStore')->name('pegawaiapel.store')->middleware('administrator');
Route::get('apel-pegawai-cetak', 'ApelController@pegawaiapelCetak')->name('pegawaiapel.cetak')->middleware('administrator');
Route::get('apel-pegawai/detail/{skpd}/{tanggal_apel}', 'ApelController@pegawaiapelDetail')->name('pegawaiapel.detail')->middleware('administrator');
Route::get('apel-pegawai/detail/cetak', 'ApelController@pegawaiapelDetailCetak')->name('pegawaiapel.detailCetak');
Route::get('apel-skpd', 'ApelController@apelSKPD')->name('apelskpd');
Route::post('apel-skpd', 'ApelController@apelSKPDStore')->name('apelskpd.store');

// Auth::routes();
Route::get('/', 'Auth\LoginController@showLoginForm')->name('index');
Route::post('login', 'Auth\LoginController@loginProcess')->name('login.proses');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('firstLogin', 'UserController@firstLogin')->name('firstLogin');
Route::post('firstLogin', 'UserController@ubahPassword')->name('firstLogin.post');

Route::get('cetakTpp', 'HomeController@cetakTPP')->name('cetakTPP');


// Pejabat Dokumen
Route::get('pejabat-dokumen', 'PejabatDokumenController@index')->name('pejabatdokumen.index');
Route::post('pejabat-dokumen', 'PejabatDokumenController@store')->name('pejabatdokumen.post');
Route::get('pejabat-dokumen/{id}', 'PejabatDokumenController@bind');
Route::post('pejabat-dokumen/edit', 'PejabatDokumenController@edit')->name('pejabatdokumen.edit');
Route::get('pejabat/flagstatus/{id}', 'PejabatDokumenController@changeflag')->name('pejabatdokumen.changeflag');


// Laporan Administartor
Route::get('laporan', 'LaporanController@laporanAdministrator')->name('laporanAdministrator')->middleware('administrator');
Route::post('laporan', 'LaporanController@laporanAdministratorStore')->name('laporanAdministrator.store')->middleware('administrator');
Route::get('cetakAdministrator', 'LaporanController@cetakAdministrator')->name('laporan.cetakAdministrator')->middleware('administrator');
// Laporan Admin
Route::get('laporan-skpd', 'LaporanController@laporanAdmin')->name('laporanAdmin')->middleware('admin');
Route::post('laporan-skpd', 'LaporanController@laporanAdminStore')->name('laporanAdmin.store')->middleware('admin');
Route::get('cetakAdmin', 'LaporanController@cetakAdmin')->name('laporan.cetakAdmin')->middleware('admin');
// Laporan Pegawai
Route::get('laporan-pegawai', 'LaporanController@laporanPegawai')->name('laporanPegawai');
Route::post('laporan-pegawai', 'LaporanController@laporanPegawaiStore')->name('laporanPegawai.store');
Route::get('cetakPegawai', 'LaporanController@cetakPegawai')->name('laporan.cetakPegawai');


// Shift
Route::get('shift', 'ShiftController@index')->name('shift.index');
Route::post('shift', 'ShiftController@skpdShift')->name('shift.skpd');


// CronJob for Convert TaLog to Preson_Log
Route::get('convert', 'ConvertController@log_to_preson_log')->name('convert.index');
