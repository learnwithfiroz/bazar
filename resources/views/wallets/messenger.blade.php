@extends('layouts.app')

@section('title', 'আমার ওয়ালেট ও ফান্ড')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Hero Card -->
    <div class="bg-slate-900 text-white p-6 rounded-3xl shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">আমার ব্যক্তিগত পেটি-ক্যাশ ফান্ড</span>
            <div class="text-3xl sm:text-4xl font-black mt-1 {{ $wallet->isLowBalance() ? 'text-rose-400' : 'text-emerald-400' }}">
                ৳ {{ number_format($wallet->current_balance, 2) }}
            </div>
            <span class="text-xs text-slate-400 mt-1 block">এলার্ট লিমিট: ৳ {{ number_format($wallet->low_balance_alert_limit, 2) }}</span>
        </div>

        @if($wallet->isLowBalance())
        <div class="bg-rose-500/20 border border-rose-500/40 p-3 rounded-2xl text-xs text-rose-200">
            ⚠️ আপনার ওয়ালেটে ব্যালেন্স কম রয়েছে। নতুন ফান্ডের জন্য রিকোয়েস্ট পাঠান।
        </div>
        @endif
    </div>

    <!-- 2 Col: Request Fund Form & Requests History -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Fund Request Form (5 cols) -->
        <div class="md:col-span-5 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span>💸</span>
                <span>নতুন ফান্ডের আবেদন</span>
            </h2>

            <form action="{{ route('fund-requests.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">টাকার পরিমাণ (৳) *</label>
                    <input type="number" step="0.01" min="1" name="amount" required placeholder="যেমন: 3000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">আবেদনের কারণ / বাজারের আইটেম</label>
                    <textarea name="reason" rows="3" placeholder="যেমন: আগামীকালকের চাল, ডাল ও তেল কেনার জন্য টাকা প্রয়োজন..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-md shadow-brand-600/20 transition">
                    আবেদন জমা দিন
                </button>
            </form>
        </div>

        <!-- Fund Requests History (7 cols) -->
        <div class="md:col-span-7 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span>📋</span>
                <span>আমার ফান্ডের আবেদনের হিস্ট্রি</span>
            </h2>

            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto pr-1">
                @forelse($fundRequests as $req)
                <div class="py-3 flex justify-between items-start text-xs">
                    <div class="space-y-0.5">
                        <div class="font-bold text-slate-900">৳ {{ number_format($req->amount, 2) }}</div>
                        <p class="text-slate-500 text-[11px]">{{ $req->reason ?: 'সাধারণ ফান্ড রিকোয়েস্ট' }}</p>
                        <span class="text-[10px] text-slate-400">{{ $req->created_at->format('d M, Y h:i A') }}</span>
                    </div>
                    <div>
                        @if($req->status === 'APPROVED')
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">অনুমোদিত</span>
                        @elseif($req->status === 'REJECTED')
                        <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded text-[10px]">বাতিল</span>
                        @else
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded text-[10px]">অপেক্ষমাণ</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-6 text-center">এখনও কোনো আবেদনের রেকর্ড নেই।</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Personal Ledger History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <span>📜</span>
            <span>আমার ওয়ালেটের সকল ট্রানজেকশন</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase">
                    <tr>
                        <th class="py-2.5 px-3">তারিখ</th>
                        <th class="py-2.5 px-3">ধরন</th>
                        <th class="py-2.5 px-3 text-right">পরিমাণ (৳)</th>
                        <th class="py-2.5 px-3 text-right">অবশিষ্ট ব্যালেন্স</th>
                        <th class="py-2.5 px-3">নোট / বিবরণ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $tx)
                    <tr>
                        <td class="py-2.5 px-3 text-slate-500 whitespace-nowrap">{{ $tx->created_at->format('d M, Y') }}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $tx->type === 'CREDIT' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $tx->type === 'CREDIT' ? 'রিচার্জ (+)' : 'খরচ (-)' }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right font-black {{ $tx->type === 'CREDIT' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $tx->type === 'CREDIT' ? '+' : '-' }} ৳ {{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-800">৳ {{ number_format($tx->balance_after, 2) }}</td>
                        <td class="py-2.5 px-3 text-slate-600">{{ $tx->notes }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-400">কোনো হিস্ট্রি নেই।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
