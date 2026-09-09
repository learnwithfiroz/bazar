@extends('layouts.app')

@section('title', 'বিল রিভিউ ও মেমো পরীক্ষণ - ' . $expense->title)

@section('content')
<div class="space-y-4 sm:space-y-6" x-data="{ fullImageModal: false, activeImageUrl: '', mobileTab: 'items' }">
    
    <!-- Top Action Bar Banner -->
    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                        <span>⬅</span>
                        <span>খরচের তালিকা</span>
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-xs font-black text-slate-400">বিল #{{ $expense->id }}</span>
                    @if($expense->memo_no)
                    <span class="text-xs font-black text-brand-800 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-full">মেমো: {{ $expense->memo_no }}</span>
                    @endif
                </div>
                <h1 class="text-lg sm:text-2xl font-black text-slate-900 leading-tight tracking-tight">{{ $expense->title }}</h1>
                <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap font-medium">
                    <span>📅 {{ date('d F, Y', strtotime($expense->expense_date)) }}</span>
                    <span>👤 এন্ট্রি করেছেন: <strong class="text-slate-800">{{ $expense->creator->name }}</strong></span>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <a href="{{ $whatsappUrl }}" target="_blank" class="flex-1 sm:flex-initial text-center py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md shadow-emerald-600/30 flex items-center justify-center gap-1.5 transition">
                    <span>💬 WhatsApp এ পাঠান</span>
                </a>
                <a href="{{ route('reports.print-day', ['expense_id' => $expense->id]) }}" target="_blank" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 flex items-center justify-center gap-1.5 transition">
                    <span>🖨️ প্রিন্ট</span>
                </a>

                <!-- Super Admin Delete Expense Button -->
                @if(auth()->user()->isPrincipal())
                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি এই খরচের ডাটা এবং বিল ডিলিট করতে চান? মেসেঞ্জারের ওয়ালেটে টাকা ফেরত যুক্ত হবে।');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="py-2.5 px-3.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 text-xs font-bold rounded-xl transition flex items-center gap-1">
                        <span>🗑️ ডিলিট</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Mobile View Segmented Switcher (Visible only on < lg) -->
        <div class="lg:hidden grid grid-cols-2 p-1 bg-slate-100/80 rounded-2xl text-xs font-bold">
            <button type="button" @click="mobileTab = 'items'" :class="mobileTab === 'items' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="py-2 rounded-xl transition">
                📋 আইটেম ও রিভিউ ({{ $expense->items->count() }})
            </button>
            <button type="button" @click="mobileTab = 'slips'" :class="mobileTab === 'slips' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="py-2 rounded-xl transition">
                📸 মেমো / স্লিপ ({{ $expense->slips->count() }})
            </button>
        </div>
    </div>

    <!-- Split Screen Inspection Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start">
        
        <!-- Left Side: Cash Memo / Slips Viewer (5 cols) -->
        <div class="lg:col-span-5 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4" :class="mobileTab === 'slips' ? 'block' : 'hidden lg:block'">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>📸</span>
                    <span>সংযুক্ত ক্যাশ মেমো / বিল ({{ $expense->slips->count() }})</span>
                </h2>
                <span class="text-[10px] text-slate-400 font-semibold">ট্যাপ করে জুম করুন</span>
            </div>

            @if($expense->slips->isNotEmpty())
            <div class="space-y-3">
                @foreach($expense->slips as $slip)
                <div class="group relative rounded-3xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm hover:border-brand-400 transition">
                    <img src="{{ asset('storage/' . $slip->image_path) }}" 
                         alt="Slip #{{ $slip->id }}" 
                         @click="fullImageModal = true; activeImageUrl = '{{ asset('storage/' . $slip->image_path) }}'"
                         class="w-full h-auto object-contain max-h-96 cursor-zoom-in group-hover:opacity-95 transition">
                    <div class="p-3 bg-slate-900 text-white text-[11px] flex justify-between items-center">
                        <span class="truncate max-w-[180px]">{{ $slip->original_name ?: 'Slip Image' }}</span>
                        <a href="{{ asset('storage/' . $slip->image_path) }}" target="_blank" class="text-brand-400 hover:underline font-bold">আলাদা ট্যাবে খুলুন ↗</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-10 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                <span class="text-3xl block mb-2">📄</span>
                <p class="text-xs text-slate-500 font-medium">এই খরচের সাথে কোনো ক্যাশ মেমো সংযুক্ত করা হয়নি।</p>
            </div>
            @endif
        </div>

        <!-- Right Side: Items Breakdown, Status Actions & Review Threads (7 cols) -->
        <div class="lg:col-span-7 space-y-4 sm:space-y-6" :class="mobileTab === 'items' ? 'block' : 'hidden lg:block'">
            
            <!-- Items Table & Financial Box -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                    <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <span>📋</span>
                        <span>বাজারের আইটেম তালিকা</span>
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full border text-[10px] sm:text-xs font-black {{ $expense->status_badge_class }}">
                        {{ $expense->status_label }}
                    </span>
                </div>

                <!-- Items List -->
                <div class="space-y-2">
                    @foreach($expense->items as $item)
                    <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200 flex justify-between items-center text-xs hover:border-brand-200 transition">
                        <div>
                            <div class="font-bold text-slate-900">{{ $item->item_name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $item->category }} • {{ $item->quantity }} {{ $item->unit }} × ৳{{ number_format($item->unit_price, 2) }}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-slate-900 text-sm">৳ {{ number_format($item->total_price, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Total Amount Banner -->
                <div class="p-4 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl flex justify-between items-center shadow-lg">
                    <span class="text-xs sm:text-sm font-bold">সর্বমোট বাজার খরচ:</span>
                    <span class="text-xl sm:text-2xl font-black text-brand-400">৳ {{ number_format($expense->total_amount, 2) }}</span>
                </div>

                @if($expense->notes)
                <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900">
                    <span class="font-bold">নোট:</span> {{ $expense->notes }}
                </div>
                @endif

                <!-- Status Modification for Principal / PA -->
                @if(auth()->user()->canManageFunds())
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">প্রিন্সিপাল / পিএ অনুমোদন ও যাচাই:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <form action="{{ route('expenses.status.update', $expense->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="REVIEWED">
                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                                ✅ অনুমোদন (Reviewed)
                            </button>
                        </form>
                        <form action="{{ route('expenses.status.update', $expense->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="FLAGGED">
                            <button type="submit" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 text-xs font-bold rounded-xl transition">
                                🚩 সংশোধন প্রয়োজন (Flag)
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <!-- Threaded Review Comments & Discussion Box -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-2.5">
                    <h2 class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <span>💬</span>
                        <span>স্লিপ রিভিউ ও কথোপকথন থ্রেড</span>
                    </h2>
                    <span class="text-[11px] text-slate-500">প্রিন্সিপাল ও মেসেঞ্জারের মধ্যে সরাসরি বার্তা আদান-প্রদান</span>
                </div>

                <!-- Existing Comments -->
                <div class="space-y-3">
                    @forelse($expense->comments as $comment)
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-900">{{ $comment->user->name }}</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $comment->user->isPrincipal() ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $comment->user->isPrincipal() ? 'প্রিন্সিপাল' : ($comment->user->isPA() ? 'পিএ' : 'মেসেঞ্জার') }}
                                </span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                @if(auth()->user()->isPrincipal() || $comment->user_id === auth()->id())
                                <form action="{{ route('expenses.comments.destroy', $comment->id) }}" method="POST" onsubmit="return confirm('মন্তব্যটি মুছবেন?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs" title="মুছুন">&times;</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-slate-700 leading-relaxed">{{ $comment->message }}</p>

                        <!-- Replies -->
                        @if($comment->replies->isNotEmpty())
                        <div class="pl-3 border-l-2 border-slate-300 space-y-2 mt-2 pt-1">
                            @foreach($comment->replies as $reply)
                            <div class="p-2.5 bg-white rounded-xl border border-slate-200 text-xs">
                                <div class="flex justify-between items-center mb-0.5">
                                    <span class="font-bold text-slate-800 text-[11px]">{{ $reply->user->name }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                        @if(auth()->user()->isPrincipal() || $reply->user_id === auth()->id())
                                        <form action="{{ route('expenses.comments.destroy', $reply->id) }}" method="POST" onsubmit="return confirm('মন্তব্যটি মুছবেন?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs">&times;</button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-slate-700 text-xs">{{ $reply->message }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Reply Input Box -->
                        <form action="{{ route('expenses.comments.store', $expense->id) }}" method="POST" class="pt-2 flex items-center gap-1.5">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <input type="text" name="message" required placeholder="উত্তর লিখুন..." class="flex-grow px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 bg-white">
                            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold whitespace-nowrap shadow-sm transition">
                                রিপ্লাই
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">এখনও কোনো মন্তব্য নেই।</p>
                    @endforelse
                </div>

                <!-- New Comment Form -->
                <form action="{{ route('expenses.comments.store', $expense->id) }}" method="POST" class="pt-2 border-t border-slate-100 space-y-2">
                    @csrf
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">নতুন মন্তব্য বা নির্দেশনা লিখুন</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <textarea name="message" rows="2" required placeholder="যেমন: কোনো প্রশ্ন বা নির্দেশনা থাকলে লিখুন..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 bg-slate-50/50"></textarea>
                        <button type="submit" class="py-2.5 px-5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs whitespace-nowrap shadow-md shadow-brand-600/20">
                            মন্তব্য পোস্ট
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

    <!-- Image Lightbox Modal with Full-Screen Mobile Touch -->
    <div x-show="fullImageModal" x-cloak class="fixed inset-0 bg-black/95 backdrop-blur-md z-50 flex items-center justify-center p-2 sm:p-4">
        <div @click.away="fullImageModal = false" class="relative max-w-4xl max-h-[92vh] w-full flex flex-col items-center">
            <button @click="fullImageModal = false" class="absolute -top-8 right-2 text-white text-3xl font-bold hover:text-slate-300">&times;</button>
            <img :src="activeImageUrl" class="max-h-[85vh] max-w-full object-contain rounded-2xl shadow-2xl">
        </div>
    </div>

</div>
@endsection
