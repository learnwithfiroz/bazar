@extends('layouts.app')

@section('title', 'নতুন বাজার খরচ ও বিল সাবমিট')

@section('content')
<div class="max-w-3xl mx-auto space-y-4 sm:space-y-6" x-data="expenseForm()">
    
    <!-- Top Header & Live Wallet Floating Status -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white p-5 sm:p-6 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-3 relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md text-emerald-400 border border-white/20 flex items-center justify-center text-2xl font-black shadow-inner">
                📝
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-white leading-tight">বাজার খরচ ও বিল সাবমিশন</h1>
                <p class="text-xs text-slate-300">পণ্যের তালিকা ও ক্যাশ মেমো / বিলের ছবি সংযুক্ত করুন</p>
            </div>
        </div>

        <div class="w-full sm:w-auto bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/20 flex sm:flex-col justify-between sm:justify-center items-center sm:items-end relative z-10">
            <span class="text-[10px] text-slate-300 font-bold uppercase tracking-wider block">বর্তমান ওয়ালেট ব্যালেন্স:</span>
            <span class="text-lg sm:text-xl font-black text-emerald-400">৳ {{ number_format($wallet->current_balance, 2) }}</span>
        </div>
    </div>

    <!-- Main Entry Form -->
    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-6 pb-14 sm:pb-0">
        @csrf

        <!-- Date & Bill Info Card -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                <span class="text-base">📌</span>
                <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">বাজারের তারিখ ও মেমো বিবরণ</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-1">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">বাজারের তারিখ *</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">খরচের শিরোনাম *</label>
                    <input type="text" name="title" value="দৈনিক বাজার খরচ" required placeholder="যেমন: আজকের কাঁচাবাজার ও মাছ" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">দোকান / বাজার (Vendor)</label>
                    <input type="text" name="vendor_name" list="vendor-suggestions" placeholder="যেমন: কারওয়ান বাজার / ভাই ভাই স্টোর" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    <datalist id="vendor-suggestions">
                        @if(!empty($vendorSuggestions) && count($vendorSuggestions) > 0)
                            @foreach($vendorSuggestions as $v)
                            <option value="{{ $v }}">
                            @endforeach
                        @else
                            <option value="কারওয়ান বাজার">
                            <option value="গুলশান ডিসিসি মার্কেট">
                            <option value="বনানী সুপার মার্কেট">
                            <option value="মদিনা জেনারেল স্টোর">
                            <option value="লোকাল কাঁচাবাজার">
                            <option value="স্বপ্ন সুপারশপ">
                        @endif
                    </datalist>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">ক্যাশ মেমো / বিল নং (ঐচ্ছিক)</label>
                    <input type="text" name="memo_no" placeholder="যেমন: MEMO-1029" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>
            </div>
        </div>

        <!-- 1-Tap Auto-Suggest Quick Add Chips -->
        @if(!empty($suggestedItems) && count($suggestedItems) > 0)
        <div class="bg-gradient-to-r from-indigo-50/80 to-purple-50/80 dark:from-slate-800/80 dark:to-indigo-950/40 p-4 rounded-3xl border border-indigo-100 dark:border-indigo-900/60 space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="font-black text-indigo-950 dark:text-indigo-200 flex items-center gap-1.5">
                    <span>💡</span>
                    <span>পূর্ববর্তী বাজারের অটো-সাজেশন (১-ট্যাপে যোগ করুন):</span>
                </span>
                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">অটো-ফিল ক্যাটাগরি ও দর</span>
            </div>
            <div class="flex flex-wrap gap-1.5 pt-1 max-h-24 overflow-y-auto">
                @foreach($suggestedItems->take(18) as $sItem)
                <button type="button" @click="quickAddItem('{{ addslashes($sItem->item_name) }}', '{{ addslashes($sItem->category) }}', '{{ addslashes($sItem->unit) }}', {{ (float) $sItem->avg_price }})" class="px-3 py-1 bg-white dark:bg-slate-800 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold border border-indigo-200/80 dark:border-indigo-800 shadow-sm transition active:scale-95 flex items-center gap-1">
                    <span>+</span>
                    <span>{{ $sItem->item_name }}</span>
                    <span class="text-[10px] opacity-60">({{ $sItem->unit }})</span>
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Dynamic Bazaar Items Repeater Card -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>🛒</span>
                        <span>বাজারের আইটেমসমূহ</span>
                    </h2>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">পণ্যের নাম লিখলেই ক্যাটাগরি ও দর অটোমেটিক সাজেস্ট হবে</span>
                </div>
                <button type="button" @click="addItem()" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-black rounded-xl shadow-md shadow-brand-600/20 flex items-center gap-1 transition">
                    <span>➕ আইটেম</span>
                </button>
            </div>

            <!-- Datalist for live auto-suggestions -->
            <datalist id="suggested-items-list">
                @if(!empty($suggestedItems))
                    @foreach($suggestedItems as $sItem)
                    <option value="{{ $sItem->item_name }}">{{ $sItem->category }} ({{ $sItem->unit }})</option>
                    @endforeach
                @endif
                <option value="আলু (Potato)">কাঁচাবাজার (কেজি)</option>
                <option value="পিঁয়াজ (Onion)">কাঁচাবাজার (কেজি)</option>
                <option value="রসুন (Garlic)">কাঁচাবাজার (কেজি)</option>
                <option value="আদা (Ginger)">কাঁচাবাজার (কেজি)</option>
                <option value="কাঁচামরিচ (Green Chili)">কাঁচাবাজার (কেজি)</option>
                <option value="টমেটো (Tomato)">কাঁচাবাজার (কেজি)</option>
                <option value="রুই মাছ (Rui Fish)">মাছ ও মাংস (কেজি)</option>
                <option value="কাতল মাছ (Katla Fish)">মাছ ও মাংস (কেজি)</option>
                <option value="ইলিশ মাছ (Hilsa)">মাছ ও মাংস (কেজি)</option>
                <option value="চিংড়ি মাছ (Prawn)">মাছ ও মাংস (কেজি)</option>
                <option value="গরুর মাংস (Beef)">মাছ ও মাংস (কেজি)</option>
                <option value="মুরগির মাংস (Chicken)">মাছ ও মাংস (কেজি)</option>
                <option value="ডিম (Egg)">মুদিখানা (ডজন/হালি)</option>
                <option value="সয়াবিন তেল (Soybean Oil)">মুদিখানা (লিটার)</option>
                <option value="সরিষার তেল (Mustard Oil)">মুদিখানা (লিটার)</option>
                <option value="মিনিকেট চাল (Rice)">মুদিখানা (কেজি)</option>
                <option value="নাজিরশাইল চাল (Rice)">মুদিখানা (কেজি)</option>
                <option value="মসুর ডাল (Lentil)">মুদিখানা (কেজি)</option>
                <option value="চিনি (Sugar)">মুদিখানা (কেজি)</option>
                <option value="লবণ (Salt)">মুদিখানা (প্যাকেট)</option>
                <option value="লাল শাক (Red Spinach)">শাকসবজি (আঁটি)</option>
                <option value="পালং শাক (Spinach)">শাকসবজি (আঁটি)</option>
                <option value="কলা (Banana)">ফলমূল (ডজন)</option>
                <option value="আপেল (Apple)">ফলমূল (কেজি)</option>
            </datalist>

            <!-- Items List Repeater Cards -->
            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 space-y-3 relative hover:border-brand-300 transition">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-xs font-black" x-text="index + 1"></span>
                                <span>পণ্যের তথ্য</span>
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="startVoiceRecognition(index)" title="মুখে বলে পণ্যের নাম লিখুন" class="px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg border border-indigo-200 text-xs font-bold flex items-center gap-1">
                                    <span>🎙️</span>
                                    <span class="hidden sm:inline">ভয়েস</span>
                                </button>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1 bg-rose-50 rounded-lg border border-rose-100 flex items-center gap-1">
                                    <span>🗑️ মুছুন</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <!-- Item Name with Auto-Suggest Datalist -->
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">পণ্যের নাম *</label>
                                <input type="text" :name="`items[${index}][name]`" list="suggested-items-list" x-model="item.name" @input="onItemNameInput(index)" required placeholder="যেমন: আলু / রুই মাছ" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white">
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">ক্যাটাগরি</label>
                                <select :name="`items[${index}][category]`" x-model="item.category" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white">
                                    @foreach($defaultCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantity & Unit -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">পরিমাণ ও একক *</label>
                                <div class="flex gap-1">
                                    <input type="number" inputmode="decimal" step="any" :name="`items[${index}][quantity]`" x-model="item.quantity" required min="0.01" class="w-16 px-2 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-black focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white text-center">
                                    <select :name="`items[${index}][unit]`" x-model="item.unit" class="w-full px-2 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white">
                                        <option value="কেজি">কেজি</option>
                                        <option value="গ্রাম">গ্রাম</option>
                                        <option value="লিটার">লিটার</option>
                                        <option value="পিস">পিস</option>
                                        <option value="ডজন">ডজন</option>
                                        <option value="হালি">হালি</option>
                                        <option value="আঁটি">আঁটি</option>
                                        <option value="প্যাকেট">প্যাকেট</option>
                                        <option value="বস্তা">বস্তা</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Unit Price & Subtotal -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">দর (৳) ও মোট</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" inputmode="decimal" step="any" :name="`items[${index}][unit_price]`" x-model="item.unit_price" required min="0" placeholder="দর" class="w-20 px-2 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-black focus:ring-2 focus:ring-brand-500 bg-white dark:bg-slate-800 dark:text-white text-right">
                                    <span class="text-xs font-black text-slate-900 dark:text-emerald-400 whitespace-nowrap">
                                        ৳ <span x-text="((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Grand Total Float -->
            <div class="p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-slate-800 dark:to-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800 flex justify-between items-center">
                <span class="text-xs font-black text-emerald-950 dark:text-emerald-300">সর্বমোট বিল (Grand Total):</span>
                <span class="text-xl font-black text-emerald-700 dark:text-emerald-400">৳ <span x-text="calculateGrandTotal().toFixed(2)"></span></span>
            </div>
        </div>

        <!-- Cash Memo / Slip Photos (Camera + Gallery) -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
            <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                <span class="text-base">📸</span>
                <h2 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">ক্যাশ মেমো / রসিদের ছবি</h2>
            </div>

            <div class="p-6 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 text-center space-y-2 relative hover:bg-slate-100/60 transition cursor-pointer">
                <input type="file" name="slip_photos[]" multiple accept="image/*" @change="previewImages($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mx-auto">
                    📷
                </div>
                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">
                    ক্যামেরা দিয়ে ছবি তুলুন বা মেমোর ছবি আপলোড করুন
                </div>
                <p class="text-[11px] text-slate-400">একাধিক মেমো একসাথে সিলেক্ট করা যাবে</p>
            </div>

            <!-- Preview Images Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-2" x-show="previewUrls.length > 0">
                <template x-for="url in previewUrls">
                    <div class="relative rounded-2xl overflow-hidden border-2 border-brand-500 aspect-square bg-slate-100 shadow-sm">
                        <img :src="url" class="w-full h-full object-cover">
                        <span class="absolute bottom-1 right-1 bg-slate-900/80 text-white text-[9px] px-2 py-0.5 rounded-full font-bold">বিল যুক্ত</span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Additional Notes -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">নোট / বিশেষ বিবরণ (ঐচ্ছিক)</label>
            <textarea name="notes" rows="2" placeholder="দোকানের নাম বা কোনো বিশেষ তথ্য লিখুন..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white"></textarea>
        </div>

        <!-- Submit & WhatsApp 1-Click Direct Trigger -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
            <label class="flex items-center gap-3 cursor-pointer p-4 bg-gradient-to-r from-emerald-500/10 via-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/20 border border-emerald-300 dark:border-emerald-800 rounded-2xl select-none hover:bg-emerald-100/60 transition">
                <input type="checkbox" name="send_whatsapp_now" value="1" checked class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                <div>
                    <span class="text-xs sm:text-sm font-black text-emerald-950 dark:text-emerald-200 block">🟢 সাবমিটের সাথে সাথে WhatsApp এ সম্পূর্ণ বিল টাইপ হয়ে ওপেন হবে (1-Click)</span>
                    <span class="text-[11px] text-emerald-800 dark:text-emerald-400">বাজারের প্রতিটি পণ্য, দর ও মোট হিসাব স্বয়ংক্রিয়ভাবে প্রিন্সিপালের WhatsApp এ রেডি হয়ে যাবে</span>
                </div>
            </label>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-brand-600 via-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white font-black rounded-2xl text-sm sm:text-base shadow-xl shadow-brand-600/30 flex items-center justify-center gap-2 transition active:scale-[0.99]">
                <span>💬 বিল সাবমিট করুন ও WhatsApp এ অটো পাঠান</span>
                <span>➔</span>
            </button>
        </div>

    </form>

</div>

@push('scripts')
<script>
function expenseForm() {
    return {
        items: {!! json_encode(!empty($prefilledItems) ? $prefilledItems : [['name' => '', 'category' => 'কাঁচাবাজার', 'quantity' => 1, 'unit' => 'কেজি', 'unit_price' => 0]], JSON_UNESCAPED_UNICODE) !!},
        suggestedItemsDb: {!! json_encode(!empty($suggestedItems) ? $suggestedItems : [], JSON_UNESCAPED_UNICODE) !!},
        previewUrls: [],
        addItem() {
            this.items.push({ name: '', category: 'কাঁচাবাজার', quantity: 1, unit: 'কেজি', unit_price: 0 });
        },
        quickAddItem(name, category, unit, price) {
            // If the first item is empty, replace it, otherwise append
            if (this.items.length === 1 && !this.items[0].name) {
                this.items[0] = { name: name, category: category || 'কাঁচাবাজার', quantity: 1, unit: unit || 'কেজি', unit_price: price || 0 };
            } else {
                this.items.push({ name: name, category: category || 'কাঁচাবাজার', quantity: 1, unit: unit || 'কেজি', unit_price: price || 0 });
            }
        },
        onItemNameInput(index) {
            const currentName = (this.items[index].name || '').trim().toLowerCase();
            if (!currentName) return;

            // Find matching item from database history
            const match = this.suggestedItemsDb.find(s => s.item_name.toLowerCase() === currentName);
            if (match) {
                if (match.category) this.items[index].category = match.category;
                if (match.unit) this.items[index].unit = match.unit;
                if (match.avg_price && (!this.items[index].unit_price || this.items[index].unit_price == 0)) {
                    this.items[index].unit_price = parseFloat(match.avg_price) || 0;
                }
            }
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        calculateGrandTotal() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
        },
        previewImages(event) {
            this.previewUrls = [];
            const files = event.target.files;
            if (!files) return;
            for (let i = 0; i < files.length; i++) {
                this.previewUrls.push(URL.createObjectURL(files[i]));
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
                alert('🎙️ মাইকে পণ্যের নাম বলুন...');
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
