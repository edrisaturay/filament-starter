<?php

namespace EdrisaTuray\FilamentStarter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class DeveloperGateController
 *
 * Handles the developer gate authentication UI.
 */
class DeveloperGateController extends Controller
{
    /**
     * Show the login form for the developer gate.
     */
    public function show()
    {
        return view('filament-starter::developer-gate');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $password = config('filament-starter.developer_gate.password');

        if ($request->input('password') === $password) {
            $request->session()->put('starter_developer_gate_passed', true);

            return redirect()->intended();
        }

        return back()->withErrors(['password' => 'Invalid developer password.']);
    }
}
