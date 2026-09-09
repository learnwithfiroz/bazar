@extends('layouts.app')

@section('title', 'পারিবারিক খরচের ড্যাশবোর্ড')

@section('content')
<div class="space-y-4 sm:space-y-6 max-w-5xl mx-auto">
    
    <!-- Hero Greeting Banner -->
    <div class="bg-gradient-to-r from-purple-950 via-slate-900 to-indigo-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-purple-500/30 text-purple-300 text-[10px] font-bold border border-purple-400/30">
                    🏡 পারিবারিক মনিটরিং পোর্টাল
                </span>
                <span class="text-xs text-slate-400">📅 {{ date('F Y') }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white">স্বাগতম, {{ auth()->user()->name }}!</h1>
            <p class="text-xs text-purple-200">চলতি মাসের গৃহস্থালি বাজার ও পরিবারের প্রয়োজনীয় ব্যয়ের সারসংক্ষেপ</p>
        </div>

        <div class="bg-white/10 backdrop-blur-md px-5 py-3.5 rounded-2xl border border-white/20 relative z-10 w-full sm:w-auto text-center sm:text-right">
            <span class="text-[10px] uppercase tracking-wider text-purple-200 font-bold block">চলতি মাসের সর্বমোট খরচ</span>
            <div class="text-2xl sm:text-3xl font-black text-brand-400">৳ {{ number_format($monthExpense, 2) }}</div>
        </div>
    </div>

    <!-- Category Breakdown & Recent Expenses -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6 items-start">
        
        <!-- Category Breakdown (5 cols) -->
        <div class="md:col-span-5 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>📊</span>
                    <span>ক্যাটাগরি অনুযায়ী খরচের অনুপাত</span>
                </h2>
                <span class="text-[10px] text-slate-400">লাইভ</span>
            </div>

            <div class="space-y-3.5">
                @forelse($categoryBreakdown as $cat)
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-slate-700 font-bold">{{ $cat->category }}</span>
                        <span class="text-slate-900 font-black">৳ {{ number_format($cat->total, 2) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 h-2.5 rounded-full" style="width: {{ $monthExpense > 0 ? min(100, ($cat->total / $monthExpense) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">এখনও কোনো খরচ নেই</p>
                @endforelse
            </div>
        </div>

        <!-- Daily Expenses Log (7 cols) -->
        <div class="md:col-span-7 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>📅</span>
                    <span>দৈনিক খরচের তালিকা (চলতি মাস)</span>
                </h2>
                <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-brand-600 hover:underline">সকল তালিকা ➔</a>
            </div>

            <div class="divide-y divide-slate-100 max-h-[420px] overflow-y-auto pr-1">
                @forelse($recentExpenses as $exp)
                <div class="py-3 flex justify-between items-center text-xs hover:bg-slate-50/50 p-2 rounded-2xl transition">
                    <div>
                        <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $exp->title }}</div>
                        <div class="text-slate-500 text-[11px] mt-0.5">{{ date('d F, Y', strtotime($exp->expense_date)) }} • {{ $exp->items->count() }} আইটেম • {{ $exp->creator->name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-black text-slate-900 text-sm sm:text-base">৳ {{ number_format($exp->total_amount, 2) }}</div>
                        <a href="{{ route('expenses.show', $exp->id) }}" class="text-[11px] text-purple-700 hover:underline font-bold">🔍 বিস্তারিত</a>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-8 text-center">এখনও কোনো খরচের হিসাব নেই</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
