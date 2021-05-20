<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'paypal_email' => ['required', 'string', 'email', 'max:255'],
            'username'     => ['required', 'string', 'max:255', 'unique:users'],
            'password'     => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        die("Registrations are suspended");

        $data = [
            'name'         => $data['name'],
            'email'        => $data['email'],
            'paypal_email' => $data['paypal_email'],
            'username'     => $data['username'],
            'password'     => Hash::make($data['password']),
        ];

        if ((new User())->setting('user.commission')) {
            $data['commission'] = (new User())->setting('user.commission');
        }

        if ((new User())->setting('user.minimum_payout')) {
            $data['minimum_payout'] = (new User())->setting('user.minimum_payout');
        }

        $user = User::create($data);

        if ($user->id === 1) {
            $user->user_type = 'super';
            $user->email_verified_at = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
            $user->save();
        }

        return $user;
    }
}
