<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Logout extends Component
{

    public function destroy()
    {
        // Ensure logout for the correct guard
        Auth::guard('admin')->logout();

        // Invalidate the session
        request()->session()->invalidate();

        // Regenerate CSRF token to prevent session fixation
        request()->session()->regenerateToken();

        // Redirect to the admin login page
        return redirect()->route('admin.login');
    }

    
    public function render()
    {
        return view('livewire.auth.logout');
    }
}
