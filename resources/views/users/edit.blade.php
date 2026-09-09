@extends('layouts.app')

@section('title', 'ইউজার ডাটা পরিবর্তন - ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-5 sm:space-y-6" x-data="{ selectedRole: '{{ $user->role }}', showPass: false }">
    
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-purple-950 text-white p-5 sm:p-6 rounded-3xl shadow-xl flex items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-3 relative z-10">
            <a href="{{ route('users.index') }}" class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-black text-sm border border-white/20 transition">
                ⬅
            </a>
            <div>
                <h1 class="text-base sm:text-xl font-black text-white leading-tight">ইউজারের সকল ডাটা পরিবর্তন</h1>
                <p class="text-xs text-slate-300">{{ $user->name }} ({{ $user->role_display_name }})</p>
            </div>
        </div>

        <span class="px-3 py-1 rounded-full text-xs font-black relative z-10 {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/40' : 'bg-rose-500/20 text-rose-300 border border-rose-400/40' }}">
            {{ $user->is_active ? '🟢 সক্রিয় অ্যাকাউন্ট' : '🚫 নিস্ক্রিয়' }}
        </span>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white p-5 sm:p-7 rounded-3xl border border-slate-200/80 shadow-sm space-y-5">
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_edit_page" value="1">

            <!-- Full Name -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">পূর্ণ নাম *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
            </div>

            <!-- Phone Number -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">মোবাইল নম্বর (লগইন আইডি) *</label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
            </div>

            <!-- Email Address -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ইমেইল এড্রেস (ঐচ্ছিক)</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="user@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ইউজার রোল / পদবি *</label>
                <select name="role" x-model="selectedRole" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="messenger" {{ $user->role === 'messenger' ? 'selected' : '' }}>🛵 বাজার মেসেঞ্জার (Staff - খরচ এন্ট্রি ও ওয়ালেট)</option>
                    <option value="pa" {{ $user->role === 'pa' ? 'selected' : '' }}>💼 প্রিন্সিপালের পিএ (Manager - মনিটরিং ও রিভিউ)</option>
                    <option value="family" {{ $user->role === 'family' ? 'selected' : '' }}>🏡 পরিবারের সদস্য (Viewer - রিড-ওনলি)</option>
                    <option value="principal" {{ $user->role === 'principal' ? 'selected' : '' }}>👑 প্রিন্সিপাল (Super Admin - সম্পূর্ণ ক্ষমতা)</option>
                </select>
            </div>

            <!-- Account Status Active/Inactive -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">অ্যাকাউন্ট স্ট্যাটাস *</label>
                <select name="is_active" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>🟢 সচল (Active - লগইন করতে পারবে)</option>
                    <option value="0" {{ !$user->is_active ? 'selected' : '' }}>🔴 নিস্ক্রিয় (Inactive - সাময়িক স্থগিত)</option>
                </select>
            </div>

            <!-- Direct Reset Password -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-800 uppercase">🔑 নতুন পাসওয়ার্ড সেট করুন (ঐচ্ছিক)</label>
                <div class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password" placeholder="পাসওয়ার্ড অপরিবর্তিত রাখতে খালি রাখুন" class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500 bg-white">
                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                        <span x-text="showPass ? '🙈' : '👁️'"></span>
                    </button>
                </div>
                <span class="text-[10px] text-slate-500 block">সুপার এডমিন হিসেবে আপনি সরাসরি যেকোনো ইউজারের পাসওয়ার্ড রিসেট করতে পারেন।</span>
            </div>

            <!-- Messenger Wallet Balance Direct Adjustment -->
            <div x-show="selectedRole === 'messenger'" class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl space-y-3">
                <h3 class="text-xs font-black text-emerald-950 uppercase flex items-center gap-1.5">
                    <span>💳</span>
                    <span>মেসেঞ্জারের ওয়ালেট ও ফান্ড ব্যালেন্স সংশোধন</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-emerald-900 uppercase mb-1">বর্তমান ব্যালেন্স সরাসরি পরিবর্তন (৳)</label>
                        <input type="number" step="0.01" name="wallet_balance" value="{{ $user->wallet->current_balance ?? 0 }}" class="w-full px-3 py-2.5 rounded-xl border border-emerald-300 text-xs sm:text-sm font-black focus:ring-2 focus:ring-emerald-500 bg-white">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-emerald-900 uppercase mb-1">লো ব্যালেন্স এলার্ট লিমিট (৳)</label>
                        <input type="number" step="0.01" name="low_balance_alert_limit" value="{{ $user->wallet->low_balance_alert_limit ?? 500 }}" class="w-full px-3 py-2.5 rounded-xl border border-emerald-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-emerald-500 bg-white">
                    </div>
                </div>
                <span class="text-[10px] text-emerald-700 block">⚠️ ব্যালেন্স সরাসরি পরিবর্তন করলে লেজারে স্বয়ংক্রিয় অ্যাডজাস্টমেন্ট রেকর্ড থাকবে।</span>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <a href="{{ route('users.index') }}" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    বাতিল
                </a>
                <button type="submit" class="py-3 px-6 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs sm:text-sm font-black rounded-xl shadow-lg shadow-brand-600/25 transition active:scale-[0.99]">
                    💾 সকল পরিবর্তন সেভ করুন
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
