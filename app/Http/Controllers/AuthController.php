<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput($request->only('email'));
    }

    $credentials = $request->only('email', 'password');

    if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // Update last login timestamp
        $user = Auth::user();
        $user->last_login_at = now();
        $user->save();

        // Redirect based on role
        return $this->redirectToDashboard($user->role);
    }

    return redirect()->back()
        ->withErrors(['email' => 'Invalid credentials'])
        ->withInput($request->only('email'));
}

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard($role)
    {
        switch ($role) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Finance':
                return redirect()->route('finance.dashboard');
            case 'Operator':
                return redirect()->route('operator.dashboard');
            case 'Manager':
                return redirect()->route('manager.dashboard');
            case 'Helper':
                return redirect()->route('health-checks.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
     */
    /**
 * Handle user registration
 */
public function register(Request $request)
{
    // Check if the authenticated user is either Admin or Manager
    $currentUser = auth()->user();
    if (!$currentUser->hasRole(['Admin', 'Manager'])) {
        return redirect()->route('dashboard')->with('error', 'You do not have permission to create an employee.');
    }

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:employees',
        'phone_number' => 'required|string|unique:employees',
        'date_of_birth' => 'nullable|date',
        'gender' => 'nullable|string',
        'profile_picture' => 'nullable|image|max:2048',
        'aadhaar_card' => 'nullable|string',
        'driving_license' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'permanent_address' => 'nullable|string',
        'current_address' => 'nullable|string',
        'role' => 'required|in:Admin,Marketing,Supervisor,Manager,Finance,Helper,Operator',
        'bank_name' => 'required|string|max:255',
        'bank_account_number' => 'required|string|max:255',
        'bank_ifsc_code' => 'required|string|max:255',
        'password' => 'required|string|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput($request->except('password', 'password_confirmation'));
    }

    // Handle file uploads
    $profilePicturePath = null;
    if ($request->hasFile('profile_picture')) {
        $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
    }

    $drivingLicensePath = null;
    if ($request->hasFile('driving_license')) {
        $drivingLicensePath = $request->file('driving_license')->store('driving_licenses', 'public');
    }

    // Create employee record
    $employee = Employee::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'date_of_birth' => $request->date_of_birth,
        'gender' => $request->gender,
        'profile_picture' => $profilePicturePath,
        'aadhaar_card' => $request->aadhaar_card,
        'driving_license' => $drivingLicensePath,
        'permanent_address' => $request->permanent_address,
        'current_address' => $request->current_address,
        'role' => $request->role,
        'bank_name' => $request->bank_name,
        'bank_account_number' => $request->bank_account_number,
        'bank_ifsc_code' => $request->bank_ifsc_code,
        'username' => $this->generateUniqueUsername($request->first_name, $request->last_name),
        'password' => Hash::make($request->password),
        'status' => 'Active',
    ]);

    // Create user record for login
    $user = new User();
    $user->name = $employee->getFullNameAttribute();
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->role = $request->role;
    $user->save();

    // Redirect to the current user's dashboard with success message
    return $this->redirectToDashboard($currentUser->role)
        ->with('success', 'Employee created successfully.');
}

    /**
     * Generate a unique username
     */
    private function generateUniqueUsername($firstName, $lastName)
    {
        $baseUsername = Str::lower(Str::substr($firstName, 0, 1) . $lastName);
        $username = $baseUsername;
        $counter = 1;

        while (Employee::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle password change
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect',
            ]);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
