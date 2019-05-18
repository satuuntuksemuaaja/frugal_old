<?php

class IndexController extends BaseController
{
    public $layout = "layouts.main";

    /**
     * Show the Valendar
     */
    public function index()
    {
        $view = View::make('index');
        $this->layout->title = "Frugal Calendar";
        $this->layout->content = $view;
    }

    /**
     * App Starting point.
     *
     * @return \Illuminate\View\View
     */
    public function login()
    {
        return View::make('login');
    }

    /**
     * Mobile bypass using hash
     *
     * @param $hash
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function loginBypass($hash)
    {
        $user = User::whereBypass($hash)->whereActive(1)->first();
        if ($user)
        {
            Auth::loginUsingId($user->id);
        }
        else return "Access Denied";
        return Redirect::to('/mobile');
    }

    /**
     * Authenticate the user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function auth()
    {
        if (!Input::has('email') || !Input::has('password'))
        {
            return Redirect::to('login')->with('loginFailed', 'error');
        }

        // Begin Authentication
        if (Auth::attempt(['email' => Input::get('email'), 'password' => Input::get('password')]))
        {
            return Redirect::intended('/');
        }
        else
        {
            return Redirect::to('login')->withError('loginFailed');
        }

    }

    /**
     * Show the dashboard for the given designer.
     */
    public function dashboard()
    {
        $this->layout->title = "Frugal Calendar";
        $this->layout->content = View::make('dashboard');
    }

}
