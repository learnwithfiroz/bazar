<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->isMessenger()) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['current_balance' => 0.00, 'low_balance_alert_limit' => 500.00, 'currency' => 'BDT']
            );
            $transactions = $wallet->transactions()->latest()->paginate(20);
            $fundRequests = $wallet->fundRequests()->latest()->paginate(10);
            return view('wallets.messenger', compact('wallet', 'transactions', 'fundRequests'));
        }

        // Ensure all messengers have an active wallet record
        $messengerUsers = User::where('role', 'messenger')->get();
        foreach ($messengerUsers as $m) {
            Wallet::firstOrCreate(
                ['user_id' => $m->id],
                ['current_balance' => 0.00, 'low_balance_alert_limit' => 500.00, 'currency' => 'BDT']
            );
        }

        // For Principal, PA, and Family: Fetch all messenger wallets
        $wallets = Wallet::whereHas('user', function ($q) {
            $q->where('role', 'messenger');
        })->with('user')->get();

        $pendingRequests = FundRequest::where('status', 'PENDING')->with(['requester', 'wallet'])->latest()->get();
        $recentTransactions = WalletTransaction::with(['wallet.user', 'performer'])->latest()->take(30)->get();

        return view('wallets.index', compact('wallets', 'pendingRequests', 'recentTransactions'));
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount'    => 'required|numeric|min:1',
            'notes'     => 'nullable|string|max:255',
        ]);

        $this->walletService->topUpWallet(
            $request->wallet_id,
            Auth::id(),
            (float) $request->amount,
            $request->notes ?? 'ফান্ড রিচার্জ (টপ-আপ)'
        );

        return back()->with('success', 'মেসেঞ্জারের ওয়ালেটে ৳ ' . number_format($request->amount, 2) . ' সফলভাবে রিচার্জ করা হয়েছে।');
    }

    public function storeFundRequest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['current_balance' => 0.00, 'low_balance_alert_limit' => 500.00, 'currency' => 'BDT']
        );

        $fundRequest = FundRequest::create([
            'wallet_id'    => $wallet->id,
            'requested_by' => $user->id,
            'amount'       => $request->amount,
            'reason'       => $request->reason,
            'status'       => 'PENDING',
        ]);

        $principal = User::where('role', 'principal')->first();
        $principalPhone = $principal->phone_number ?? '';
        $whatsappUrl = WhatsAppService::generateFundRequestLink($fundRequest, $principalPhone);

        return back()->with([
            'success' => 'ফান্ড রিকোয়েস্ট সফলভাবে জমা হয়েছে।',
            'whatsapp_fund_url' => $whatsappUrl,
        ]);
    }

    public function actionFundRequest(Request $request, FundRequest $fundRequest)
    {
        $request->validate([
            'action' => 'required|in:APPROVE,REJECT',
            'amount' => 'nullable|numeric|min:1',
            'notes'  => 'nullable|string|max:255',
        ]);

        if ($request->action === 'APPROVE') {
            $this->walletService->approveFundRequest(
                $fundRequest->id,
                Auth::id(),
                $request->filled('amount') ? (float) $request->amount : null,
                $request->notes ?? ''
            );
            return back()->with('success', 'ফান্ড রিকোয়েস্ট অনুমোদিত হয়েছে এবং মেসেঞ্জারের ওয়ালেটে টাকা যোগ করা হয়েছে।');
        } else {
            $this->walletService->rejectFundRequest(
                $fundRequest->id,
                Auth::id(),
                $request->notes ?? ''
            );
            return back()->with('warning', 'ফান্ড রিকোয়েস্ট প্রত্যাখ্যান করা হয়েছে।');
        }
    }

    /**
     * 1-by-1 Delete Fund Request (Super Admin only)
     */
    public function destroyFundRequest(FundRequest $fundRequest)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'শুধুমাত্র সুপার এডমিন ফান্ড রিকোয়েস্ট ডিলিট করতে পারবেন।');
        }
        $fundRequest->delete();
        return back()->with('success', 'ফান্ড রিকোয়েস্টটি সফলভাবে ডিলিট করা হয়েছে।');
    }

    /**
     * 1-by-1 Delete Wallet Transaction (Super Admin only)
     */
    public function destroyTransaction(WalletTransaction $transaction)
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'শুধুমাত্র সুপার এডমিন ট্রানজেকশন রেকর্ড ডিলিট করতে পারবেন।');
        }

        // Adjust wallet balance if needed or remove the ledger log
        $wallet = $transaction->wallet;
        if ($wallet) {
            if ($transaction->type === 'CREDIT') {
                $wallet->decrement('current_balance', (float) $transaction->amount);
            } elseif ($transaction->type === 'DEBIT') {
                $wallet->increment('current_balance', (float) $transaction->amount);
            }
        }

        $transaction->delete();

        return back()->with('success', 'ট্রানজেকশন রেকর্ডটি সফলভাবে ডিলিট করা হয়েছে এবং সংশ্লিষ্ট ওয়ালেট ব্যালেন্স সমন্বয় করা হয়েছে।');
    }
}
