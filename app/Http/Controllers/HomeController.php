<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only('home');
    }

    /**
     * Show home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Show Login.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function login()
    {
        return redirect(route('nova.login'));
    }

    /**
     * Show registration page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function register()
    {
        return redirect(route('nova.register'));
    }

    /**
     * Show Password reset.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function passwordReset()
    {
        return redirect(route('nova.password.request'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function home()
    {
        return redirect(config('nova.path'));
    }
}
