@extends('layouts.app')

@section('title', 'বাজার খরচ রিপোর্ট ও সামগ্রিক বাজার তালিকা')

@section('content')
<div class="space-y-5 sm:space-y-6">
    
    <!-- Top Filter Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl space-y-4 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 relative z-10">
            <div class="space-y-1">
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-300 text-[10px] font-bold border border-indigo-400/30">
                    📈 অ্যানালিটিক্স, বাজার লিস্ট ও প্রিন্ট ভাউচার
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">বাজার খরচের রিপোর্ট ও সামগ্রিক বাজার তালিকা</h1>
                <p class="text-xs text-slate-300">তারিখ রেঞ্জ অনুযায়ী কোন পণ্য কতটুকু কেনা হয়েছে এবং সর্বমোট খরচের পূর্ণাঙ্গ হিসেব</p>
            </div>

            <!-- PDF / Excel / Print Action Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 w-full sm:w-auto">
                <a href="{{ route('reports.print-range', ['start_date' => $startDate, 'end_date' => $endDate, 'messenger_id' => $messengerId]) }}" target="_blank" class="py-2.5 px-3.5 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-2xl shadow-lg shadow-brand-600/30 flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                    <span>🖨️ বাজার লিস্ট (PDF)</span>
                </a>
                <a href="{{ route('reports.export-csv', ['start_date' => $startDate, 'end_date' => $endDate, 'messenger_id' => $messengerId]) }}" class="py-2.5 px-3.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-black rounded-2xl shadow-md flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                    <span>📊 এক্সেল / CSV</span>
                </a>
                <a href="{{ route('reports.print-day', ['date' => $endDate, 'messenger_id' => $messengerId]) }}" target="_blank" class="py-2.5 px-3.5 bg-white/15 hover:bg-white/25 text-white text-xs font-bold rounded-2xl backdrop-blur-md border border-white/20 flex items-center justify-center gap-1.5 transition text-center">
                    <span>📄 দৈনিক ভাউচার</span>
                </a>
            </div>
        </div>

        <!-- Date Range & Messenger Filter Form -->
        <form action="{{ route('reports.index') }}" method="GET" class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/15 grid grid-cols-1 sm:grid-cols-12 gap-3 relative z-10">
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-300 uppercase mb-1">শুরুর তারিখ</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 rounded-xl border border-white/20 text-xs text-slate-900 font-bold focus:ring-2 focus:ring-brand-500 bg-white">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-300 uppercase mb-1">শেষ তারিখ</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 rounded-xl border border-white/20 text-xs text-slate-900 font-bold focus:ring-2 focus:ring-brand-500 bg-white">
            </div>

            @if(!auth()->user()->isMessenger())
            <div class="sm:col-span-3">
                <label class="block text-[10px] font-bold text-slate-300 uppercase mb-1">মেসেঞ্জার</label>
                <select name="messenger_id" class="w-full px-3 py-2 rounded-xl border border-white/20 text-xs text-slate-900 font-bold focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">সকল মেসেঞ্জার</option>
                    @foreach($messengers as $m)
                    <option value="{{ $m->id }}" {{ $messengerId == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="{{ auth()->user()->isMessenger() ? 'sm:col-span-6' : 'sm:col-span-3' }} flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-black rounded-xl text-xs transition shadow-md flex items-center justify-center gap-1">
                    <span>🔍 ফিল্টার করুন</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Summary KPI & Category Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6">
        
        <!-- Summary Card (4 cols) -->
        <div class="md:col-span-4 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">সিলেক্টেড পিরিয়ডের সামারি</h2>
            <div>
                <span class="text-xs text-slate-500">মোট খরচের পরিমাণ:</span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-0.5">৳ {{ number_format($totalExpense, 2) }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">{{ date('d M, Y', strtotime($startDate)) }} থেকে {{ date('d M, Y', strtotime($endDate)) }}</span>
            </div>

            <div class="pt-3 border-t border-slate-100 space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-600">মোট ভাউচারের সংখ্যা:</span>
                    <span class="font-black text-slate-900">{{ $expenses->count() }} টি</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">মোট বাজার পণ্যের ধরন:</span>
                    <span class="font-black text-emerald-700">{{ $consolidatedBazaarList->count() }} প্রকার</span>
                </div>
            </div>
        </div>

        <!-- Category Breakdown Stats (8 cols) -->
        <div class="md:col-span-8 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">ক্যাটাগরি অনুযায়ী খরচের পরিসংখ্যান</h2>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($categoryStats as $c)
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 hover:border-brand-300 transition">
                    <div class="text-xs font-bold text-slate-700">{{ $c->category }}</div>
                    <div class="text-base font-black text-slate-900 mt-0.5">৳ {{ number_format($c->total_spent, 2) }}</div>
                    <span class="text-[10px] text-slate-400 font-semibold">{{ $c->item_count }} টি আইটেম</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-6 col-span-2 sm:col-span-3 text-center">কোনো ক্যাটাগরি ডাটা নেই</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- 🌟 1. Consolidated Bazaar Items List (তারিখ রেঞ্জের মোট বাজার পণ্য ও আইটেম লিস্ট) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                    <span>🛒</span>
                    <span>তারিখ রেঞ্জের সামগ্রিক বাজার পণ্য তালিকা (Consolidated Bazaar Items)</span>
                </h2>
                <span class="text-xs text-slate-500">এই সময়কালে কোন পণ্য মোট কতটুকু কেনা হয়েছে এবং মোট কত টাকা খরচ হয়েছে</span>
            </div>

            <a href="{{ route('reports.print-range', ['start_date' => $startDate, 'end_date' => $endDate, 'messenger_id' => $messengerId]) }}" target="_blank" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <span>🖨️ বাজার লিস্ট প্রিন্ট</span>
            </a>
        </div>

        <!-- Mobile Card List for Consolidated Bazaar Items -->
        <div class="md:hidden space-y-2.5">
            @forelse($consolidatedBazaarList as $bItem)
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-bold text-slate-900 text-sm">{{ $bItem->item_name }}</div>
                        <span class="text-[10px] text-slate-500 font-semibold">{{ $bItem->category }} • কেনা হয়েছে {{ $bItem->frequency }} বার</span>
                    </div>
                    <div class="text-right">
                        <div class="font-black text-slate-900 text-sm">৳ {{ number_format($bItem->total_amount, 2) }}</div>
                        <span class="text-[10px] text-slate-500 block">গড় দর: ৳{{ number_format($bItem->avg_unit_price, 2) }}</span>
                    </div>
                </div>
                <div class="flex justify-between items-center text-[11px] pt-1.5 border-t border-slate-200 text-slate-700 font-bold">
                    <span>মোট পরিমাণ:</span>
                    <span class="text-brand-700">{{ number_format($bItem->total_quantity, 2) }} {{ $bItem->unit }}</span>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-6 text-center">কোনো বাজার আইটেম পাওয়া যায়নি।</p>
            @endforelse
        </div>

        <!-- Desktop Table for Consolidated Bazaar Items -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase">
                    <tr>
                        <th class="py-3 px-3.5">#</th>
                        <th class="py-3 px-3.5">পণ্যের নাম</th>
                        <th class="py-3 px-3.5">ক্যাটাগরি</th>
                        <th class="py-3 px-3.5 text-center">মোট পরিমাণ</th>
                        <th class="py-3 px-3.5 text-right">গড় দর (৳)</th>
                        <th class="py-3 px-3.5 text-center">ক্রয়ের সংখ্যা</th>
                        <th class="py-3 px-3.5 text-right">সর্বমোট টাকা (৳)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($consolidatedBazaarList as $idx => $bItem)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-3.5 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-3.5 font-bold text-slate-900">{{ $bItem->item_name }}</td>
                        <td class="py-3 px-3.5 text-slate-600 font-semibold">{{ $bItem->category }}</td>
                        <td class="py-3 px-3.5 text-center font-black text-brand-800">{{ number_format($bItem->total_quantity, 2) }} {{ $bItem->unit }}</td>
                        <td class="py-3 px-3.5 text-right text-slate-600">৳ {{ number_format($bItem->avg_unit_price, 2) }}</td>
                        <td class="py-3 px-3.5 text-center text-slate-500">{{ $bItem->frequency }} বার</td>
                        <td class="py-3 px-3.5 text-right font-black text-slate-900">৳ {{ number_format($bItem->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">কোনো বাজার আইটেম পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 🌟 2. Daily Vouchers Breakdown Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-5 sm:p-6 space-y-3">
        <h2 class="text-sm font-bold text-slate-900">প্রতিদিনের বাজার ভাউচার লগ</h2>

        <!-- Mobile Card List for Reports (< md) -->
        <div class="md:hidden space-y-2.5">
            @forelse($expenses as $exp)
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex justify-between items-center text-xs">
                <div>
                    <div class="font-bold text-slate-900">{{ $exp->title }}</div>
                    <div class="text-[10px] text-slate-400">{{ date('d M, Y', strtotime($exp->expense_date)) }} • {{ $exp->creator->name }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-black text-slate-900 text-sm">৳ {{ number_format($exp->total_amount, 2) }}</span>
                    <a href="{{ route('reports.print-day', ['date' => $exp->expense_date]) }}" target="_blank" class="p-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl text-xs font-bold transition">
                        🖨️
                    </a>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-8 text-center">এই সময়ে কোনো খরচ নেই।</p>
            @endforelse
        </div>

        <!-- Desktop Table for Reports (>= md) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase">
                    <tr>
                        <th class="py-3 px-3">তারিখ</th>
                        <th class="py-3 px-3">বিবরণ</th>
                        <th class="py-3 px-3">মেসেঞ্জার</th>
                        <th class="py-3 px-3 text-center">আইটেম সংখ্যা</th>
                        <th class="py-3 px-3 text-right">মোট টাকা (৳)</th>
                        <th class="py-3 px-3 text-center">প্রিন্ট ভাউচার</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-3 whitespace-nowrap font-bold text-slate-800">{{ date('d-m-Y', strtotime($exp->expense_date)) }}</td>
                        <td class="py-3 px-3 font-semibold text-slate-900">
                            {{ $exp->title }}
                            @if($exp->memo_no)
                            <span class="inline-block text-[10px] text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded font-semibold ml-1">মেমো: {{ $exp->memo_no }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $exp->creator->name }}</td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700">{{ $exp->items->count() }} টি</td>
                        <td class="py-3 px-3 text-right font-black text-slate-900">৳ {{ number_format($exp->total_amount, 2) }}</td>
                        <td class="py-3 px-3 text-center">
                            <a href="{{ route('reports.print-day', ['date' => $exp->expense_date]) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 transition">
                                <span>🖨️ স্লিপ প্রিন্ট</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">কোনো খরচের হিসাব পাওয়া যায়নি।</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
