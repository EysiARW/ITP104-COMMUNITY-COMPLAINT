<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user()->load(['role', 'area']),
            'areas' => Area::where('is_active', true)->orderBy('area_name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'contact_number' => ['required', 'regex:/^(09|\+639)\d{9}$/'],
            'area_id' => ['nullable', 'exists:areas,area_id'],
            'address' => ['nullable', 'string', 'max:255'],
        ], ['contact_number.regex' => 'Use a valid Philippine mobile number, e.g. 09171234567.']);

        $user->update($data);
        Audit::log('UPDATE', 'Updated own profile', 'users', $user->user_id);

        return back()->with('success', 'Profile updated.');
    }

    public function password(Request $request)
    {
        $request->validateWithBag('password', [
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], ['password.different' => 'The new password must be different from the current one.']);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.'], 'password');
        }

        $user->update(['password_hash' => Hash::make($request->password)]);
        Audit::log('UPDATE', 'Changed own password', 'users', $user->user_id);

        return back()->with('success', 'Password changed.');
    }
}
