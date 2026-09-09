@extends('layouts.app')

@section('title', 'লগইন পোর্টাল - ডেইলি বাজার ও ফান্ড ম্যানেজমেন্ট')

@section('content')
<div class="max-w-md mx-auto py-2 sm:py-6" x-data="{ showPass: false, activeRoleHint: 'all' }">
    
    <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-200/80 space-y-6 relative overflow-hidden">
        
        <!-- Background Ambient Glow -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-brand-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-purple-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

        <!-- Header & Logo -->
        <div class="text-center relative">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white text-3xl mb-3 shadow-lg shadow-brand-600/30">
                🛒
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">বাজার ও ফান্ড লগইন পোর্টাল</h1>
            <p class="text-xs text-slate-500 mt-1 font-medium">সকল সদস্য (প্রিন্সিপাল, পিএ, মেসেঞ্জার, ফ্যামিলি) এই প্যানেল থেকে লগইন করুন</p>
        </div>

        <!-- Role Badges Hint Selector Bar -->
        <div class="p-1 bg-slate-100 rounded-2xl grid grid-cols-4 text-center gap-1 text-[11px] font-bold">
            <button type="button" @click="activeRoleHint = 'principal'" :class="activeRoleHint === 'principal' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-500'" class="py-1.5 rounded-xl transition">
                👑 প্রিন্সিপাল
            </button>
            <button type="button" @click="activeRoleHint = 'pa'" :class="activeRoleHint === 'pa' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500'" class="py-1.5 rounded-xl transition">
                💼 পিএ
            </button>
            <button type="button" @click="activeRoleHint = 'messenger'" :class="activeRoleHint === 'messenger' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'" class="py-1.5 rounded-xl transition">
                🛵 মেসেঞ্জার
            </button>
            <button type="button" @click="activeRoleHint = 'family'" :class="activeRoleHint === 'family' ? 'bg-white text-amber-700 shadow-sm' : 'text-slate-500'" class="py-1.5 rounded-xl transition">
                🏡 পরিবার
            </button>
        </div>

        <!-- Active Role Guidance Banner -->
        <div class="text-[11px] p-2.5 rounded-xl border transition"
             :class="{
                'bg-purple-50 border-purple-200 text-purple-900': activeRoleHint === 'principal',
                'bg-blue-50 border-blue-200 text-blue-900': activeRoleHint === 'pa',
                'bg-emerald-50 border-emerald-200 text-emerald-900': activeRoleHint === 'messenger',
                'bg-amber-50 border-amber-200 text-amber-900': activeRoleHint === 'family',
                'bg-slate-50 border-slate-200 text-slate-700': activeRoleHint === 'all'
             }">
            <template x-if="activeRoleHint === 'principal'">
                <p>👑 <strong>প্রিন্সিপাল (Super Admin):</strong> খরচের অনুমোদন, ভাউচার অডিট, ওয়ালেট রিচার্জ ও ইউজার নিয়ন্ত্রণ।</p>
            </template>
            <template x-if="activeRoleHint === 'pa'">
                <p>💼 <strong>পিএ (Manager):</strong> বাজার খরচের প্রাথমিক ভেরিফিকেশন ও ডেইলি মনিটরিং।</p>
            </template>
            <template x-if="activeRoleHint === 'messenger'">
                <p>🛵 <strong>বাজার মেসেঞ্জার (Staff):</strong> কাঁচাবাজার এন্ট্রি, ক্যাশ মেমোর ছবি আপলোড ও ফান্ড রিকোয়েস্ট।</p>
            </template>
            <template x-if="activeRoleHint === 'family'">
                <p>🏡 <strong>পরিবারের সদস্য (Viewer):</strong> স্বচ্ছতার জন্য প্রতিদিনের বাজার খরচের লাইভ মনিটরিং।</p>
            </template>
            <template x-if="activeRoleHint === 'all'">
                <p>💡 আপনার রেজিস্টার্ড মোবাইল নম্বর বা ইমেইল এবং পাসওয়ার্ড দিয়ে লগইন করুন।</p>
            </template>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Login Field (Phone or Email) with Autocomplete -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">মোবাইল নম্বর অথবা ইমেইল *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3.5 text-slate-400 text-sm">📱</span>
                    <input type="text" name="login" value="{{ old('login') }}" autocomplete="username" required autofocus placeholder="যেমন: 01713144920 অথবা admin@expense.com" class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-xs sm:text-sm font-semibold bg-slate-50/50">
                </div>
            </div>

            <!-- Password Field with Autocomplete & Show/Hide toggle -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">পাসওয়ার্ড *</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3.5 text-slate-400 text-sm">🔒</span>
                    <input :type="showPass ? 'text' : 'password'" name="password" autocomplete="current-password" required placeholder="••••••••" class="w-full pl-10 pr-10 py-3 rounded-2xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-xs sm:text-sm bg-slate-50/50">
                    <button type="button" @click="showPass = !showPass" class="absolute right-3.5 top-3 text-slate-400 hover:text-slate-600 text-sm focus:outline-none">
                        <span x-text="showPass ? '🙈' : '👁️'"></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                    <span class="text-slate-700 font-bold">লগইন তথ্য ও পাসওয়ার্ড মনে রাখুন (Auto-Save)</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white font-black rounded-2xl text-sm sm:text-base shadow-lg shadow-brand-600/25 transition active:scale-[0.99] flex items-center justify-center gap-2">
                <span>লগইন করুন</span>
                <span>➔</span>
            </button>
        </form>

        <!-- 1-Click Android App Install Card on Login Screen -->
        <div class="p-3.5 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-500/10 rounded-2xl border border-emerald-300/80 dark:border-emerald-800 text-center space-y-2">
            <div class="flex items-center justify-center gap-2 text-emerald-950 dark:text-emerald-200 text-xs font-black">
                <span>📲</span>
                <span>অ্যান্ড্রয়েড মোবাইলে ১-ট্যাপে ইনস্টল করুন</span>
            </div>
            <button type="button" @click="pwaInstallModal = true" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/25 transition active:scale-95 flex items-center justify-center gap-1.5">
                <span>📲 অ্যাপ ডাউনলোড ও ইনস্টল করুন</span>
            </button>
        </div>

        <!-- Security Footer -->
        <div class="pt-2 text-center border-t border-slate-100 flex items-center justify-center gap-1.5 text-[11px] text-slate-400">
            <span>🛡️</span>
            <span>সম্পূর্ণ এনক্রিপ্টেড, আইপি ট্র্যাকিং ও রোল-বেসড সিকিউর পোর্টাল</span>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Strict Anti-Inspect & Anti-F12 for Login Page
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || 
            e.keyCode === 123 ||
            (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) || 
            (e.ctrlKey && (e.key === 'U' || e.key === 'u' || e.key === 'S' || e.key === 's'))) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush
@endsection
