@extends('layouts.app')

@section('title', 'দৈনিক বাজার খরচের তালিকা')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ filterOpen: false }">
    
    <!-- Top Bar with Collapsible Filter for Mobile -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex justify-between items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-xl font-bold border border-brand-100 shadow-inner">
                    📝
                </div>
                <div>
                    <h1 class="text-base sm:text-xl font-black text-slate-900 leading-tight">বাজার খরচের তালিকা</h1>
                    <p class="text-[10px] sm:text-xs text-slate-500 font-medium">সকল বাজার এন্ট্রি ও ভাউচার পর্যালোচনার হিসেব</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Mobile Filter Toggle Button -->
                <button @click="filterOpen = !filterOpen" class="sm:hidden px-3.5 py-2 bg-slate-100 text-slate-800 text-xs font-bold rounded-xl flex items-center gap-1 border border-slate-200">
                    <span>🔍</span>
                    <span x-text="filterOpen ? 'বন্ধ' : 'ফিল্টার'"></span>
                </button>

                @if(auth()->user()->isMessenger())
                <a href="{{ route('expenses.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-xl shadow-md shadow-brand-600/25 flex items-center gap-1.5 transition active:scale-[0.98]">
                    <span>➕ নতুন এন্ট্রি</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Filter Form -->
        <div :class="filterOpen ? 'block' : 'hidden sm:block'" class="pt-3 border-t border-slate-100 transition">
            <form action="{{ route('expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 sm:gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">তারিখ</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-1 focus:ring-brand-500 bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">স্ট্যাটাস</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-1 focus:ring-brand-500 bg-slate-50/50">
                        <option value="">সকল স্ট্যাটাস</option>
                        <option value="SUBMITTED" {{ request('status') == 'SUBMITTED' ? 'selected' : '' }}>পর্যালোচনার অপেক্ষায় (Submitted)</option>
                        <option value="REVIEWED" {{ request('status') == 'REVIEWED' ? 'selected' : '' }}>অনুমোদিত / যাচাইকৃত (Reviewed)</option>
                        <option value="FLAGGED" {{ request('status') == 'FLAGGED' ? 'selected' : '' }}>সংশোধনীয় (Flagged)</option>
                    </select>
                </div>

                @if(!auth()->user()->isMessenger())
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">মেসেঞ্জার</label>
                    <select name="created_by" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:ring-1 focus:ring-brand-500 bg-slate-50/50">
                        <option value="">সকল মেসেঞ্জার</option>
                        @foreach($messengers as $m)
                        <option value="{{ $m->id }}" {{ request('created_by') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow-sm">
                        🔍 সার্চ
                    </button>
                    <a href="{{ route('expenses.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs text-center">
                        রিসেট
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 1. MOBILE CARD VIEW (< md screens) -->
    <div class="md:hidden space-y-3">
        @forelse($expenses as $exp)
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            📅 {{ date('d M, Y', strtotime($exp->expense_date)) }}
                        </span>
                        @if($exp->memo_no)
                        <span class="text-[9px] font-bold text-brand-800 bg-brand-50 border border-brand-200 px-1.5 py-0.2 rounded">মেমো: {{ $exp->memo_no }}</span>
                        @endif
                    </div>
                    <a href="{{ route('expenses.show', $exp->id) }}" class="text-sm font-bold text-slate-900 hover:text-brand-600 mt-0.5 block">
                        {{ $exp->title }}
                    </a>
                </div>
                <div class="text-right">
                    <div class="text-base font-black text-slate-900">৳ {{ number_format($exp->total_amount, 2) }}</div>
                </div>
            </div>

            <!-- Items & Slips badges -->
            <div class="flex items-center gap-2 flex-wrap text-xs text-slate-600">
                <span class="px-2 py-0.5 rounded-full border text-[10px] font-black {{ $exp->status_badge_class }}">
                    {{ $exp->status_label }}
                </span>
                <span class="text-[11px] text-slate-500">👤 {{ $exp->creator->name }}</span>
                <span class="text-[11px] text-slate-500">📦 {{ $exp->items->count() }} আইটেম</span>
                @if($exp->slips->isNotEmpty())
                <span class="text-[11px] text-brand-600 font-bold">📸 {{ $exp->slips->count() }} মেমো</span>
                @endif
            </div>

            <!-- Quick Action Buttons on Mobile Card -->
            <div class="grid {{ auth()->user()->isPrincipal() ? 'grid-cols-4' : 'grid-cols-3' }} gap-1.5 pt-2.5 border-t border-slate-100">
                <a href="{{ route('expenses.show', $exp->id) }}" class="py-2 bg-slate-900 text-white rounded-xl text-center text-xs font-bold shadow-sm">
                    🔍 ভিউ
                </a>
                <a href="{{ route('expenses.whatsapp', $exp->id) }}" target="_blank" class="py-2 bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-xl text-center text-xs font-bold flex items-center justify-center gap-1">
                    <span>💬 WA</span>
                </a>
                <a href="{{ route('reports.print-day', ['expense_id' => $exp->id]) }}" target="_blank" class="py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-center text-xs font-bold flex items-center justify-center gap-1 transition">
                    <span>🖨️ প্রিন্ট</span>
                </a>
                @if(auth()->user()->isPrincipal())
                <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('এই খরচের ডাটা ডিলিট করতে চান?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-center text-xs font-bold">
                        🗑️ মুছুন
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white p-10 rounded-3xl text-center text-slate-400 text-xs border border-slate-200">
            কোনো খরচের হিসাব পাওয়া যায়নি।
        </div>
        @endforelse
    </div>

    <!-- 2. DESKTOP TABLE VIEW (>= md screens) -->
    <div class="hidden md:block bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-5 space-y-3">
        <h2 class="text-sm font-bold text-slate-900">খরচের সম্পূর্ণ তালিকা</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-y border-slate-200 text-slate-600 uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">তারিখ</th>
                        <th class="px-4 py-3.5">বিবরণ / শিরোনাম</th>
                        <th class="px-4 py-3.5">এন্ট্রি করেছেন</th>
                        <th class="px-4 py-3.5">আইটেম / মেমো</th>
                        <th class="px-4 py-3.5 text-right">মোট টাকা (৳)</th>
                        <th class="px-4 py-3.5">স্ট্যাটাস</th>
                        <th class="px-4 py-3.5 text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-800">
                            {{ date('d-m-Y', strtotime($exp->expense_date)) }}
                        </td>
                        <td class="px-4 py-3.5">
                            <a href="{{ route('expenses.show', $exp->id) }}" class="font-bold text-slate-900 hover:text-brand-600">
                                {{ $exp->title }}
                            </a>
                            @if($exp->memo_no)
                            <span class="inline-block text-[10px] text-brand-700 bg-brand-50 border border-brand-200 px-1.5 py-0.5 rounded font-semibold ml-1">মেমো: {{ $exp->memo_no }}</span>
                            @endif
                            @if($exp->comments->isNotEmpty())
                            <span class="block text-[10px] text-purple-600 mt-0.5">💬 {{ $exp->comments->count() }} টি মন্তব্য</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-600 font-medium">
                            {{ $exp->creator->name }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-500">
                            <span>📦 {{ $exp->items->count() }} আইটেম</span>
                            @if($exp->slips->isNotEmpty())
                            <span class="ml-1.5 text-brand-600 font-bold">📸 {{ $exp->slips->count() }} মেমো</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-black text-slate-900">
                            ৳ {{ number_format($exp->total_amount, 2) }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-black {{ $exp->status_badge_class }}">
                                {{ $exp->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('expenses.show', $exp->id) }}" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                                    🔍 বিস্তারিত
                                </a>
                                <a href="{{ route('expenses.whatsapp', $exp->id) }}" target="_blank" title="WhatsApp এ পাঠান" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-xl text-xs font-bold transition">
                                    💬
                                </a>
                                <a href="{{ route('reports.print-day', ['expense_id' => $exp->id]) }}" target="_blank" title="প্রিন্ট ভাউচার" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs transition">
                                    🖨️
                                </a>
                                @if(auth()->user()->isPrincipal())
                                <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই খরচের ডাটা ডিলিট করতে চান?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="ডিলিট করুন" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition">
                                        🗑️
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">কোনো খরচের হিসাব পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($expenses->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200">
        {{ $expenses->links() }}
    </div>
    @endif

</div>
@endsection
