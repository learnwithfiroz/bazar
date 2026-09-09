@extends('layouts.app')

@section('title', $demand->title . ' - শপিং চেকলিস্ট')

@section('content')
<div class="max-w-3xl mx-auto space-y-4 sm:space-y-6" x-data="shoppingDeck({{ $demand->id }}, {{ $demand->purchased_count }}, {{ $demand->total_items_count }})">
    
    <!-- Top Hero Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="space-y-1 relative z-10">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 text-indigo-300 text-[10px] font-bold border border-indigo-400/30">
                    🛍️ বাজার শপিং ডিমান্ড
                </span>
                <span class="text-xs text-slate-300">📅 {{ date('d F, Y', strtotime($demand->target_date)) }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black border {{ $demand->status_badge_class }}" x-text="statusLabel">
                    {{ $demand->status_label }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $demand->title }}</h1>
            <div class="text-xs text-slate-300 flex items-center gap-3 flex-wrap">
                <span>👤 তৈরি করেছেন: <strong>{{ $demand->creator->name }}</strong></span>
                @if($demand->assignee)
                <span>🛵 মেসেঞ্জার: <strong class="text-emerald-400">{{ $demand->assignee->name }}</strong></span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto relative z-10 flex-wrap">
            <a href="{{ route('expenses.create', ['demand_id' => $demand->id]) }}" class="w-full sm:w-auto py-2.5 px-4 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-2xl shadow-lg shadow-brand-600/30 flex items-center justify-center gap-1.5 transition active:scale-[0.98]">
                <span>🛒 খরচের বিলে রূপান্তর</span>
            </a>
        </div>
    </div>

    <!-- Live Progress Bar Card -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm space-y-2">
        <div class="flex justify-between items-center text-xs sm:text-sm font-black text-slate-800">
            <span>কেনাকাটার অগ্রগতি:</span>
            <span class="text-brand-700"><span x-text="purchasedCount"></span> / <span x-text="totalItems"></span> টি কেনা হয়েছে (<span x-text="calculatePercent()"></span>%)</span>
        </div>
        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden p-0.5 border border-slate-200">
            <div class="bg-gradient-to-r from-brand-500 to-emerald-500 h-2 rounded-full transition-all duration-300" :style="`width: ${calculatePercent()}%`"></div>
        </div>
    </div>

    <!-- Interactive Checklist Items Deck -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden p-5 sm:p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                    <span>📋</span>
                    <span>বাজার পণ্যের চেকলিস্ট</span>
                </h2>
                <span class="text-[11px] text-slate-500">পণ্য কেনা হলে বক্সে টিক চিহ্ন দিন</span>
            </div>
            <span class="text-xs font-bold text-slate-400">মোট {{ $demand->items->count() }} আইটেম</span>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach($demand->items as $item)
            <div class="py-3.5 flex items-center justify-between gap-3 select-none hover:bg-slate-50/80 px-2 rounded-2xl transition"
                 :class="itemStates[{{ $item->id }}] ? 'opacity-60 bg-slate-50/50' : ''">
                
                <label class="flex items-center gap-3.5 cursor-pointer flex-grow">
                    <input type="checkbox" 
                           :checked="itemStates[{{ $item->id }}]"
                           @change="toggleItem({{ $item->id }})"
                           class="w-5 h-5 rounded-lg text-brand-600 focus:ring-brand-500 cursor-pointer">
                    
                    <div>
                        <span class="text-xs sm:text-sm font-black text-slate-900 block"
                              :class="itemStates[{{ $item->id }}] ? 'line-through text-slate-400' : ''">
                            {{ $item->item_name }}
                        </span>
                        <span class="text-[11px] text-slate-500 font-semibold">
                            {{ $item->category }} • পরিমাণ: <strong class="text-slate-800">{{ $item->quantity }} {{ $item->unit }}</strong>
                        </span>
                    </div>
                </label>

                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border transition"
                          :class="itemStates[{{ $item->id }}] ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-600 border-slate-200'">
                        <span x-text="itemStates[{{ $item->id }}] ? '✓ কেনা শেষ' : 'বাকি আছে'"></span>
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        @if($demand->notes)
        <div class="p-4 bg-amber-50/80 rounded-2xl border border-amber-200 text-xs text-amber-900 space-y-1">
            <span class="font-black uppercase tracking-wider block text-[10px]">বাসা থেকে বিশেষ নোট:</span>
            <p>{{ $demand->notes }}</p>
        </div>
        @endif
    </div>

    <!-- 1-Click Convert to Expense Button Bar -->
    <div class="bg-gradient-to-r from-emerald-500/10 via-emerald-50 to-teal-50 border border-emerald-300 p-5 rounded-3xl flex flex-col sm:flex-row justify-between items-center gap-3 shadow-sm">
        <div>
            <span class="text-xs sm:text-sm font-black text-emerald-950 block">কেনাকাটা শেষে সরাসরি খরচের বিল এন্ট্রি করুন</span>
            <span class="text-[11px] text-emerald-800">লিস্টের পণ্যগুলোর নাম ও পরিমাণ স্বয়ংক্রিয়ভাবে খরচ ফর্মে যুক্ত হয়ে যাবে</span>
        </div>
        <a href="{{ route('expenses.create', ['demand_id' => $demand->id]) }}" class="w-full sm:w-auto py-3 px-6 bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-black rounded-2xl shadow-md shadow-brand-600/30 flex items-center justify-center gap-1.5 transition active:scale-95 whitespace-nowrap">
            <span>🛒 খরচের বিলে কনভার্ট করুন ➔</span>
        </a>
    </div>

</div>

@push('scripts')
<script>
function shoppingDeck(demandId, initialPurchased, total) {
    return {
        demandId: demandId,
        purchasedCount: initialPurchased,
        totalItems: total,
        statusLabel: '{{ $demand->status_label }}',
        itemStates: {
            @foreach($demand->items as $it)
            {{ $it->id }}: {{ $it->is_purchased ? 'true' : 'false' }},
            @endforeach
        },
        calculatePercent() {
            if (this.totalItems === 0) return 0;
            return Math.round((this.purchasedCount / this.totalItems) * 100);
        },
        toggleItem(itemId) {
            this.itemStates[itemId] = !this.itemStates[itemId];
            if (this.itemStates[itemId]) {
                this.purchasedCount++;
            } else {
                this.purchasedCount--;
            }

            fetch(`/bazaar-demand-items/${itemId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.statusLabel = data.status_label;
            })
            .catch(err => console.error(err));
        }
    }
}
</script>
@endpush
@endsection
