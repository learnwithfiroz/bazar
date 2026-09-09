@extends('layouts.app')

@section('title', 'ওয়ালেট ও ফান্ড ম্যানেজমেন্ট')

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ topUpModal: false, selectedWalletId: '{{ $wallets->first()->id ?? '' }}', selectedMessengerName: '{{ $wallets->first()->user->name ?? '' }}' }">
    
    <!-- Top Header -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center text-xl font-bold border border-brand-100 shadow-inner">
                💳
            </div>
            <div>
                <h1 class="text-base sm:text-xl font-black text-slate-900 leading-tight">মেসেঞ্জার ওয়ালেট ও ফান্ড কন্ট্রোল</h1>
                <p class="text-[10px] sm:text-xs text-slate-500 font-medium">লাইভ ব্যালেন্স, রিচার্জ হিস্ট্রি ও ফান্ড রিকোয়েস্ট অনুমোদন</p>
            </div>
        </div>

        @if(auth()->user()->canManageFunds())
        <button @click="topUpModal = true" class="w-full sm:w-auto py-2.5 px-4 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-xl shadow-md shadow-brand-600/25 flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
            <span>➕ ফান্ড রিচার্জ করুন</span>
        </button>
        @endif
    </div>

    <!-- Messengers Wallet Live Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        @forelse($wallets as $w)
        <div class="bg-white p-5 rounded-3xl border {{ $w->isLowBalance() ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200/90' }} shadow-sm space-y-3 relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-500 text-white flex items-center justify-center text-sm font-black shadow-inner">
                        🛵
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 leading-tight">{{ $w->user->name ?? 'মেসেঞ্জার' }}</h2>
                        <span class="text-[10px] text-slate-400 font-bold block">{{ $w->user->phone_number }}</span>
                    </div>
                </div>
                
                @if($w->isLowBalance())
                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-black border border-rose-200">
                    ⚠️ রিচার্জ প্রয়োজন
                </span>
                @else
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black border border-emerald-200">
                    পর্যাপ্ত ফান্ড
                </span>
                @endif
            </div>

            <!-- Current Balance Big Banner -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500">বর্তমান ব্যালেন্স:</span>
                <span class="text-xl sm:text-2xl font-black {{ $w->isLowBalance() ? 'text-rose-600' : 'text-slate-900' }}">
                    ৳ {{ number_format($w->current_balance, 2) }}
                </span>
            </div>

            @if(auth()->user()->canManageFunds())
            <button @click="selectedWalletId = '{{ $w->id }}'; selectedMessengerName = '{{ $w->user->name }}'; topUpModal = true" class="w-full py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-800 border border-brand-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                <span>➕ এই ওয়ালেটে রিচার্জ করুন</span>
            </button>
            @endif
        </div>
        @empty
        <div class="col-span-full bg-white p-8 rounded-3xl border border-slate-200 text-center text-slate-400 text-xs">
            কোনো মেসেঞ্জার ওয়ালেট পাওয়া যায়নি।
        </div>
        @endforelse
    </div>

    <!-- Pending Fund Requests Deck -->
    @if($pendingRequests->isNotEmpty())
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border border-amber-200 rounded-3xl p-5 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-amber-950 flex items-center gap-2">
                <span>⚠️</span>
                <span>জরুরি ফান্ড রিকোয়েস্ট (অনুমোদনের অপেক্ষায়)</span>
            </h2>
            <span class="px-2.5 py-0.5 bg-amber-200 text-amber-900 rounded-full text-xs font-black">{{ $pendingRequests->count() }} টি আবেদন</span>
        </div>

        <div class="space-y-2.5">
            @foreach($pendingRequests as $req)
            <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-900">{{ $req->requester->name }}</span>
                        <span class="px-2.5 py-0.5 bg-amber-100 text-amber-900 text-xs font-black rounded-full">টাকার পরিমাণ: ৳ {{ number_format($req->amount, 2) }}</span>
                    </div>
                    <p class="text-xs text-slate-600">{{ $req->reason ?: 'বাজার খরচের জন্য অতিরিক্ত ফান্ড প্রয়োজন।' }}</p>
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
                        <button type="submit" class="w-full py-2 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-xl">
                            ❌ বাতিল
                        </button>
                    </form>
                    @if(auth()->user()->isPrincipal())
                    <form action="{{ route('fund-requests.destroy', $req->id) }}" method="POST" onsubmit="return confirm('এই ফান্ড রিকোয়েস্টটি ডিলিট করতে চান?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="ডিলিট" class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs">
                            🗑️
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Transaction Ledger History with 1-by-1 Delete for Super Admin -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-5 space-y-3">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span>📜</span>
                <span>ওয়ালেট লেজার ও ট্রানজেকশন হিস্ট্রি</span>
            </h2>
            <span class="text-xs text-slate-400">সর্বশেষ ৩০টি রেকর্ড</span>
        </div>

        <!-- Mobile Card List for Transactions (< md) -->
        <div class="md:hidden space-y-2.5">
            @forelse($recentTransactions as $tx)
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-bold text-slate-900">{{ $tx->wallet->user->name ?? 'N/A' }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ $tx->created_at->format('d M, h:i A') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-black {{ $tx->type === 'CREDIT' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $tx->type === 'CREDIT' ? '+' : '-' }} ৳ {{ number_format($tx->amount, 2) }}
                        </span>
                        <span class="text-[10px] text-slate-500 block">অবশিষ্ট: ৳{{ number_format($tx->balance_after, 2) }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center text-[11px] pt-1.5 border-t border-slate-200 text-slate-600">
                    <span class="truncate max-w-[180px]">{{ $tx->notes ?: 'সাধারণ ট্রানজেকশন' }}</span>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $tx->type === 'CREDIT' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $tx->type === 'CREDIT' ? 'রিচার্জ' : 'খরচ' }}
                        </span>
                        @if(auth()->user()->isPrincipal())
                        <form action="{{ route('wallet-transactions.destroy', $tx->id) }}" method="POST" onsubmit="return confirm('এই ট্রানজেকশন রেকর্ডটি ডিলিট করতে চান? ওয়ালেট ব্যালেন্স সমন্বয় হবে।');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="ডিলিট" class="p-1 bg-rose-50 text-rose-700 rounded-lg border border-rose-200 text-[10px]">
                                🗑️
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-6 text-center">কোনো ট্রানজেকশন নেই।</p>
            @endforelse
        </div>

        <!-- Desktop Table for Transactions (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase">
                    <tr>
                        <th class="py-3 px-3">তারিখ ও সময়</th>
                        <th class="py-3 px-3">মেসেঞ্জার</th>
                        <th class="py-3 px-3">ধরন</th>
                        <th class="py-3 px-3 text-right">পরিমাণ (৳)</th>
                        <th class="py-3 px-3 text-right">আগের ব্যালেন্স</th>
                        <th class="py-3 px-3 text-right">পরের ব্যালেন্স</th>
                        <th class="py-3 px-3">বিবরণ / নোট</th>
                        @if(auth()->user()->isPrincipal())
                        <th class="py-3 px-3 text-center">অ্যাকশন</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $tx)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-3 whitespace-nowrap text-slate-500 font-medium">
                            {{ $tx->created_at->format('d-m-Y h:i A') }}
                        </td>
                        <td class="py-3 px-3 font-bold text-slate-900">
                            {{ $tx->wallet->user->name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $tx->type === 'CREDIT' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $tx->type === 'CREDIT' ? '➕ রিচার্জ' : '➖ বাজার খরচ' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right font-black {{ $tx->type === 'CREDIT' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $tx->type === 'CREDIT' ? '+' : '-' }} ৳ {{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-500">৳ {{ number_format($tx->balance_before, 2) }}</td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900">৳ {{ number_format($tx->balance_after, 2) }}</td>
                        <td class="py-3 px-3 text-slate-600 font-medium">{{ $tx->notes ?: 'N/A' }}</td>
                        @if(auth()->user()->isPrincipal())
                        <td class="py-3 px-3 text-center">
                            <form action="{{ route('wallet-transactions.destroy', $tx->id) }}" method="POST" onsubmit="return confirm('এই ট্রানজেকশন রেকর্ডটি ডিলিট করতে চান? সংশ্লিষ্ট মেসেঞ্জারের ওয়ালেট ব্যালেন্স সমন্বয় করা হবে।');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="ডিলিট করুন" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs transition">
                                    🗑️
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isPrincipal() ? '8' : '7' }}" class="py-6 text-center text-slate-400">কোনো ট্রানজেকশন রেকর্ড নেই।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top-Up Wallet Modal -->
    <div x-show="topUpModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="topUpModal = false" class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 p-5 sm:p-6 space-y-4">
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>➕</span>
                    <span>ফান্ড রিচার্জ (মেসেঞ্জার ওয়ালেট)</span>
                </h3>
                <button @click="topUpModal = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <form action="{{ route('wallets.topup') }}" method="POST" class="space-y-3.5">
                @csrf
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">মেসেঞ্জারের নাম নির্বাচন করুন *</label>
                    <select name="wallet_id" x-model="selectedWalletId" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                        @foreach($wallets as $w)
                        <option value="{{ $w->id }}">🛵 {{ $w->user->name ?? 'মেসেঞ্জার' }} (বর্তমান ব্যালেন্স: ৳ {{ number_format($w->current_balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">টাকার পরিমাণ (৳) *</label>
                    <input type="number" inputmode="decimal" step="0.01" min="1" name="amount" required placeholder="যেমন: 5000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-black focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">মন্তব্য / নোট (ঐচ্ছিক)</label>
                    <input type="text" name="notes" placeholder="যেমন: সাপ্তাহিক বাজার ফান্ড বা ক্যাশ রিচার্জ" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="topUpModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">বাতিল</button>
                    <button type="submit" class="py-2.5 px-5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-md">টাকা রিচার্জ করুন</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
