<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthMail;

class AuthController extends Controller
{
    function index(){
        return view('halaman_auth/login');
    }

    function login(Request $request){
        $request->validate([
            'email'=>'required',
            'password'=>'required',
        ],[
            'email.required'=>'Email wajib diisi',
            'password.required'=>'Password wajib diisi',
        ]);

        $infoLogin=[
            'email'=>$request->email,
            'password'=>$request->password,
        ];

        if(Auth::attempt($infoLogin)){
            if(Auth::user()->email_verified_at != null){
               if(Auth::user()->role === 'admin'){
                return redirect()->route('admin')->with('success','Halo admin, Anda berhasil login');
               }else if(Auth::user()->role === 'user'){
                return redirect()->route('user')->with('success','Halo user, Anda berhasil login');
               }
            }else{
                Auth::logout();
                return redirect()->route('auth')->withErrors('Akun Anda belum aktif, silahkan verifikasi terlebih dahulu');
            }
           
        }else{
            return redirect()->route('auth')->withErrors('Email atau Password salah');
        };
    }

    function create(){
        return view('halaman_auth/register');
    }

    function register(Request $request){

        $str=Str::random(100);
        
        $request->validate([
            'fullname'=>'required|min:5',
            'email'=>'required|unique:users|email',
            'password'=>'required|min:6',
            'gambar'=>'required|image|file',
        ],[
            'fullname.required'=>'Full Name wajib diisi',
            'fullname.min'=>'Full Name minimal 5 karakter',
            'password.required'=>'Password wajib diisi',
            'password.min'=>'Password minimal 6 karakter',
            'email.required'=>'Email wajib diisi',
            'email.unique'=>'Email wajib unik',
            'gambar.required'=>'Gambar wajib diisi',
            'gambar.image'=>'Gambar wajib berupa image',
            'gambar.file'=>'Gambar wajib berupa file',
        ]);

        $gambar_file = $request->file('gambar');
        $gambar_ekstensi=$gambar_file->extension();
        $nama_gambar=date('ymdhis').".".$gambar_ekstensi;

        $gambar_file->move(public_path('picture/accounts'),$nama_gambar);

        $infoRegister=[
            'fullname'=>$request->fullname,
            'email'=>$request->email,
            'password'=>$request->password,
            'gambar'=>$nama_gambar,
            'verify_key'=> $str,

        ];

        User::create($infoRegister);

        $details=[
            'name'=>$infoRegister['fullname'],
            'role'=>'user',
            'website'=>'SMTP Multi User',
            'datetime'=>date('Y-m-d H:i:s'),
            'url'=>'http://'. request()->getHttpHost()."/"."verify/".$infoRegister['verify_key'],
        ];

        Mail::to($infoRegister['email'])->send(new AuthMail($details));

        return redirect()->route('auth')->with('success','Link Verifikasi telah dikirim ke email anda. Cek email untuk melakukan verifikasi');
        
    }

    function verify($verify_key){
        $keyCheck = User::select('verify_key')
        ->where('verify_key',$verify_key)
        ->exists();

        if($keyCheck){
            $user = User::where('verify_key',$verify_key)->update([
                'email_verified_at' => date('Y-m_d H:i:s')
            ]);
            return redirect()->route('auth')->with('success','Verifikasi Berhasil. Akun Anda sudah aktif');
        }else{
            return redirect()->route('auth')->withErrors('Keys tidak valid. Pastikan sudah register');
        };
    }
}
