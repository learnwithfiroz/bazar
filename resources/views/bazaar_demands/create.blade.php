@extends('layouts.app')

@section('title', 'নতুন বাজার শপিং লিস্ট তৈরি')

@section('content')
<div class="max-w-3xl mx-auto space-y-4 sm:space-y-6" x-data="demandForm()">
    
    <!-- Top Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white p-5 sm:p-6 rounded-3xl shadow-xl flex justify-between items-center gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white/10 text-indigo-300 border border-white/20 flex items-center justify-center text-2xl font-black">
                🛍️
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-white leading-tight">বাজারের ডিমান্ড / শপিং লিস্ট তৈরি</h1>
                <p class="text-xs text-slate-300">বাসা থেকে প্রয়োজনীয় পণ্যের তালিকা ও আনুমানিক পরিমাণ লিখুন</p>
            </div>
        </div>

        <a href="{{ route('bazaar-demands.index') }}" class="py-2 px-3.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/20 transition">
            তালিকা ➔
        </a>
    </div>

    <!-- Main Form -->
    <form action="{{ route('bazaar-demands.store') }}" method="POST" class="space-y-4 sm:space-y-6">
        @csrf

        <!-- Basic Info Card -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                <span class="text-base">📌</span>
                <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">তালিকার প্রাথমিক তথ্য</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">লিস্টের শিরোনাম *</label>
                    <input type="text" name="title" value="আজকের বাজার চাহিদা" required placeholder="যেমন: সকালের কাঁচাবাজার" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">বাজারের তারিখ *</label>
                    <input type="date" name="target_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">নির্দিষ্ট মেসেঞ্জার (ঐচ্ছিক)</label>
                    <select name="assigned_to" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                        <option value="">সকল মেসেঞ্জার (উন্মুক্ত)</option>
                        @foreach($messengers as $m)
                        <option value="{{ $m->id }}">🛵 {{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 1-Tap Auto-Suggest Quick Add Chips -->
        @if(!empty($suggestedItems) && count($suggestedItems) > 0)
        <div class="bg-gradient-to-r from-indigo-50/80 to-purple-50/80 dark:from-slate-800/80 dark:to-indigo-950/40 p-4 rounded-3xl border border-indigo-100 dark:border-indigo-900/60 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="font-black text-indigo-950 dark:text-indigo-200 flex items-center gap-1.5">
                    <span>💡</span>
                    <span>পূর্ববর্তী শপিং আইটেম সাজেশন্স (১-ট্যাপে যোগ করুন):</span>
                </span>
                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">অটো-ফিল ক্যাটাগরি ও ইউনিট</span>
            </div>
            <div class="flex flex-wrap gap-1.5 pt-1 max-h-24 overflow-y-auto">
                @foreach($suggestedItems->take(18) as $sItem)
                <button type="button" @click="quickAddItem('{{ addslashes($sItem->item_name) }}', '{{ addslashes($sItem->category) }}', '{{ addslashes($sItem->unit) }}')" class="px-3 py-1 bg-white dark:bg-slate-800 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold border border-indigo-200/80 dark:border-indigo-800 shadow-sm transition active:scale-95 flex items-center gap-1">
                    <span>+</span>
                    <span>{{ $sItem->item_name }}</span>
                    <span class="text-[10px] opacity-60">({{ $sItem->unit }})</span>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Dynamic Items Repeater Card -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📋</span>
                        <span>প্রয়োজনীয় পণ্যের তালিকা</span>
                    </h2>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">পণ্যের নাম লিখলেই ক্যাটাগরি অটোমেটিক সাজেস্ট হবে</span>
                </div>
                <button type="button" @click="addItem()" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-black rounded-xl shadow-md shadow-brand-600/20 flex items-center gap-1 transition">
                    <span>➕ আইটেম</span>
                </button>
            </div>

            <!-- Datalist for live suggestions -->
            <datalist id="demand-items-list">
                @if(!empty($suggestedItems))
                    @foreach($suggestedItems as $sItem)
                    <option value="{{ $sItem->item_name }}">{{ $sItem->category }} ({{ $sItem->unit }})</option>
                    @endforeach
                @endif
                <option value="আলু (Potato)">কাঁচাবাজার</option>
                <option value="পিঁয়াজ (Onion)">কাঁচাবাজার</option>
                <option value="রসুন (Garlic)">কাঁচাবাজার</option>
                <option value="আদা (Ginger)">কাঁচাবাজার</option>
                <option value="কাঁচামরিচ (Green Chili)">কাঁচাবাজার</option>
                <option value="টমেটো (Tomato)">কাঁচাবাজার</option>
                <option value="রুই মাছ (Rui Fish)">মাছ ও মাংস</option>
                <option value="কাতল মাছ (Katla Fish)">মাছ ও মাংস</option>
                <option value="ইলিশ মাছ (Hilsa)">মাছ ও মাংস</option>
                <option value="গরুর মাংস (Beef)">মাছ ও মাংস</option>
                <option value="মুরগির মাংস (Chicken)">মাছ ও মাংস</option>
                <option value="ডিম (Egg)">মুদিখানা</option>
                <option value="সয়াবিন তেল (Soybean Oil)">মুদিখানা</option>
                <option value="মসুর ডাল (Lentil)">মুদিখানা</option>
            </datalist>

            <!-- Items Repeater Cards -->
            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 space-y-3 relative hover:border-brand-300 transition">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-black" x-text="index + 1"></span>
                                <span>আইটেম বিবরণ</span>
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="startVoiceRecognition(index)" title="মুখে বলে পণ্যের নাম লিখুন" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg border border-indigo-200 text-xs font-bold flex items-center gap-1">
                                    <span>🎙️</span>
                                    <span class="hidden sm:inline">ভয়েস</span>
                                </button>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1 bg-rose-50 rounded-lg border border-rose-100">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-12 gap-2.5 sm:gap-3">
                            <div class="col-span-2 sm:col-span-5">
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">পণ্যের নাম *</label>
                                <input type="text" :name="`items[${index}][name]`" list="demand-items-list" x-model="item.name" @input="onItemNameInput(index)" required placeholder="যেমন: ইলিশ মাছ / আলু / শসা" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white">
                            </div>

                            <div class="col-span-2 sm:col-span-3">
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">ক্যাটাগরি</label>
                                <select :name="`items[${index}][category]`" x-model="item.category" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white">
                                    @foreach($defaultCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">পরিমাণ *</label>
                                <input type="number" inputmode="decimal" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required placeholder="২" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-black focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white text-center">
                            </div>

                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">একক</label>
                                <select :name="`items[${index}][unit]`" x-model="item.unit" class="w-full px-2 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white">
                                    <option value="কেজি">কেজি</option>
                                    <option value="গ্রাম">গ্রাম</option>
                                    <option value="লিটার">লিটার</option>
                                    <option value="আঁটি">আঁটি</option>
                                    <option value="পিস">পিস</option>
                                    <option value="প্যাকেট">প্যাকেট</option>
                                    <option value="ডজন">ডজন</option>
                                    <option value="হালি">হালি</option>
                                    <option value="বস্তা">বস্তা</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="addItem()" class="w-full py-3.5 bg-slate-100/80 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center gap-1.5 transition">
                <span>➕ আরও পণ্য যোগ করুন</span>
            </button>
        </div>

        <!-- Notes -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">বিশেষ নির্দেশনা / নোট (ঐচ্ছিক)</label>
            <textarea name="notes" rows="2" placeholder="যেমন: তাজা দেখে আনবেন বা নির্দিষ্ট দোকান থেকে কিনবেন..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white"></textarea>
        </div>

        <button type="submit" class="w-full py-4 bg-gradient-to-r from-brand-600 via-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white font-black rounded-2xl text-sm sm:text-base shadow-xl shadow-brand-600/30 flex items-center justify-center gap-2 transition active:scale-[0.99]">
            <span>💾 শপিং লিস্ট সেভ করুন ও WhatsApp এ পাঠান</span>
            <span>➔</span>
        </button>

    </form>
</div>

@push('scripts')
<script>
function demandForm() {
    return {
        items: [
            { name: '', category: 'কাঁচাবাজার', quantity: 1, unit: 'কেজি' }
        ],
        suggestedItemsDb: {!! json_encode(!empty($suggestedItems) ? $suggestedItems : [], JSON_UNESCAPED_UNICODE) !!},
        addItem() {
            this.items.push({ name: '', category: 'কাঁচাবাজার', quantity: 1, unit: 'কেজি' });
        },
        quickAddItem(name, category, unit) {
            if (this.items.length === 1 && !this.items[0].name) {
                this.items[0] = { name: name, category: category || 'কাঁচাবাজার', quantity: 1, unit: unit || 'কেজি' };
            } else {
                this.items.push({ name: name, category: category || 'কাঁচাবাজার', quantity: 1, unit: unit || 'কেজি' });
            }
        },
        onItemNameInput(index) {
            const currentName = (this.items[index].name || '').trim().toLowerCase();
            if (!currentName) return;

            const match = this.suggestedItemsDb.find(s => s.item_name.toLowerCase() === currentName);
            if (match) {
                if (match.category) this.items[index].category = match.category;
                if (match.unit) this.items[index].unit = match.unit;
            }
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        startVoiceRecognition(index) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert('আপনার ব্রাউজারে ভয়েস রিকগনিশন সাপোর্ট করে না। দয়া করে গুগল ক্রোম ব্যবহার করুন।');
                return;
            }
            const recognition = new SpeechRecognition();
            recognition.lang = 'bn-BD';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            recognition.onstart = () => {
                alert('🎙️ মাইকে বাংলায় বলুন (যেমন: ২ কেজি আলু বা রুই মাছ)...');
            };

            recognition.onresult = (event) => {
                const text = event.results[0][0].transcript;
                this.items[index].name = text;
                this.onItemNameInput(index);
            };

            recognition.onerror = (event) => {
                console.error('Speech error: ', event.error);
            };

            recognition.start();
        }
    }
}
</script>
@endpush
@endsection
