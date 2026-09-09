@extends('layouts.app')

@section('title', 'মেসেঞ্জার ড্যাশবোর্ড - বাজার ও ওয়ালেট')

@section('content')
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6" x-data="{ fundModal: false }">
    
    <!-- Top Wallet Balance Widget (Luxury Gradient Hero Card) -->
    <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] sm:text-xs text-emerald-400 font-black uppercase tracking-wider block">আমার পেটি-ক্যাশ ওয়ালেট</span>
                    @if($wallet->isLowBalance())
                    <span class="px-2.5 py-0.5 bg-rose-500/20 text-rose-300 border border-rose-500/40 text-[10px] font-black rounded-full animate-pulse">
                        ⚠️ ব্যালেন্স কম
                    </span>
                    @else
                    <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold rounded-full">
                        🟢 ওয়ালেট সচল
                    </span>
                    @endif
                </div>
                <h1 class="text-lg sm:text-xl font-black text-white mt-1">{{ auth()->user()->name }} (মেসেঞ্জার)</h1>
            </div>

            <div class="text-left sm:text-right">
                <span class="text-[11px] text-slate-300 block">বর্তমান ক্যাশ ব্যালেন্স:</span>
                <div class="text-3xl sm:text-4xl font-black tracking-tight {{ $wallet->isLowBalance() ? 'text-rose-400' : 'text-emerald-400' }}">
                    ৳ {{ number_format($wallet->current_balance, 2) }}
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons inside Hero Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 pt-5 mt-4 border-t border-white/10 relative z-10">
            <a href="{{ route('expenses.create') }}" class="py-3 px-4 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white font-black text-xs sm:text-sm rounded-2xl text-center shadow-lg shadow-brand-600/30 flex items-center justify-center gap-2 transition active:scale-[0.98]">
                <span>➕ নতুন বাজার ও বিল এন্ট্রি</span>
            </a>
            <button @click="fundModal = true" class="py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm rounded-2xl text-center backdrop-blur-md border border-white/20 flex items-center justify-center gap-2 transition active:scale-[0.98]">
                <span>💸 নতুন ফান্ডের আবেদন</span>
            </button>
            <a href="{{ route('bazaar-demands.index') }}" class="py-3 px-4 bg-indigo-600/80 hover:bg-indigo-600 text-white font-bold text-xs sm:text-sm rounded-2xl text-center flex items-center justify-center gap-2 transition active:scale-[0.98] col-span-1 sm:col-span-2 md:col-span-1">
                <span>🛍️ শপিং চেকলিস্ট</span>
            </a>
        </div>
    </div>

    <!-- Low Balance & Pending Fund Notice -->
    @if($wallet->isLowBalance() || $activeFundRequest)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @if($wallet->isLowBalance())
        <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 rounded-3xl p-4 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h3 class="text-xs font-bold text-rose-900 dark:text-rose-300">ব্যালেন্স ৳ ৫০০ এর নিচে নেমে গেছে!</h3>
                    <p class="text-[10px] sm:text-[11px] text-rose-700 dark:text-rose-400">বাজার করার জন্য নতুন ফান্ড চেয়ে আবেদন করুন।</p>
                </div>
            </div>
            <button @click="fundModal = true" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm whitespace-nowrap">
                টাকা চান
            </button>
        </div>
        @endif

        @if($activeFundRequest)
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/60 rounded-3xl p-4 flex items-center justify-between shadow-sm">
            <div class="space-y-0.5">
                <span class="text-xs font-bold text-amber-900 dark:text-amber-300 block">⏳ ফান্ড রিকোয়েস্ট পেন্ডিং রয়েছে</span>
                <span class="text-[11px] text-amber-700 dark:text-amber-400">চাহিদা: ৳ {{ number_format($activeFundRequest->amount, 2) }} (অনুমোদনের অপেক্ষায়)</span>
            </div>
            <span class="text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-200 dark:bg-amber-900 px-2.5 py-1 rounded-xl">অপেক্ষমাণ</span>
        </div>
        @endif
    </div>
    @endif

    <!-- Main Content 2-Column Responsive Grid on Desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- Left: Today's Expenses & Submissions (lg:col-span-7) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🛒</span>
                        <span>আজকের বাজার খরচ ({{ date('d M, Y') }})</span>
                    </h2>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">মোট খরচ: <strong class="text-slate-900 dark:text-white">৳ {{ number_format($todayTotal, 2) }}</strong></span>
                </div>
                <a href="{{ route('expenses.create') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">➕ নতুন এন্ট্রি</a>
            </div>

            <div class="space-y-3">
                @forelse($todayExpenses as $exp)
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2.5">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $exp->title }}</div>
                            @if($exp->memo_no)
                            <span class="text-[10px] font-bold text-brand-700 bg-brand-50 dark:bg-brand-950 px-1.5 py-0.5 rounded">মেমো: {{ $exp->memo_no }}</span>
                            @endif
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $exp->status_badge_class }} inline-block mt-0.5">
                                {{ $exp->status_label }}
                            </span>
                        </div>
                        <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white">৳ {{ number_format($exp->total_amount, 2) }}</span>
                    </div>

                    <div class="text-[11px] text-slate-600 dark:text-slate-300">
                        <span class="font-bold">আইটেম:</span>
                        {{ $exp->items->pluck('item_name')->join(', ') }}
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-2.5 border-t border-slate-200 dark:border-slate-700 gap-2 flex-wrap sm:flex-nowrap">
                        <a href="{{ route('expenses.show', $exp->id) }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-brand-600 flex items-center gap-1">
                            <span>🔍 মেমো ও মন্তব্য</span>
                        </a>

                        <a href="{{ route('expenses.whatsapp', $exp->id) }}" target="_blank" class="w-full sm:w-auto py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition">
                            <span>💬 WhatsApp এ প্রিন্সিপালকে পাঠান</span>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    আজকের কোনো খরচের এন্ট্রি দেওয়া হয়নি।
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Recent Past History & Overview (lg:col-span-5) -->
        <div class="lg:col-span-5 space-y-5">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-4 sm:p-5 space-y-3">
                <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2">
                    <h2 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">পূর্ববর্তী খরচের হিস্ট্রি</h2>
                    <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">সকল তালিকা ➔</a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentExpenses as $rexp)
                    <div class="py-2.5 flex justify-between items-center text-xs hover:bg-slate-50/50 dark:hover:bg-slate-800/40 p-1.5 rounded-xl transition">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">{{ $rexp->title }}</div>
                            <div class="text-slate-400 text-[10px]">{{ date('d M, Y', strtotime($rexp->expense_date)) }} • {{ $rexp->items->count() }} আইটেম</div>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-slate-900 dark:text-white">৳ {{ number_format($rexp->total_amount, 2) }}</div>
                            <a href="{{ route('expenses.show', $rexp->id) }}" class="text-[10px] text-brand-600 dark:text-brand-400 hover:underline font-bold">বিস্তারিত ➔</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-3 text-center">এখনও কোনো পুরনো হিসেব নেই।</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Fund Request Modal -->
    <div x-show="fundModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="fundModal = false" class="bg-white dark:bg-slate-900 w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>💸</span>
                    <span>ফান্ডের আবেদন (Fund Request)</span>
                </h3>
                <button @click="fundModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <form action="{{ route('fund-requests.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">কত টাকা প্রয়োজন? (৳) *</label>
                    <input type="number" inputmode="decimal" step="0.01" min="1" name="amount" required placeholder="যেমন: 3000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-black focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">আবেদনের কারণ / বাজারের বিবরণ</label>
                    <textarea name="reason" rows="3" placeholder="যেমন: আগামীকালের মাছ ও মাংস কেনার জন্য অগ্রিম ফান্ড..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="fundModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">বাতিল</button>
                    <button type="submit" class="py-2.5 px-5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-md">রিকোয়েস্ট পাঠান</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
