<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (isset(Auth::user()->id)) {
            if(Auth::user()->isAdmin()) {
                return redirect('/admin');
            }

            if(Auth::user()->isUser()) {
                return redirect('/main');
            }

        }

        return view('welcome');
    }
}
