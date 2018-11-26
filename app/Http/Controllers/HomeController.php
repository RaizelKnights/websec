<?php

namespace App\Http\Controllers;

use App\PasswordHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }
    public function gantiPass(){
        return view('auth.passwords.change');
    }
    

    public function changePassword(Request $request){

        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error","Password Saat Ini Tidak Valid!.");
        }

        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            //Current password and new password are same
            return redirect()->back()->with("error","Password Anda Sama Seperti Password Sekarang!.");
        }

        $validatedData = $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|dumbpwd|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/|string|min:8',
            'password_confirmation' => 'required|min:8'
         ]);

        //Check Password History
        $user = Auth::user();
        $passwordHistories = $user->passwordHistories()->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->take(3)->get();
        foreach($passwordHistories as $passwordHistory){
            echo $passwordHistory->password;
            if (Hash::check($request->get('new-password'), $passwordHistory->password)) {
                // The passwords matches
                return redirect()->back()->with("error","Password Anda Sudah Pernah Di Gunakan Sebelumnya.");
            }
        }


        //Change Password

        $user->password = bcrypt($request->get('new-password'));
        $user->save();

        //entry into password history
        $passwordHistory = PasswordHistory::create([
            'user_id' => $user->id,
            'password' => bcrypt($request->get('new-password'))
        ]);

        return redirect()->back()->with("success","Password Sukses Di Ubah!");

    }
}
