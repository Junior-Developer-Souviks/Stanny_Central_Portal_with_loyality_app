<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class Profile extends Component
{
    public $user;
    public $activeTab = 'app';

    public $new_password;
    public $confirm_password;

    public function mount()
    {
        $this->user = Auth::guard('admin')->user();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function changePassword()
    {
        $this->validate([
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);

        $this->user->update([
            'password' => Hash::make($this->new_password),
        ]);

        session()->flash('success', 'Password changed successfully!');
        $this->reset(['new_password', 'confirm_password']);
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
