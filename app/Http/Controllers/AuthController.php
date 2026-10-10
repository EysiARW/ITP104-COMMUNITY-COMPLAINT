<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            Audit::log('LOGIN', 'User logged in', 'users', Auth::id());

            // Admins always land on their dashboard; ignore any remembered complainant-only page.
            if (Auth::user()->isAdmin()) {
                $request->session()->forget('url.intended');
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        // Check if user exists but is pending or rejected to provide clear feedback
        $existing = User::where('email', $credentials['email'])->first();
        if ($existing && Hash::check($credentials['password'], $existing->password_hash)) {
            if ($existing->isPendingVerification()) {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your account registration is still pending verification and approval by the Barangay administration. Please wait for an administrator to review your documents.']);
            }
            if ($existing->isVerificationRejected()) {
                $reason = $existing->rejection_reason ? ": {$existing->rejection_reason}" : '.';
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your account registration was rejected' . $reason . ' You may register again with valid documents.']);
            }
            if (! $existing->is_active) {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact the Barangay hall.']);
            }
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records, or the account is inactive.']);
    }

    public function showRegister()
    {
        return view('auth.register', ['areas' => Area::where('is_active', true)->orderBy('area_name')->get()]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'contact_number' => ['required', 'regex:/^(09|\+639)\d{9}$/'],
            'area_id' => [
                'required',
                Rule::exists('areas', 'area_id')->where('is_active', true),
            ],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'verification_doc_type' => ['required', 'string', 'in:voters_cert,valid_id,certificate_of_residency'],
            'verification_doc' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB max
            'privacy' => ['accepted'],
        ], [
            'area_id.exists' => 'Please select a valid, active purok/area.',
            'contact_number.regex' => 'Use a valid Philippine mobile number, e.g. 09171234567.',
            'verification_doc_type.required' => 'Please select the type of document you are submitting.',
            'verification_doc_type.in' => 'Please select a valid document type.',
            'verification_doc.required' => 'You must upload a document (Voter\'s Certification, Valid ID, or Certificate of Residency) for verification.',
            'verification_doc.mimes' => 'Document must be an image (JPG, PNG) or PDF.',
            'verification_doc.max' => 'Document file must not exceed 5 MB.',
            'privacy.accepted' => 'You must agree to the Data Privacy notice to register.',
        ]);

        $file = $request->file('verification_doc');
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->store('verification_documents', 'local');

        $user = User::create([
            'role_id' => User::ROLE_COMPLAINANT,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'area_id' => $data['area_id'],
            'address' => $data['address'],
            'password_hash' => Hash::make($data['password']),
            'verification_doc_path' => $storedPath,
            'verification_doc_type' => $data['verification_doc_type'],
            'verification_doc_name' => $originalName,
            'verification_status' => User::STATUS_PENDING,
            'is_active' => false, // Inactive until approved by admin
        ]);

        Audit::log('REGISTER', "New complainant registered (Pending Verification): {$user->full_name}", 'users', $user->user_id);

        return redirect()->route('login')->with('success', 'Registration submitted successfully! Your account is queued for Barangay administrator verification. You will be able to log in once your documents are approved.');
    }

    public function logout(Request $request)
    {
        Audit::log('LOGOUT', 'User logged out', 'users', Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}
