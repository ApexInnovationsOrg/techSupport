<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Support\Facades\Session;

use Auth;
// use Illuminate\Routing\Controller;
use App\Employee;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	use AuthenticatesAndRegistersUsers;


	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{

		$this->auth = $auth;
		$this->registrar = $registrar;
		$this->middleware('guest', ['except' => 'getLogout']);
	}

 	public function postLogin(Request $request)
	{
		$this->validate($request, [
			'Email' => 'required|email', 'Password' => 'required',
		]);

		$credentials = $request->only('Email', 'Password');
		if($this->oldAuthenticate($credentials))
		{
			return redirect()->intended($this->redirectPath());
		}

		return redirect($this->loginPath())
		->withInput($request->only('Email', 'remember'))
		->withErrors([
			'Email' => $this->getFailedLoginMessage(),
		]);
	}

    public function oldAuthenticate($credentials)
    {
    	// dd($credentials);
		$username = strtolower($credentials['Email']);
    	$pass = md5("p6^8&" . $credentials['Password']);
		
		if(substr($username,-20) == "@apexinnovations.com"){
			$username = $username;
		}else{
			$username = $username . "@apexinnovations.com";
		}
		
		$user = Employee::whereRaw("Email='". $username ."' AND Password='". $pass ."' AND Active = 'Y'")->first();
		
		if($user !== null){
			Session::put('AdminID', $user->ID);
			Session::put('AdminName', $user->FirstName . ' ' . $user->LastName);
			Auth::loginUsingId($user->ID);
			return true;			
    	}
    	else
    	{
    		return false;
    	}
	}
}


