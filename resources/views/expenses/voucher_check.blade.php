@extends('layouts.app')

@section('title', 'ভাউচার ভিত্তিক বিল ও মেমো যাচাই - Voucher Wise Check')

@section('content')
<div class="space-y-5 sm:space-y-6" x-data="{ 
    fullImageModal: false, 
    activeImageUrl: '',
    openImage(url) {
        this.activeImageUrl = url;
        this.fullImageModal = true;
    }
}">
    
    <!-- Top Hero Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-300 text-[10px] font-bold border border-indigo-400/30">
                    🔎 ভাউচার ও মেমো অডিট
                </span>
                <span class="text-xs text-slate-300">📅 {{ date('d F, Y') }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">ভাউচার ভিত্তিক বিল ও মেমো যাচাই (Voucher Check)</h1>
            <p class="text-xs text-slate-300">ক্যাশ মেমোর ছবির সাথে আইটেম দর ও পরিমাণের সামঞ্জস্য পরীক্ষা এবং অনুমোদন</p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto relative z-10">
            <a href="{{ route('reports.index') }}" class="w-full sm:w-auto py-2.5 px-4 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-2xl backdrop-blur-md border border-white/20 flex items-center justify-center gap-1.5 transition text-center">
                <span>📈 সামগ্রিক রিপোর্ট</span>
            </a>
        </div>
    </div>

    <!-- 4 High-Level Audit Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Pending Verification -->
        <a href="{{ route('expenses.voucher-check', ['status' => 'SUBMITTED']) }}" class="bg-white p-4 sm:p-5 rounded-3xl border {{ request('status') === 'SUBMITTED' ? 'border-amber-400 ring-2 ring-amber-200' : 'border-slate-200/80' }} shadow-sm hover:shadow-md transition space-y-1 block">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">যাচাইয়ের অপেক্ষায়</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 font-black text-sm flex items-center justify-center">⏳</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-900">{{ $pendingCount }} টি ভাউচার</div>
            <span class="text-[10px] text-slate-400 block">মেমো ও দর চেক প্রয়োজন</span>
        </a>

        <!-- Approved / Verified -->
        <a href="{{ route('expenses.voucher-check', ['status' => 'REVIEWED']) }}" class="bg-white p-4 sm:p-5 rounded-3xl border {{ request('status') === 'REVIEWED' ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-slate-200/80' }} shadow-sm hover:shadow-md transition space-y-1 block">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">অনুমোদিত ও যাচাইকৃত</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 font-black text-sm flex items-center justify-center">✅</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700">{{ $reviewedCount }} টি ভাউচার</div>
            <span class="text-[10px] text-slate-400 block">সম্পূর্ণ অডিট সম্পন্ন</span>
        </a>

        <!-- Flagged for Correction -->
        <a href="{{ route('expenses.voucher-check', ['status' => 'FLAGGED']) }}" class="bg-white p-4 sm:p-5 rounded-3xl border {{ request('status') === 'FLAGGED' ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-200/80' }} shadow-sm hover:shadow-md transition space-y-1 block">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">সংশোধন প্রয়োজন</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 font-black text-sm flex items-center justify-center">🚩</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-600">{{ $flaggedCount }} টি ভাউচার</div>
            <span class="text-[10px] text-slate-400 block">মেসেঞ্জারের উত্তর প্রয়োজন</span>
        </a>

        <!-- Total Approved Spending -->
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">যাচাইকৃত মোট ব্যয়</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 font-black text-sm flex items-center justify-center">💳</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">৳ {{ number_format($totalAmountReviewed, 2) }}</div>
            <span class="text-[10px] text-slate-400 block">অনুমোদিত অডিট ব্যালেন্স</span>
        </div>

    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
        <form action="{{ route('expenses.voucher-check') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            
            <div class="sm:col-span-4">
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">ভাউচার আইডি / মেমো নং / শিরোনাম সার্চ</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="যেমন: 102 বা MEMO-109..." class="w-full pl-8 pr-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                </div>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">যাচাইয়ের স্ট্যাটাস</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>⏳ পর্যালোচনার অপেক্ষায় (Pending)</option>
                    <option value="REVIEWED" {{ request('status') === 'REVIEWED' ? 'selected' : '' }}>✅ অনুমোদিত / যাচাইকৃত (Reviewed)</option>
                    <option value="FLAGGED" {{ request('status') === 'FLAGGED' ? 'selected' : '' }}>🚩 সংশোধন প্রয়োজন (Flagged)</option>
                </select>
            </div>

            @if(!auth()->user()->isMessenger())
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">বাজারকারী মেসেঞ্জার</label>
                <select name="created_by" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="">সকল মেসেঞ্জার</option>
                    @foreach($messengers as $m)
                    <option value="{{ $m->id }}" {{ request('created_by') == $m->id ? 'selected' : '' }}>🛵 {{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="{{ auth()->user()->isMessenger() ? 'sm:col-span-5' : 'sm:col-span-2' }} flex gap-1.5">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    সার্চ
                </button>
                <a href="{{ route('expenses.voucher-check') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs text-center">
                    রিসেট
                </a>
            </div>

        </form>
    </div>

    <!-- Voucher Wise Inspection Deck -->
    <div class="space-y-6">
        @forelse($expenses as $expense)
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden p-5 sm:p-6 space-y-5 hover:border-brand-300 transition">
            
            <!-- Voucher Header Banner -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-900 text-white text-xs font-black">
                            ভাউচার #{{ $expense->id }}
                        </span>
                        @if($expense->memo_no)
                        <span class="px-2.5 py-0.5 rounded-full bg-brand-50 border border-brand-200 text-brand-800 text-xs font-black">
                            ক্যাশ মেমো: {{ $expense->memo_no }}
                        </span>
                        @endif
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black border {{ $expense->status_badge_class }}">
                            {{ $expense->status_label }}
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900">{{ $expense->title }}</h2>
                    <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                        <span>📅 তারিখ: <strong>{{ date('d F, Y', strtotime($expense->expense_date)) }}</strong></span>
                        <span>👤 বাজার করেছেন: <strong class="text-slate-800">{{ $expense->creator->name }}</strong></span>
                        <span>📦 মোট আইটেম: <strong>{{ $expense->items->count() }} টি</strong></span>
                    </div>
                </div>

                <!-- Total Amount & Quick Print -->
                <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">ভাউচারের মোট টাকা:</span>
                        <span class="text-xl sm:text-2xl font-black text-slate-900">৳ {{ number_format($expense->total_amount, 2) }}</span>
                    </div>
                    <a href="{{ route('reports.print-day', ['expense_id' => $expense->id]) }}" target="_blank" title="প্রিন্ট স্লিপ" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition flex items-center gap-1">
                        <span>🖨️</span>
                        <span class="hidden sm:inline">ভাউচার স্লিপ</span>
                    </a>
                </div>
            </div>

            <!-- Side-by-Side Verification Grid: Left Memo Image / Right Itemized Table -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                
                <!-- Left: Cash Memo Slip Photo Preview (4 cols) -->
                <div class="lg:col-span-4 space-y-2">
                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider block flex items-center gap-1.5">
                        <span>📸</span>
                        <span>সংযুক্ত ক্যাশ মেমো ({{ $expense->slips->count() }} টি)</span>
                    </span>

                    @if($expense->slips->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach($expense->slips as $slip)
                        <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm hover:border-brand-400 transition">
                            <img src="{{ asset('storage/' . $slip->image_path) }}" 
                                 alt="Cash Memo"
                                 @click="openImage('{{ asset('storage/' . $slip->image_path) }}')"
                                 class="w-full h-48 sm:h-56 object-contain bg-white cursor-zoom-in group-hover:opacity-95 transition">
                            <div class="p-2 bg-slate-900 text-white text-[10px] flex justify-between items-center">
                                <span class="truncate max-w-[150px]">{{ $slip->original_name ?: 'Slip Memo' }}</span>
                                <button type="button" @click="openImage('{{ asset('storage/' . $slip->image_path) }}')" class="text-brand-400 font-bold hover:underline">
                                    🔍 বড় করে দেখুন
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                        <span class="text-2xl block mb-1">📄</span>
                        <p class="text-xs text-slate-500">কোনো মেমোর ছবি সংযুক্ত নেই</p>
                    </div>
                    @endif
                </div>

                <!-- Right: Itemized Table with Calculations (8 cols) -->
                <div class="lg:col-span-8 space-y-3">
                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider block flex items-center gap-1.5">
                        <span>📋</span>
                        <span>বাজার পণ্যের দর ও পরিমাণের বিবরণ</span>
                    </span>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase">
                                <tr>
                                    <th class="py-2.5 px-3">#</th>
                                    <th class="py-2.5 px-3">পণ্যের নাম</th>
                                    <th class="py-2.5 px-3">ক্যাটাগরি</th>
                                    <th class="py-2.5 px-3 text-center">পরিমাণ</th>
                                    <th class="py-2.5 px-3 text-right">একক দর (৳)</th>
                                    <th class="py-2.5 px-3 text-right">মোট টাকা (৳)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($expense->items as $iIdx => $item)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-2.5 px-3 text-slate-400">{{ $iIdx + 1 }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900">{{ $item->item_name }}</td>
                                    <td class="py-2.5 px-3 text-slate-500">{{ $item->category }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-800">{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td class="py-2.5 px-3 text-right text-slate-600">৳ {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-2.5 px-3 text-right font-black text-slate-900">৳ {{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                                <tr class="bg-slate-50 font-black">
                                    <td colspan="5" class="py-2.5 px-3 text-right text-slate-700">ভাউচার সাবটোটাল:</td>
                                    <td class="py-2.5 px-3 text-right text-brand-800 text-sm">৳ {{ number_format($expense->total_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($expense->notes)
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                        <span class="font-bold">মেসেঞ্জারের নোট:</span> {{ $expense->notes }}
                    </div>
                    @endif
                </div>

            </div>

            <!-- Verification Action Bar & Auditor Remarks -->
            @if(auth()->user()->canManageFunds() || auth()->user()->isPrincipal())
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="space-y-0.5">
                        <span class="text-xs font-black text-slate-800 uppercase tracking-wider block">ভাউচার যাচাই ও অনুমোদন একশন</span>
                        <span class="text-[11px] text-slate-500">মেমোর সাথে সঠিক থাকলে অনুমোদন দিন অথবা সংশোধনের জন্য ফ্ল্যাগ করুন</span>
                    </div>

                    <div class="grid grid-cols-2 sm:flex items-center gap-2 w-full sm:w-auto">
                        <!-- Approve Action -->
                        <form action="{{ route('expenses.status.update', $expense->id) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="REVIEWED">
                            <button type="submit" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-1">
                                <span>✅ অনুমোদন (Approve)</span>
                            </button>
                        </form>

                        <!-- Flag Action -->
                        <form action="{{ route('expenses.status.update', $expense->id) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="FLAGGED">
                            <button type="submit" class="w-full py-2 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1">
                                <span>🚩 ফ্ল্যাগ (Flag)</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Discussion & Remarks Form -->
                <form action="{{ route('expenses.comments.store', $expense->id) }}" method="POST" class="pt-2 border-t border-slate-200/80 flex items-center gap-2">
                    @csrf
                    <input type="text" name="message" required placeholder="এই ভাউচার নিয়ে কোনো পর্যবেক্ষণ বা নোট লিখুন..." class="flex-grow px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 bg-white">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl whitespace-nowrap shadow-sm transition">
                        নোট যোগ করুন
                    </button>
                </form>
            </div>
            @endif

        </div>
        @empty
        <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-slate-400 space-y-2">
            <span class="text-3xl block">📋</span>
            <div class="text-sm font-bold text-slate-600">কোনো ভাউচার পাওয়া যায়নি</div>
            <p class="text-xs text-slate-400">ফিল্টারের তথ্য পরিবর্তন করে আবার চেষ্টা করুন।</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($expenses->hasPages())
    <div class="p-4 bg-white rounded-3xl border border-slate-200">
        {{ $expenses->links() }}
    </div>
    @endif

    <!-- Image Lightbox Modal with Full-Screen Touch Zoom -->
    <div x-show="fullImageModal" x-cloak class="fixed inset-0 bg-black/95 backdrop-blur-md z-50 flex items-center justify-center p-2 sm:p-4">
        <div @click.away="fullImageModal = false" class="relative max-w-4xl max-h-[92vh] w-full flex flex-col items-center">
            <button @click="fullImageModal = false" class="absolute -top-8 right-2 text-white text-3xl font-bold hover:text-slate-300">&times;</button>
            <img :src="activeImageUrl" class="max-h-[85vh] max-w-full object-contain rounded-2xl shadow-2xl">
        </div>
    </div>

</div>
@endsection
