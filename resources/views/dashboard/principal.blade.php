@extends('layouts.app')

@section('title', 'প্রিন্সিপাল ও এক্সিকিউটিভ ড্যাশবোর্ড')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ 
    topUpModal: false, 
    resetModal: false,
    budgetModal: false,
    selectedWalletId: '{{ $messengers->first()->wallet->id ?? '' }}', 
    selectedMessengerName: '{{ $messengers->first()->name ?? '' }}',
    openTopUp(walletId, name) {
        this.selectedWalletId = walletId;
        this.selectedMessengerName = name;
        this.topUpModal = true;
    }
}">
    
    <!-- Top Greeting Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-purple-950 rounded-3xl p-5 sm:p-7 text-white shadow-xl relative overflow-hidden flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="space-y-1 relative z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full bg-purple-500/30 text-purple-300 text-[10px] font-bold border border-purple-400/30">
                    👑 প্রিন্সিপাল এক্সিকিউটিভ প্যানেল
                </span>
                <span class="text-xs text-slate-400">📅 {{ date('l, d F Y') }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                স্বাগতম, {{ auth()->user()->name }}!
            </h1>
            <p class="text-xs text-slate-300">দৈনিক বাজার খরচ, বাজেট ট্র্যাকিং, ভাউচার অডিট ও ফান্ড কন্ট্রোল</p>
        </div>

        <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto relative z-10 flex-wrap">
            <button @click="openTopUp('{{ $messengers->first()->wallet->id ?? '' }}', '{{ $messengers->first()->name ?? '' }}')" class="py-2.5 px-3.5 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-2xl shadow-lg shadow-brand-600/30 flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                <span>➕ ফান্ড রিচার্জ</span>
            </button>
            <a href="{{ route('bazaar-demands.create') }}" class="py-2.5 px-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-2xl shadow-md flex items-center justify-center gap-1.5 transition">
                <span>🛍️ শপিং লিস্ট</span>
            </a>
            <a href="{{ route('expenses.voucher-check') }}" class="py-2.5 px-3.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-2xl backdrop-blur-md border border-white/20 flex items-center justify-center gap-1.5 transition text-center">
                <span>🔎 ভাউচার চেক</span>
            </a>
            @if(auth()->user()->isPrincipal())
            <a href="{{ route('system.backup') }}" title="ডেটাবেস ব্যাকআপ ফাইল ডাউনলোড করুন" class="py-2.5 px-3 bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-300 border border-emerald-400/30 text-xs font-bold rounded-2xl transition flex items-center justify-center gap-1">
                <span>💾 ব্যাকআপ</span>
            </a>
            <button @click="resetModal = true" title="সম্পূর্ণ ডাটা রিসেট" class="py-2.5 px-3 bg-rose-500/20 hover:bg-rose-500/40 text-rose-300 border border-rose-400/30 text-xs font-bold rounded-2xl transition flex items-center justify-center gap-1">
                <span>⚠️ রিসেট</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Monthly Budget Ceiling & Progress Card -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-black text-sm">
                    🎯
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">চলতি মাসের বাজার বাজেট ট্র্যাকার ({{ date('F Y') }})</h3>
                    <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">বাজেট সিলিং: <strong>৳ {{ number_format($monthlyBudgetLimit, 2) }}</strong> | খরচ হয়েছে: <strong class="text-slate-900 dark:text-white">৳ {{ number_format($monthExpense, 2) }}</strong></span>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                <span class="px-2.5 py-1 rounded-xl text-xs font-black {{ $budgetPercent > 90 ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : ($budgetPercent > 70 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300') }}">
                    {{ $budgetPercent }}% ব্যবহৃত
                </span>
                <button @click="budgetModal = true" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    ⚙️ বাজেট পরিবর্তন
                </button>
            </div>
        </div>

        <div class="w-full bg-slate-100 dark:bg-slate-800 h-3 rounded-full overflow-hidden p-0.5 border border-slate-200 dark:border-slate-700">
            <div class="h-2 rounded-full transition-all duration-700 {{ $budgetPercent > 90 ? 'bg-rose-500' : ($budgetPercent > 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $budgetPercent }}%"></div>
        </div>
    </div>

    <!-- 4 High-Level Modern Metric KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Today's Spend -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">আজকের বাজার খরচ</span>
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                    🛒
                </div>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">৳ {{ number_format($todayExpense, 2) }}</div>
                <span class="text-[11px] text-slate-400 block mt-1">তারিখ: {{ date('d M, Y') }}</span>
            </div>
        </div>

        <!-- Month-to-date Spend -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">চলতি মাসের মোট খরচ</span>
                <div class="w-9 h-9 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                    📈
                </div>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-blue-700 dark:text-blue-400 tracking-tight">৳ {{ number_format($monthExpense, 2) }}</div>
                <span class="text-[11px] text-slate-400 block mt-1">{{ date('F Y') }}</span>
            </div>
        </div>

        <!-- Total Wallet Cash Float -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition relative overflow-hidden group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">হাতে ক্যাশ ফান্ড</span>
                <div class="w-9 h-9 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                    💳
                </div>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-purple-700 dark:text-purple-400 tracking-tight">৳ {{ number_format($totalWalletsBalance, 2) }}</div>
                <span class="text-[11px] text-slate-400 block mt-1">মেসেঞ্জার ওয়ালেট সঞ্চিত</span>
            </div>
        </div>

        <!-- Pending Review & Fund Requests Alert -->
        <a href="{{ route('expenses.voucher-check', ['status' => 'SUBMITTED']) }}" class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition relative overflow-hidden group block">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ভাউচার চেক পেন্ডিং</span>
                <div class="w-9 h-9 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                    🔔
                </div>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-black text-rose-600 tracking-tight">{{ $pendingReviewsCount + $pendingFundRequestsCount }} টি</div>
                <span class="text-[11px] text-slate-400 block mt-1">ভাউচার অডিট করুন ➔</span>
            </div>
        </a>

    </div>

    <!-- Visual Interactive Charts Row (Line Chart & Donut Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
        
        <!-- Left: Daily Spending Trend Line/Bar Chart (8 cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2.5">
                <div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>📊</span>
                        <span>দৈনিক খরচের ভিজ্যুয়াল ট্রেন্ড গ্রাফ</span>
                    </h2>
                    <span class="text-[10px] text-slate-400">চলতি মাসের দিন অনুযায়ী খরচের গ্রাফচিত্র</span>
                </div>
                <span class="text-xs font-black text-brand-600">৳ {{ number_format($monthExpense, 2) }}</span>
            </div>
            <div class="h-60 sm:h-72 w-full relative">
                <canvas id="spendingTrendChart"></canvas>
            </div>
        </div>

        <!-- Right: Category Spending Donut Chart (4 cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2.5">
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                    <span>🍩</span>
                    <span>ক্যাটাগরি ভিত্তিক খরচের পাই-চার্ট</span>
                </h2>
            </div>
            <div class="h-60 sm:h-72 w-full relative flex items-center justify-center">
                <canvas id="categoryDonutChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Active Bazaar Shopping Demand Checklist Cards -->
    @if($activeDemands->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2.5">
            <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>🛍️</span>
                <span>চলমান বাজার শপিং ডিমান্ড ও চেকলিস্ট</span>
            </h2>
            <a href="{{ route('bazaar-demands.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">সকল লিস্ট ➔</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($activeDemands as $demand)
            <a href="{{ route('bazaar-demands.show', $demand->id) }}" class="p-3.5 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 rounded-2xl border border-slate-200/80 dark:border-slate-700 flex items-center justify-between transition group">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 block">{{ $demand->title }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">📅 {{ date('d M', strtotime($demand->target_date)) }} • 👤 {{ $demand->creator->name }}</span>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black border {{ $demand->status_badge_class }}">
                        {{ $demand->purchased_count }}/{{ $demand->total_items_count }} টি
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Pending Fund Requests Alert Box -->
    @if($pendingFundRequests->isNotEmpty())
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 dark:from-amber-950/30 dark:via-amber-950/20 dark:to-orange-950/30 border border-amber-200 dark:border-amber-900/60 rounded-3xl p-4 sm:p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">⚠️</span>
                <h2 class="text-xs sm:text-sm font-black text-amber-950 dark:text-amber-200">জরুরি ফান্ড রিকোয়েস্ট (অনুমোদনের অপেক্ষায়)</h2>
            </div>
            <span class="px-2.5 py-0.5 bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-200 rounded-full text-[10px] font-bold">{{ $pendingFundRequests->count() }} টি আবেদন</span>
        </div>

        <div class="space-y-2.5">
            @foreach($pendingFundRequests as $req)
            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-amber-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $req->requester->name }}</span>
                        <span class="px-2.5 py-0.5 bg-amber-100 dark:bg-amber-950 text-amber-900 dark:text-amber-300 text-xs font-black rounded-full">৳ {{ number_format($req->amount, 2) }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400">{{ $req->reason ?: 'বাজার খরচের জন্য অতিরিক্ত ফান্ড প্রয়োজন।' }}</p>
                </div>
                <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto">
                    <form action="{{ route('fund-requests.action', $req->id) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="APPROVE">
                        <button type="submit" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm">
                            ✅ অনুমোদন
                        </button>
                    </form>
                    <form action="{{ route('fund-requests.action', $req->id) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="REJECT">
                        <button type="submit" class="w-full py-2 px-4 bg-rose-50 dark:bg-rose-950 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 text-xs font-bold rounded-xl">
                            ❌ বাতিল
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Main Grid: Messengers Wallet Status & Recent Expense Slips -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
        
        <!-- Left: Messengers' Live Balances (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2.5">
                    <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🛵</span>
                        <span>মেসেঞ্জার ওয়ালেট তালিকা</span>
                    </h2>
                    <a href="{{ route('wallets.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">সকল হিস্ট্রি ➔</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($messengers as $m)
                    <div class="p-3.5 rounded-2xl border {{ ($m->wallet && $m->wallet->isLowBalance()) ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700' }} flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                                <span>🛵</span>
                                <span>{{ $m->name }}</span>
                            </div>
                            <div class="text-sm font-black mt-0.5 {{ ($m->wallet && $m->wallet->isLowBalance()) ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">
                                ৳ {{ number_format($m->wallet->current_balance ?? 0, 2) }}
                            </div>
                            @if($m->wallet && $m->wallet->isLowBalance())
                            <span class="inline-block text-[9px] font-bold text-rose-700 bg-rose-100 dark:bg-rose-950 dark:text-rose-300 px-1.5 py-0.5 rounded mt-0.5">⚠️ ব্যালেন্স কম</span>
                            @endif
                        </div>

                        <button @click="openTopUp('{{ $m->wallet->id ?? '' }}', '{{ $m->name }}')" class="px-3 py-2 bg-white dark:bg-slate-700 hover:bg-brand-50 text-brand-700 dark:text-brand-300 border border-brand-300 dark:border-brand-700 text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1">
                            <span>➕ রিচার্জ</span>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-2 text-center">কোনো মেসেঞ্জার নেই।</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Recent Daily Expenses & Slips (8 cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2.5">
                <div>
                    <h2 class="text-xs sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>📋</span>
                        <span>সাম্প্রতিক বাজার খরচ ও স্লিপসমূহ</span>
                    </h2>
                    <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">দোকান ট্র্যাকিং, মেমো ও নিরীক্ষা</span>
                </div>
                <a href="{{ route('expenses.voucher-check') }}" class="text-xs font-bold text-indigo-700 dark:text-indigo-400 hover:underline">🔎 ভাউচার চেক ➔</a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentExpenses as $expense)
                <div class="py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 hover:bg-slate-50/70 dark:hover:bg-slate-800/60 p-2.5 rounded-2xl transition">
                    <div class="space-y-1 w-full sm:w-auto">
                        <div class="flex items-center justify-between sm:justify-start gap-2 flex-wrap">
                            <a href="{{ route('expenses.show', $expense->id) }}" class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white hover:text-brand-600">
                                {{ $expense->title }}
                            </a>
                            @if($expense->vendor_name)
                            <span class="text-[10px] font-bold text-indigo-800 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950 border border-indigo-200 dark:border-indigo-800 px-2 py-0.5 rounded">🏪 {{ $expense->vendor_name }}</span>
                            @endif
                            @if($expense->memo_no)
                            <span class="text-[10px] font-bold text-brand-700 bg-brand-50 dark:bg-brand-950 px-2 py-0.5 rounded">মেমো: {{ $expense->memo_no }}</span>
                            @endif
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $expense->status_badge_class }}">
                                {{ $expense->status_label }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2.5 text-[11px] text-slate-500 dark:text-slate-400 flex-wrap">
                            <span>📅 {{ date('d M, Y', strtotime($expense->expense_date)) }}</span>
                            <span>👤 {{ $expense->creator->name }}</span>
                            <span>📦 {{ $expense->items->count() }} আইটেম</span>
                            @if($expense->slips->isNotEmpty())
                            <span class="text-brand-600 font-bold">📸 {{ $expense->slips->count() }} মেমো</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end pt-1 sm:pt-0">
                        <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white">৳ {{ number_format($expense->total_amount, 2) }}</span>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('expenses.show', $expense->id) }}" class="px-3 py-1.5 bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                                🔍 রিভিউ
                            </a>
                            <a href="{{ route('reports.print-day', ['expense_id' => $expense->id]) }}" target="_blank" title="প্রিন্ট ভাউচার" class="p-1.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                                🖨️
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-slate-400 text-xs">
                    এখনও কোনো খরচের এন্ট্রি দেওয়া হয়নি।
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Top-Up Wallet Modal -->
    <div x-show="topUpModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="topUpModal = false" class="bg-white dark:bg-slate-900 w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>➕</span>
                    <span>ফান্ড রিচার্জ (মেসেঞ্জার ওয়ালেট)</span>
                </h3>
                <button @click="topUpModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <form action="{{ route('wallets.topup') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">মেসেঞ্জারের নাম নির্বাচন করুন *</label>
                    <select name="wallet_id" x-model="selectedWalletId" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                        @foreach($messengers as $m)
                        <option value="{{ $m->wallet->id ?? '' }}">🛵 {{ $m->name }} (বর্তমান ব্যালেন্স: ৳ {{ number_format($m->wallet->current_balance ?? 0, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">টাকার পরিমাণ (৳) *</label>
                    <input type="number" inputmode="decimal" step="0.01" min="1" name="amount" required placeholder="যেমন: 5000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-black focus:ring-2 focus:ring-brand-500 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">মন্তব্য / নোট (ঐচ্ছিক)</label>
                    <input type="text" name="notes" placeholder="যেমন: সাপ্তাহিক বাজার ফান্ড বা ক্যাশ রিচার্জ" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-800 dark:text-white">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="topUpModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl">বাতিল</button>
                    <button type="submit" class="py-2.5 px-5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-md">টাকা রিচার্জ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Budget Edit Modal -->
    <div x-show="budgetModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="budgetModal = false" class="bg-white dark:bg-slate-900 w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🎯</span>
                    <span>মাসিক বাজার বাজেট সিলিং নির্ধারণ</span>
                </h3>
                <button @click="budgetModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <form action="{{ route('system.budget') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">মাসিক সর্বোচ্চ বাজার বাজেট (টাকা) *</label>
                    <input type="number" inputmode="decimal" step="100" min="1000" name="monthly_budget" value="{{ $monthlyBudgetLimit }}" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-base font-black focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">বাজেটের ৮০% বা ৯০% খরচ হয়ে গেলে ড্যাশবোর্ডে সতর্কতা দেখানো হবে।</span>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="budgetModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl">বাতিল</button>
                    <button type="submit" class="py-2.5 px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md">বাজেট সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Super Admin Full System Data Reset Security Modal -->
    @if(auth()->user()->isPrincipal())
    <div x-show="resetModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="resetModal = false" class="bg-white dark:bg-slate-900 w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl border border-rose-300 dark:border-rose-900 p-5 sm:p-7 space-y-4">
            <div class="flex justify-between items-start border-b border-rose-100 dark:border-rose-900/60 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-950 text-rose-700 flex items-center justify-center text-xl font-bold">
                        ⚠️
                    </div>
                    <div>
                        <h3 class="text-base font-black text-rose-950 dark:text-rose-300">সম্পূর্ণ সিস্টেম ডাটা রিসেট</h3>
                        <span class="text-[11px] text-rose-600 font-bold">Super Admin Security Action</span>
                    </div>
                </div>
                <button @click="resetModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <div class="p-4 bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-2xl space-y-2 text-xs text-rose-900 dark:text-rose-300">
                <span class="font-black block uppercase">রিসেট দিলে যা যা পরিষ্কার হবে:</span>
                <ul class="list-disc list-inside space-y-1 font-medium text-[11px]">
                    <li>সমস্ত বাজার খরচের হিসাব ও আইটেমের তালিকা মুছে যাবে।</li>
                    <li>সমস্ত আপলোডকৃত ক্যাশ মেমো ও বিলের ছবি ডিলিট হবে।</li>
                    <li>সমস্ত ওয়ালেট ট্রানজেকশন ও ফান্ড রিকোয়েস্ট ক্লিয়ার হবে।</li>
                    <li>মেসেঞ্জারদের ওয়ালেট ব্যালেন্স ৳ ০.০০ এ রিসেট হবে।</li>
                    <li class="font-black text-emerald-800 dark:text-emerald-400 list-none pt-1">🔒 <strong>ইউজার অ্যাকাউন্টসমূহ ও লগইন সুরক্ষিত থাকবে।</strong></li>
                </ul>
            </div>

            <form action="{{ route('system.reset-all') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase mb-1">নিশ্চিত করতে সুপার এডমিনের পাসওয়ার্ড লিখুন *</label>
                    <input type="password" name="admin_password" required placeholder="আপনার বর্তমান পাসওয়ার্ড লিখুন..." class="w-full px-3.5 py-3 rounded-xl border border-rose-300 dark:border-rose-800 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-rose-500 bg-rose-50/30 dark:bg-slate-800 dark:text-white">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="resetModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl">বাতিল</button>
                    <button type="submit" onclick="return confirm('আপনি কি চূড়ান্তভাবে নিশ্চিত যে আপনি সমস্ত খরচের ডাটা ও মেমো মুছে ফ্রেশ ক্লিন সিস্টেম করতে চান?');" class="py-2.5 px-5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl shadow-lg shadow-rose-600/30">
                        ⚠️ হ্যাঁ, সম্পূর্ণ ডাটা রিসেট করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Spending Trend Chart (Line / Bar Chart)
    const trendCtx = document.getElementById('spendingTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json(!empty($chartLabels) ? $chartLabels : [date('d M')]),
                datasets: [{
                    label: 'বাজার খরচ (টাকা)',
                    data: @json(!empty($chartValues) ? $chartValues : [0]),
                    backgroundColor: 'rgba(22, 163, 74, 0.75)',
                    borderColor: '#16a34a',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '৳' + value; }
                        }
                    }
                }
            }
        });
    }

    // 2. Category Donut Chart
    const donutCtx = document.getElementById('categoryDonutChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json(!empty($catLabels) ? $catLabels : ['অন্যান্য']),
                datasets: [{
                    data: @json(!empty($catValues) ? $catValues : [1]),
                    backgroundColor: [
                        '#16a34a', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#64748b'
                    ],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
