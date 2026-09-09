<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * High-Scale User Management (Optimized for 10,000+ Users with Server-Side Search & Pagination)
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'শুধুমাত্র সুপার এডমিনের ইউজার ম্যানেজমেন্ট করার অধিকার আছে।');
        }

        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');

        $query = User::with(['wallet'])->withCount('expenses');

        // Indexed search by name or phone
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($roleFilter && in_array($roleFilter, ['principal', 'pa', 'messenger', 'family'])) {
            $query->where('role', $roleFilter);
        }

        // Status filter
        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', (bool) $statusFilter);
        }

        // Paginate 20 records per page for blazing fast performance
        $users = $query->latest('id')->paginate(20)->withQueryString();

        // Fast Indexed Metric Aggregates (Direct DB SQL counters)
        $totalUsers = User::count();
        $activeUsersCount = User::where('is_active', true)->count();
        $messengerCount = User::where('role', 'messenger')->count();
        $paCount = User::where('role', 'pa')->count();
        $familyCount = User::where('role', 'family')->count();
        $principalCount = User::where('role', 'principal')->count();
        $totalMessengerBalance = Wallet::sum('current_balance');

        return view('users.index', compact(
            'users',
            'totalUsers',
            'activeUsersCount',
            'messengerCount',
            'paCount',
            'familyCount',
            'principalCount',
            'totalMessengerBalance',
            'search',
            'roleFilter',
            'statusFilter'
        ));
    }

    public function create()
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'অনুমতি নেই।');
        }
        return redirect()->route('users.index');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'অনুমতি নেই।');
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number|max:20',
            'email'        => 'nullable|email|unique:users,email|max:255',
            'password'     => 'required|string|min:6',
            'role'         => ['required', Rule::in(['principal', 'pa', 'messenger', 'family'])],
            'initial_balance' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
            'email'        => $request->email ?: null,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'is_active'    => true,
        ]);

        // If the newly created user is a Messenger, initialize their wallet
        if ($user->isMessenger()) {
            $initialBalance = $request->filled('initial_balance') ? (float) $request->initial_balance : 0.00;
            Wallet::create([
                'user_id'                 => $user->id,
                'current_balance'         => $initialBalance,
                'low_balance_alert_limit' => 500.00,
                'currency'                => 'BDT',
            ]);
        }

        return back()->with('success', "নতুন ইউজার '{$user->name}' ({$user->role_display_name}) সফলভাবে তৈরি করা হয়েছে।");
    }

    public function edit(User $user)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'অনুমতি নেই।');
        }

        $user->load('wallet');
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'অনুমতি নেই।');
        }

        $request->validate([
            'name'                     => 'required|string|max:255',
            'phone_number'             => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'email'                    => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'                     => ['required', Rule::in(['principal', 'pa', 'messenger', 'family'])],
            'is_active'                => 'required|boolean',
            'password'                 => 'nullable|string|min:6',
            'wallet_balance'           => 'nullable|numeric',
            'low_balance_alert_limit'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $user) {
            $data = [
                'name'         => $request->name,
                'phone_number' => $request->phone_number,
                'email'        => $request->email ?: null,
                'role'         => $request->role,
                'is_active'    => (bool) $request->is_active,
            ];

            // Update password if entered
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Handle wallet adjustments if user is/was messenger or has wallet
            if ($user->isMessenger()) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['current_balance' => 0.00, 'low_balance_alert_limit' => 500.00, 'currency' => 'BDT']
                );

                if ($request->filled('wallet_balance')) {
                    $newBalance = (float) $request->wallet_balance;
                    $oldBalance = (float) $wallet->current_balance;
                    
                    if ($newBalance !== $oldBalance) {
                        $wallet->current_balance = $newBalance;
                        $wallet->save();

                        WalletTransaction::create([
                            'wallet_id'      => $wallet->id,
                            'performed_by'   => Auth::id(),
                            'type'           => 'ADJUSTMENT',
                            'amount'         => abs($newBalance - $oldBalance),
                            'balance_before' => $oldBalance,
                            'balance_after'  => $newBalance,
                            'reference_type' => null,
                            'reference_id'   => null,
                            'notes'          => 'সুপার এডমিন কর্তৃক সরাসরি ব্যালেন্স সংশোধন/অ্যাডজাস্টমেন্ট',
                        ]);
                    }
                }

                if ($request->filled('low_balance_alert_limit')) {
                    $wallet->low_balance_alert_limit = (float) $request->low_balance_alert_limit;
                    $wallet->save();
                }
            }
        });

        if ($request->has('from_edit_page')) {
            return redirect()->route('users.index')->with('success', "ইউজার '{$user->name}'-এর যাবতীয় তথ্য ও ব্যালেন্স সফলভাবে আপডেট করা হয়েছে।");
        }

        return back()->with('success', "ইউজার '{$user->name}'-এর যাবতীয় তথ্য সফলভাবে পরিবর্তন করা হয়েছে।");
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'অনুমতি নেই।');
        }

        if ($user->id === Auth::id()) {
            return back()->with('warning', 'আপনি নিজের সুপার এডমিন অ্যাকাউন্ট ডিলিট করতে পারবেন না।');
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', "ইউজার '{$userName}' সফলভাবে ডিলিট করা হয়েছে।");
    }
}
