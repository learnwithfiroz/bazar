@extends('layouts.app')

@section('title', 'বাজারের শপিং ডিমান্ড ও হিস্ট্রি চেকলিস্ট')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-300 text-[10px] font-bold border border-indigo-400/30">
                🛍️ ফ্যামিলি শপিং চেকলিস্ট ও হিস্ট্রি
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">বাজারের ডিমান্ড ও অতীত শপিং হিস্ট্রি</h1>
            <p class="text-xs text-slate-300">বাসা থেকে প্রয়োজনীয় পণ্যের তালিকা তৈরি, লাইভ বাজারে চেকলিস্ট ও অতীত হিস্ট্রি আর্কাইভ</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto relative z-10">
            <a href="{{ route('bazaar-demands.create') }}" class="w-full sm:w-auto py-3 px-5 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-2xl shadow-lg shadow-brand-600/30 flex items-center justify-center gap-2 transition active:scale-[0.98]">
                <span>➕ নতুন শপিং লিস্ট তৈরি</span>
                <span>➔</span>
            </a>
        </div>
    </div>

    <!-- 4 High-Level History Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('bazaar-demands.index', ['tab' => 'all']) }}" class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-indigo-400 transition block">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>সর্বমোট লিস্ট</span>
                <span class="text-base">📋</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $totalCount }} টি</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">মোট তৈরিকৃত চাহিদা</span>
        </a>

        <a href="{{ route('bazaar-demands.index', ['tab' => 'active']) }}" class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-amber-400 transition block">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>চলমান ও পেন্ডিং</span>
                <span class="text-base">⏳</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-600 mt-1">{{ $activeCount }} টি</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">বাজারে কেনাকাটা প্রক্রিয়াধীন</span>
        </a>

        <a href="{{ route('bazaar-demands.index', ['tab' => 'history']) }}" class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-emerald-400 transition block">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>সম্পন্ন শপিং হিস্ট্রি</span>
                <span class="text-base">✅</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600 mt-1">{{ $historyCompletedCount }} টি</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">১০০% সম্পন্ন হওয়া বাজার</span>
        </a>

        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>কেনা পণ্যের সংখ্যা</span>
                <span class="text-base">📦</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $totalPurchasedItems }} টি</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">মোট ক্রয়কৃত আইটেম</span>
        </div>
    </div>

    <!-- Segmented Navigation Tabs Bar -->
    <div class="bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex gap-2 overflow-x-auto">
        <a href="{{ route('bazaar-demands.index', ['tab' => 'all']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <span>🔍 সকল শপিং ডিমান্ড ({{ $totalCount }})</span>
        </a>
        <a href="{{ route('bazaar-demands.index', ['tab' => 'active']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'active' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <span>⏳ চলমান চেকলিস্ট ({{ $activeCount }})</span>
        </a>
        <a href="{{ route('bazaar-demands.index', ['tab' => 'history']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $tab === 'history' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <span>📜 অতীত শপিং হিস্ট্রি ও আর্কাইভ ({{ $historyCompletedCount }})</span>
        </a>
    </div>

    <!-- Search & Advanced History Filter Form -->
    <form action="{{ route('bazaar-demands.index') }}" method="GET" class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm grid grid-cols-1 sm:grid-cols-12 gap-3">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <!-- Search Bar -->
        <div class="sm:col-span-4">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">লিস্ট বা পণ্যের নাম দিয়ে সার্চ</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="যেমন: শাকসবজি, রুই মাছ বা সপ্তাহের বাজার..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-800 dark:text-white">
        </div>

        <!-- Date Range Filter -->
        <div class="sm:col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">শুরুর তারিখ</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-800 dark:text-white">
        </div>

        <div class="sm:col-span-3">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">শেষ তারিখ</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold focus:ring-2 focus:ring-indigo-500 dark:bg-slate-800 dark:text-white">
        </div>

        <!-- Action Filter Button -->
        <div class="sm:col-span-2 flex items-end gap-2">
            <button type="submit" class="w-full py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-md transition">
                🔍 ফিল্টার
            </button>
            <a href="{{ route('bazaar-demands.index', ['tab' => $tab]) }}" class="py-2.5 px-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 rounded-xl text-xs font-bold text-center">
                রিসেট
            </a>
        </div>
    </form>

    <!-- Demands & History Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($demands as $demand)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border {{ $demand->status === 'COMPLETED' ? 'border-emerald-200 dark:border-emerald-900/60' : 'border-slate-200 dark:border-slate-800' }} shadow-sm hover:border-indigo-400 transition p-5 space-y-3.5 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                        📅 {{ date('d M, Y', strtotime($demand->target_date)) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full border text-[10px] font-black {{ $demand->status_badge_class }}">
                        {{ $demand->status_label }}
                    </span>
                </div>

                <a href="{{ route('bazaar-demands.show', $demand->id) }}" class="text-sm sm:text-base font-black text-slate-900 dark:text-white hover:text-indigo-600 block leading-snug">
                    {{ $demand->title }}
                </a>

                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5 font-medium">
                    <div>👤 তৈরি করেছেন: <strong class="text-slate-800 dark:text-slate-200">{{ $demand->creator->name }}</strong></div>
                    @if($demand->assignee)
                    <div>🛵 মেসেঞ্জার: <strong class="text-brand-700 dark:text-brand-400">{{ $demand->assignee->name }}</strong></div>
                    @endif
                </div>

                <!-- Preview Items List Pills -->
                <div class="flex flex-wrap gap-1 pt-1">
                    @foreach($demand->items->take(4) as $it)
                    <span class="text-[10px] px-2 py-0.5 rounded-lg {{ $it->is_purchased ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 line-through' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $it->item_name }} ({{ $it->quantity + 0 }}{{ $it->unit }})
                    </span>
                    @endforeach
                    @if($demand->items->count() > 4)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold">
                        +{{ $demand->items->count() - 4 }} টি
                    </span>
                    @endif
                </div>

                <!-- Live Shopping Progress Bar -->
                <div class="space-y-1 pt-1">
                    <div class="flex justify-between text-[11px] font-bold">
                        <span class="text-slate-600 dark:text-slate-400">কেনাকাটার অগ্রগতি:</span>
                        <span class="{{ $demand->status === 'COMPLETED' ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $demand->purchased_count }} / {{ $demand->total_items_count }} টি সম্পন্ন ({{ $demand->progress_percent }}%)</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="{{ $demand->status === 'COMPLETED' ? 'bg-emerald-500' : 'bg-indigo-500' }} h-2 rounded-full transition-all duration-500" style="width: {{ $demand->progress_percent }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <a href="{{ route('bazaar-demands.show', $demand->id) }}" class="w-full py-2 bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 text-white rounded-xl text-xs font-bold text-center transition shadow-sm">
                    🔍 চেকলিস্ট
                </a>
                <a href="{{ route('bazaar-demands.print', $demand->id) }}" target="_blank" title="প্রিন্ট চেকলিস্ট" class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 rounded-xl text-xs">
                    🖨️
                </a>
                @if(auth()->user()->isMessenger())
                <a href="{{ route('expenses.create', ['demand_id' => $demand->id]) }}" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold text-center transition">
                    🛒 বিল এন্ট্রি
                </a>
                @endif
                @if(auth()->user()->isPrincipal() || $demand->created_by === auth()->id())
                <form action="{{ route('bazaar-demands.destroy', $demand->id) }}" method="POST" onsubmit="return confirm('এই শপিং লিস্টটি মুছে ফেলতে চান?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-700 dark:text-rose-300 rounded-xl border border-rose-200 dark:border-rose-900 text-xs">
                        🗑️
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-slate-900 p-12 rounded-3xl border border-slate-200 dark:border-slate-800 text-center text-slate-400 space-y-2">
            <span class="text-3xl block">🛍️</span>
            <div class="text-sm font-bold text-slate-600 dark:text-slate-300">কোনো শপিং লিস্ট বা হিস্ট্রি রেকর্ড পাওয়া যায়নি</div>
            <p class="text-xs text-slate-400">নতুন শপিং লিস্ট তৈরি করতে উপরের বাটনে ক্লিক করুন অথবা ফিল্টার রিসেট করুন।</p>
        </div>
        @endforelse
    </div>

    @if($demands->hasPages())
    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
        {{ $demands->links() }}
    </div>
    @endif

</div>
@endsection
