<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\FundRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's profile settings with rich activity metrics.
     */
    public function show()
    {
        $user = Auth::user();
        
        $userExpensesCount = Expense::where('created_by', $user->id)->count();
        $userExpensesTotal = Expense::where('created_by', $user->id)->sum('total_amount');
        
        $userWallet = Wallet::where('user_id', $user->id)->first();
        $userFundRequestsCount = FundRequest::where('requested_by', $user->id)->count();

        $monthlyBudgetLimit = (float) SystemSetting::get('monthly_budget_limit', 50000);

        return view('profile.show', compact(
            'user',
            'userExpensesCount',
            'userExpensesTotal',
            'userWallet',
            'userFundRequestsCount',
            'monthlyBudgetLimit'
        ));
    }

    /**
     * Update basic profile details (Name, Phone, Email, Avatar) with 100% native PHP file upload.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate text fields safely without touching Symfony mime guessers
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'email'        => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $data = [
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
            'email'        => $request->email ?: null,
        ];

        // Safe Avatar upload using native PHP file operations (Zero finfo dependency)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            if ($file->getSize() > 10 * 1024 * 1024) {
                return back()->withErrors(['avatar' => 'ছবির সাইজ সর্বোচ্চ ১০ মেগাবাইট হতে পারবে।']);
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'])) {
                return back()->withErrors(['avatar' => 'শুধুমাত্র JPG, PNG, WebP বা GIF ছবি আপলোড করা যাবে।']);
            }

            $storageDir = storage_path('app/public/avatars');
            $publicDir = public_path('storage/avatars');
            if (!file_exists($storageDir)) @mkdir($storageDir, 0775, true);
            if (!file_exists($publicDir)) @mkdir($publicDir, 0775, true);

            // Native delete without Flysystem/finfo
            if ($user->avatar) {
                $oldStorage = storage_path('app/public/' . $user->avatar);
                $oldPublic = public_path('storage/' . $user->avatar);
                if (file_exists($oldStorage)) @unlink($oldStorage);
                if (file_exists($oldPublic)) @unlink($oldPublic);
            }

            $fileName = Str::random(40) . '.' . $ext;
            $file->move($storageDir, $fileName);
            @copy($storageDir . '/' . $fileName, $publicDir . '/' . $fileName);
            $data['avatar'] = 'avatars/' . $fileName;
        }

        $user->update($data);

        return back()->with('success', 'আপনার প্রোফাইল তথ্য ও ছবি সফলভাবে সংরক্ষিত হয়েছে।');
    }

    /**
     * Change user password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ডটি সঠিক নয়।'])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'আপনার পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
    }
}
