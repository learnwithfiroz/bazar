<?php

namespace App\Http\Controllers;

use App\Models\BazaarDemand;
use App\Models\Expense;
use App\Models\FundRequest;
use App\Models\SystemSetting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isMessenger()) {
            return $this->messengerDashboard($user);
        }

        if ($user->isFamily()) {
            return $this->familyDashboard();
        }

        // Default to Principal & PA Executive Dashboard
        return $this->principalDashboard();
    }

    private function principalDashboard()
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $todayExpense = Expense::where('expense_date', $today)->sum('total_amount');
        $monthExpense = Expense::where('expense_date', 'like', "{$thisMonth}%")->sum('total_amount');

        // Monthly Budget Limit
        $monthlyBudgetLimit = (float) SystemSetting::get('monthly_budget_limit', 50000);
        $budgetPercent = $monthlyBudgetLimit > 0 ? min(100, round(($monthExpense / $monthlyBudgetLimit) * 100)) : 0;

        // Optimized Messenger Fetch (Fast eager loading with wallet)
        $messengers = User::where('role', 'messenger')->with('wallet')->take(12)->get();
        $totalWalletsBalance = (float) Wallet::sum('current_balance');

        $pendingReviewsCount = Expense::where('status', 'SUBMITTED')->count();
        $pendingFundRequestsCount = FundRequest::where('status', 'PENDING')->count();

        $recentExpenses = Expense::with(['creator', 'items', 'slips', 'comments'])
            ->latest('expense_date')
            ->latest('id')
            ->take(8)
            ->get();

        $pendingFundRequests = FundRequest::where('status', 'PENDING')->with(['requester', 'wallet'])->get();

        // Active Shopping Demands
        $activeDemands = BazaarDemand::with(['creator', 'items'])
            ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
            ->latest('target_date')
            ->take(4)
            ->get();

        // Category breakdown for this month
        $categoryBreakdown = DB::table('expense_items')
            ->join('expenses', 'expense_items.expense_id', '=', 'expenses.id')
            ->where('expenses.expense_date', 'like', "{$thisMonth}%")
            ->select('expense_items.category', DB::raw('SUM(expense_items.total_price) as total'))
            ->groupBy('expense_items.category')
            ->get();

        // Daily spendings for Chart.js
        $dailySpendings = Expense::where('expense_date', 'like', "{$thisMonth}%")
            ->select('expense_date', DB::raw('SUM(total_amount) as total'))
            ->groupBy('expense_date')
            ->orderBy('expense_date', 'asc')
            ->get();

        $chartLabels = $dailySpendings->map(fn($d) => date('d M', strtotime($d->expense_date)))->toArray();
        $chartValues = $dailySpendings->map(fn($d) => (float)$d->total)->toArray();

        $catLabels = $categoryBreakdown->map(fn($c) => $c->category)->toArray();
        $catValues = $categoryBreakdown->map(fn($c) => (float)$c->total)->toArray();

        return view('dashboard.principal', compact(
            'todayExpense',
            'monthExpense',
            'totalWalletsBalance',
            'pendingReviewsCount',
            'pendingFundRequestsCount',
            'recentExpenses',
            'messengers',
            'pendingFundRequests',
            'activeDemands',
            'categoryBreakdown',
            'monthlyBudgetLimit',
            'budgetPercent',
            'chartLabels',
            'chartValues',
            'catLabels',
            'catValues'
        ));
    }

    private function messengerDashboard(User $user)
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['current_balance' => 0.00, 'low_balance_alert_limit' => 500.00, 'currency' => 'BDT']
        );

        $today = now()->toDateString();
        $todayExpenses = Expense::where('created_by', $user->id)
            ->where('expense_date', $today)
            ->with(['items', 'slips'])
            ->get();

        $todayTotal = $todayExpenses->sum('total_amount');

        $recentExpenses = Expense::where('created_by', $user->id)
            ->with(['items', 'slips', 'comments'])
            ->latest('expense_date')
            ->latest('id')
            ->take(5)
            ->get();

        $recentTransactions = $wallet->transactions()->latest()->take(5)->get();
        $activeFundRequest = FundRequest::where('wallet_id', $wallet->id)
            ->where('status', 'PENDING')
            ->first();

        // Assigned or Active Shopping Demands
        $activeDemands = BazaarDemand::with(['creator', 'items'])
            ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
            ->latest('target_date')
            ->take(3)
            ->get();

        // Get Principal's phone number for 1-click WhatsApp
        $principal = User::where('role', 'principal')->first();
        $principalPhone = $principal->phone_number ?? '8801700000000';

        return view('dashboard.messenger', compact(
            'wallet',
            'todayExpenses',
            'todayTotal',
            'recentExpenses',
            'recentTransactions',
            'activeFundRequest',
            'activeDemands',
            'principalPhone'
        ));
    }

    private function familyDashboard()
    {
        $thisMonth = now()->format('Y-m');
        $monthExpense = Expense::where('expense_date', 'like', "{$thisMonth}%")->sum('total_amount');
        
        $dailyExpenses = Expense::where('expense_date', 'like', "{$thisMonth}%")
            ->select('expense_date', DB::raw('SUM(total_amount) as total'))
            ->groupBy('expense_date')
            ->orderBy('expense_date', 'desc')
            ->get();

        $recentExpenses = Expense::with(['creator', 'items', 'slips'])
            ->latest('expense_date')
            ->take(15)
            ->get();

        $activeDemands = BazaarDemand::with(['creator', 'items'])
            ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
            ->latest('target_date')
            ->take(5)
            ->get();

        $categoryBreakdown = DB::table('expense_items')
            ->join('expenses', 'expense_items.expense_id', '=', 'expenses.id')
            ->where('expenses.expense_date', 'like', "{$thisMonth}%")
            ->select('expense_items.category', DB::raw('SUM(expense_items.total_price) as total'))
            ->groupBy('expense_items.category')
            ->get();

        return view('dashboard.family', compact('monthExpense', 'dailyExpenses', 'recentExpenses', 'activeDemands', 'categoryBreakdown'));
    }
}
