<?php
namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLogin extends Component
{
    public $email = '';
    public $password = '';
    
    public function mount()
    {
        $user = Auth::guard('admin')->user();
        if($user){
            return redirect()->route('admin.dashboard'); // Middleware will now check authentication
        }
        // $this->fill(['email' => 'admin@gmail.com', 'password' => 'secret']);
    }
    // Validation rules
    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6', // Add password minimum length
    ];

    public function render()
    {
        return view('livewire.auth.admin-login');
    }

    

    // Method for logging in
    public function login()
    {
        $attributes = $this->validate();
      
        if (!Auth::guard('admin')->attempt(['email' => $this->email, 'password' => $this->password])) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }
        // dd($attributes);
        // Set authenticated admin user
        $user = Auth::guard('admin')->user();
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Authentication failed.',
            ]);
        }
        
        // Regenerate session
        // session()->regenerate();
    
        return redirect()->route('admin.dashboard'); // Middleware will now check authentication
    }
    
}
