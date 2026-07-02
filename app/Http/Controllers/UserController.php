<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // You can redirect or return a response here
        return redirect()->route('signup')->with('success', 'User registered successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function logout(User $user)
    {
        Auth::logout();
        return redirect()->route('login');
    }
    /**
     * Handle user login.
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->intended('/dashboard'); // Redirect to the intended page after login
        }

        return redirect()->route('login')->withErrors(['email' => 'Invalid credentials.']);    
    
    }
}
