<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login (NPK + Password).
     */
    public function login(Request $request)
    {
        $request->validate([
            'npk' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('npk', $request->npk)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();

            // Redirect monitoring accounts to TV dashboard
            if ($user->role === 'monitoring') {
                return redirect()->route('tv.dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'npk' => 'NPK atau Password salah.',
        ])->withInput($request->only('npk'));
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Show user management page (accessible from dashboard).
     */
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('auth.users', compact('users'));
    }

    /**
     * Store a new user.
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'npk' => 'required|string|unique:users,npk',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'npk' => $request->npk,
            'name' => $request->name,
            'email' => $request->npk . '@mtm.local',
            'password' => Hash::make($request->password),
            'role' => 'admin',
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Delete a user.
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting self
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Update own profile (NPK & password).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'npk' => 'required|string|unique:users,npk,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $user->npk = $request->npk;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Redirect back to wherever user came from
        $redirect = $user->role === 'monitoring' ? route('tv.dashboard') : route('dashboard');
        return redirect($redirect)->with('success', 'Profile berhasil diperbarui!');
    }
}
