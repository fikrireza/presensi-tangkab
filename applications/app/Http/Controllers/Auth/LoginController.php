<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use App\Models\User;
use Validator;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function showLoginForm()
    {
      return view('auth.login');
    }

    public function loginProcess(Request $request)
    {

      $message = [
        'nip_sapk.required' => 'Wajib di isi',
        'password.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nip_sapk' => 'required',
        'password' => 'required'
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('index')->withErrors($validator)->withInput();
      }

      if(Auth::attempt(['nip_sapk'=>$request->nip_sapk, 'password'=>$request->password]))
      {
        $user = Auth::user();

        $set = user::find(Auth::user()->id);
        $getcounter = $set->seen;
        $set->seen = $getcounter+1;
        $set->save();

        if($getcounter=="0") {
          return redirect('firstLogin')->with('firsttimelogin', "Selamat Datang. Ubah Password Anda Sekarang.");
        } else {
          return redirect('home');
        }
      }
      else
      {
        return redirect()->route('index')->with('messageloginfailed', "Periksa Kembali NIP dan Password Anda.")->withInput();
      }
    }

    public function logout()
    {
      session()->flush();
      Auth::logout();
      return redirect()->route('index');
    }
}
