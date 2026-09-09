@extends('layouts.app')

@section('title', 'আমার প্রোফাইল ও সেটিংস - ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-5 sm:space-y-6" x-data="{ 
    avatarPreview: '{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}',
    showCurrentPass: false,
    showNewPass: false,
    showConfirmPass: false,
    newPasswordValue: '',
    activeTab: '{{ request('tab', 'info') }}',
    handleAvatarSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.avatarPreview = URL.createObjectURL(file);
        }
    },
    getPasswordStrength() {
        if (!this.newPasswordValue) return 0;
        let score = 0;
        if (this.newPasswordValue.length >= 6) score += 30;
        if (this.newPasswordValue.length >= 8) score += 20;
        if (/[A-Z]/.test(this.newPasswordValue)) score += 20;
        if (/[0-9]/.test(this.newPasswordValue)) score += 15;
        if (/[^A-Za-z0-9]/.test(this.newPasswordValue)) score += 15;
        return Math.min(100, score);
    }
}">
    
    <!-- Top VIP Hero Profile Card -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 rounded-3xl p-5 sm:p-8 text-white shadow-2xl relative overflow-hidden border border-slate-800">
        <div class="absolute -right-12 -bottom-12 w-60 h-60 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-0 right-1/4 w-40 h-40 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-7 relative z-10 text-center sm:text-left">
            
            <!-- Avatar Display with Live Camera Overlay -->
            <div class="relative group flex-shrink-0">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-slate-800/80 backdrop-blur-md border-2 border-indigo-400/50 p-1.5 flex items-center justify-center shadow-2xl overflow-hidden ring-4 ring-white/10">
                    <template x-if="avatarPreview">
                        <img :src="avatarPreview" class="w-full h-full object-cover rounded-2xl">
                    </template>
                    <template x-if="!avatarPreview">
                        <span class="text-4xl sm:text-5xl font-black text-indigo-300">{{ mb_substr($user->name, 0, 1) }}</span>
                    </template>
                </div>
                <!-- Status Badge Dot -->
                <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-3 border-slate-950 rounded-full ring-2 ring-emerald-400/50 flex items-center justify-center text-[10px] text-white font-bold" title="অ্যাক্টিভ মেম্বার">✓</span>
            </div>

            <!-- Profile Info Body -->
            <div class="space-y-2.5 flex-grow">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 justify-center sm:justify-start flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black w-fit mx-auto sm:mx-0 shadow-sm
                        {{ $user->isPrincipal() ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-purple-500/30' : '' }}
                        {{ $user->isPA() ? 'bg-gradient-to-r from-blue-500 to-cyan-600 text-white shadow-blue-500/30' : '' }}
                        {{ $user->isMessenger() ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-emerald-500/30' : '' }}
                        {{ $user->isFamily() ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-amber-500/30' : '' }}
                    ">
                        {{ $user->isPrincipal() ? '👑 ' . $user->role_display_name : ($user->isPA() ? '💼 ' . $user->role_display_name : ($user->isMessenger() ? '🛵 ' . $user->role_display_name : '🏡 ' . $user->role_display_name)) }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-2.5 text-xs text-slate-300">
                    <a href="https://wa.me/88{{ ltrim($user->phone_number, '0') }}" target="_blank" class="flex items-center gap-1.5 bg-white/10 hover:bg-emerald-600/30 px-3 py-1.5 rounded-xl border border-white/15 transition group">
                        <span class="text-emerald-400">📞</span>
                        <strong class="group-hover:text-emerald-300">{{ $user->phone_number }}</strong>
                    </a>
                    
                    @if($user->email)
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-blue-300">✉️</span>
                        <strong>{{ $user->email }}</strong>
                    </span>
                    @endif

                    <span class="flex items-center gap-1.5 bg-white/5 px-2.5 py-1.5 rounded-xl text-slate-400 text-[11px]">
                        📅 যোগদান: {{ $user->created_at->format('d M, Y') }}
                    </span>
                    <span class="flex items-center gap-1.5 bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 px-2.5 py-1.5 rounded-xl text-[11px] font-bold">
                        🌐 আইপি: {{ $user->last_login_ip ?: request()->ip() }}
                    </span>
                    <span class="flex items-center gap-1.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-2.5 py-1.5 rounded-xl text-[11px] font-bold">
                        ⏱️ শেষ লগইন: {{ $user->last_login_at ? $user->last_login_at->format('d M, h:i A') : 'বর্তমান সেশন' }}
                    </span>
                </div>
            </div>

            <!-- Messenger Wallet Quick Pill (If applicable) -->
            @if($user->isMessenger() && $userWallet)
            <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md px-5 py-3.5 rounded-3xl border border-white/20 text-center sm:text-right flex-shrink-0 shadow-xl">
                <span class="text-[10px] text-emerald-300 uppercase font-black tracking-wider block">হাতে নগদ ব্যালেন্স</span>
                <span class="text-2xl font-black text-emerald-400 mt-0.5 block">৳ {{ number_format($userWallet->current_balance, 2) }}</span>
            </div>
            @endif

        </div>

        <!-- Segmented Tab Navigation -->
        <div class="flex gap-2 pt-5 border-t border-slate-800 mt-6 overflow-x-auto">
            <button type="button" @click="activeTab = 'info'" :class="activeTab === 'info' ? 'bg-white text-slate-900 font-black shadow-lg' : 'bg-white/10 text-slate-300 font-bold hover:bg-white/20 hover:text-white'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 whitespace-nowrap">
                <span>👤</span>
                <span>ব্যক্তিগত তথ্য ও ছবি</span>
            </button>
            
            <button type="button" @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-white text-slate-900 font-black shadow-lg' : 'bg-white/10 text-slate-300 font-bold hover:bg-white/20 hover:text-white'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 whitespace-nowrap">
                <span>🔐</span>
                <span>পাসওয়ার্ড ও নিরাপত্তা</span>
            </button>

            @if($user->isPrincipal() || $user->isPA())
            <button type="button" @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'bg-white text-slate-900 font-black shadow-lg' : 'bg-white/10 text-slate-300 font-bold hover:bg-white/20 hover:text-white'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 whitespace-nowrap">
                <span>⚙️</span>
                <span>সিস্টেম ব্যাকআপ ও সেটিংস</span>
            </button>
            @endif

            <button type="button" @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'bg-white text-slate-900 font-black shadow-lg' : 'bg-white/10 text-slate-300 font-bold hover:bg-white/20 hover:text-white'" class="px-4 py-2.5 rounded-2xl text-xs transition flex items-center gap-2 whitespace-nowrap">
                <span>📊</span>
                <span>অ্যাক্টিভিটি ও পারমিশন</span>
            </button>
        </div>
    </div>

    <!-- 3 Quick Activity Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>মোট এন্ট্রি</span>
                <span class="text-base">📝</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $userExpensesCount }} টি</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">বাজার খরচের হিসাব বিবরণী</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>মোট টাকার অংক</span>
                <span class="text-base">💰</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-indigo-700 dark:text-indigo-400">৳ {{ number_format($userExpensesTotal, 2) }}</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">সর্বমোট সাবমিট করা বাজার বিল</span>
        </div>

        <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>অ্যাকাউন্ট স্ট্যাটাস</span>
                <span class="text-base">🛡️</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600">ভেরিফাইড</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">সিস্টেম অথরাইজড মেম্বার</span>
        </div>
    </div>

    <!-- Tab 1: Personal Info & Avatar Upload Form -->
    <div x-show="activeTab === 'info'" class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-6">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex justify-between items-center">
            <div class="space-y-0.5">
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>👤</span>
                    <span>ব্যক্তিগত প্রোফাইল ও ছবি আপডেট</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">আপনার নাম, মোবাইল নম্বর এবং প্রোফাইলের ছবি পরিবর্তন করুন</p>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold text-brand-700 bg-brand-50 border border-brand-200">
                লাইভ প্রিভিউ
            </span>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Avatar Drag & Drop or Camera Box -->
            <div class="p-5 bg-gradient-to-r from-slate-50 to-indigo-50/40 dark:from-slate-800/60 dark:to-indigo-950/40 rounded-3xl border-2 border-dashed border-indigo-200 dark:border-indigo-800 space-y-3 text-center relative hover:bg-indigo-50/60 transition cursor-pointer group">
                <input type="file" name="avatar" accept="image/*" @change="handleAvatarSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl mx-auto shadow-md shadow-indigo-600/30 group-hover:scale-110 transition">
                    📷
                </div>
                <div class="space-y-1">
                    <span class="text-xs sm:text-sm font-black text-slate-900 dark:text-white block">নতুন ছবি সিলেক্ট করুন বা ক্যামেরা দিয়ে ছবি তুলুন</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">JPG, PNG বা WebP ফরম্যাট (সর্বোচ্চ ৫ মেগাবাইট)</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Full Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">পূর্ণ নাম *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                </div>

                <!-- Mobile Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">মোবাইল নম্বর (লগইন ও WhatsApp আইডি) *</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-400 text-xs">🇧🇩</span>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    </div>
                </div>

                <!-- Email Address -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">ইমেইল এড্রেস (ঐচ্ছিক)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-400 text-xs">✉️</span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="যেমন: user@example.com" class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white font-black rounded-2xl text-xs sm:text-sm shadow-xl shadow-brand-600/25 transition active:scale-[0.99] flex items-center justify-center gap-2">
                <span>💾 প্রোফাইল তথ্য সেভ করুন</span>
                <span>➔</span>
            </button>
        </form>
    </div>

    <!-- Tab 2: Security & Password Change Form -->
    <div x-show="activeTab === 'security'" x-cloak class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-6">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex justify-between items-center">
            <div class="space-y-0.5">
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>🔐</span>
                    <span>পাসওয়ার্ড পরিবর্তন ও অ্যাকাউন্ট সুরক্ষা</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">আপনার অ্যাকাউন্টে নিরাপদে লগইন করার জন্য একটি শক্তিশালী পাসওয়ার্ড সেট করুন</p>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold text-purple-700 bg-purple-50 border border-purple-200">
                এনক্রিপ্টেড
            </span>
        </div>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">বর্তমান পাসওয়ার্ড *</label>
                <div class="relative">
                    <input :type="showCurrentPass ? 'text' : 'password'" name="current_password" required placeholder="বর্তমান পাসওয়ার্ড লিখুন" class="w-full px-4 py-3 pr-12 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    <button type="button" @click="showCurrentPass = !showCurrentPass" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-700 text-xs">
                        <span x-text="showCurrentPass ? '🙈 লুকান' : '👁️ দেখুন'"></span>
                    </button>
                </div>
            </div>

            <!-- New Password with Strength Meter -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">নতুন পাসওয়ার্ড (কমপক্ষে ৬ অক্ষর) *</label>
                <div class="relative">
                    <input :type="showNewPass ? 'text' : 'password'" name="new_password" x-model="newPasswordValue" required placeholder="নতুন শক্তিশালী পাসওয়ার্ড লিখুন" class="w-full px-4 py-3 pr-12 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-700 text-xs">
                        <span x-text="showNewPass ? '🙈 লুকান' : '👁️ দেখুন'"></span>
                    </button>
                </div>

                <!-- Live Strength Bar -->
                <div class="space-y-1 pt-1" x-show="newPasswordValue.length > 0">
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="h-1.5 rounded-full transition-all duration-300" 
                             :class="getPasswordStrength() > 70 ? 'bg-emerald-500' : (getPasswordStrength() > 40 ? 'bg-amber-500' : 'bg-rose-500')"
                             :style="`width: ${getPasswordStrength()}%`"></div>
                    </div>
                    <span class="text-[10px] font-bold block"
                          :class="getPasswordStrength() > 70 ? 'text-emerald-700 dark:text-emerald-400' : (getPasswordStrength() > 40 ? 'text-amber-700 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')"
                          x-text="getPasswordStrength() > 70 ? '✓ শক্তিশালী পাসওয়ার্ড' : (getPasswordStrength() > 40 ? '⚠️ মাঝারি মানের পাসওয়ার্ড' : '❌ দুর্বল পাসওয়ার্ড')"></span>
                </div>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">নতুন পাসওয়ার্ড পুনরায় লিখুন *</label>
                <div class="relative">
                    <input :type="showConfirmPass ? 'text' : 'password'" name="new_password_confirmation" required placeholder="পুনরায় নতুন পাসওয়ার্ড লিখুন" class="w-full px-4 py-3 pr-12 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                    <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-700 text-xs">
                        <span x-text="showConfirmPass ? '🙈 লুকান' : '👁️ দেখুন'"></span>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-black rounded-2xl text-xs sm:text-sm shadow-lg transition active:scale-[0.99] flex items-center justify-center gap-2">
                <span>🔑 নতুন পাসওয়ার্ড সংরক্ষণ করুন</span>
                <span>➔</span>
            </button>
        </form>
    </div>

    <!-- Tab 3: System Settings & Database Backup/Restore (For Super Admin / Principal) -->
    @if($user->isPrincipal() || $user->isPA())
    <div x-show="activeTab === 'settings'" x-cloak class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-6">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>⚙️</span>
                <span>সিস্টেম ব্যাকআপ, রিস্টোর ও এক্সিকিউটিভ সেটিংস</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">ডেটাবেস ব্যাকআপ ডাউনলোড, ব্যাকআপ ফাইল আপলোড/রিস্টোর ও বাজেট সীমা নির্ধারণ</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            <!-- 1. Download Backup Box -->
            <div class="p-5 bg-gradient-to-br from-emerald-50 to-teal-50/60 dark:from-emerald-950/30 dark:to-teal-950/20 rounded-3xl border border-emerald-200 dark:border-emerald-900/60 space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">💾</span>
                        <h3 class="text-sm font-black text-emerald-950 dark:text-emerald-200">১-ক্লিক ডেটাবেস ব্যাকআপ ডাউনলোড</h3>
                    </div>
                    <p class="text-xs text-emerald-800 dark:text-emerald-300">বর্তমান সমস্ত খরচের হিসাব, মেমোর রেকর্ড ও ওয়ালেট ট্রানজেকশনের ব্যাকআপ ফাইল ডাউনলোড করে কম্পিউটারে সংরক্ষণ করুন।</p>
                </div>
                <a href="{{ route('system.backup') }}" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black text-center shadow-md shadow-emerald-600/30 flex items-center justify-center gap-2 transition active:scale-[0.99]">
                    <span>📥 সম্পূর্ণ ব্যাকআপ ফাইল ডাউনলোড করুন</span>
                </a>
            </div>

            <!-- 2. Monthly Budget Setting Box -->
            <div class="p-5 bg-gradient-to-br from-indigo-50 to-purple-50/60 dark:from-indigo-950/30 dark:to-purple-950/20 rounded-3xl border border-indigo-200 dark:border-indigo-900/60 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🎯</span>
                    <h3 class="text-sm font-black text-indigo-950 dark:text-indigo-200">মাসিক বাজার বাজেট সিলিং</h3>
                </div>
                <form action="{{ route('system.budget') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-indigo-900 dark:text-indigo-300 uppercase mb-1">সর্বোচ্চ বাজেট (টাকা) *</label>
                        <input type="number" inputmode="decimal" step="500" name="monthly_budget" value="{{ $monthlyBudgetLimit }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-300 dark:border-indigo-700 text-sm font-black focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 dark:text-white">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-md transition">
                        💾 বাজেট সেভ করুন
                    </button>
                </form>
            </div>

            <!-- 3. Upload & Restore Backup File (Super Admin Only) -->
            @if($user->isPrincipal())
            <div class="md:col-span-2 p-5 sm:p-6 bg-slate-50 dark:bg-slate-800/80 rounded-3xl border border-slate-200 dark:border-slate-700 space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <span class="text-2xl">📤</span>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">ডেটাবেস ব্যাকআপ ফাইল আপলোড ও রিস্টোর (Restore Backup)</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">পূর্বে ডাউনলোড করা ডেটাবেস ব্যাকআপ ফাইল (.sqlite বা .sql) আপলোড করে সমস্ত ডাটা ফিরিয়ে আনুন</p>
                    </div>
                </div>

                <form action="{{ route('system.backup-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি ব্যাকআপ ফাইলটি আপলোড করে বর্তমান ডেটাবেস রিস্টোর করতে চান?');">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">ব্যাকআপ ফাইল নির্বাচন করুন (.sqlite / .sql) *</label>
                            <input type="file" name="backup_file" accept=".sqlite,.db,.sqlite3,.sql" required class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">নিশ্চিত করতে সুপার এডমিনের পাসওয়ার্ড *</label>
                            <input type="password" name="admin_password" required placeholder="আপনার পাসওয়ার্ড লিখুন..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-indigo-500 bg-white dark:bg-slate-800 dark:text-white">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black rounded-2xl text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>📤 ব্যাকআপ ফাইল আপলোড ও রিস্টোর করুন</span>
                        <span>➔</span>
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
    @endif

    <!-- Tab 4: Role & Permissions Info Deck -->
    <div x-show="activeTab === 'activity'" x-cloak class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-5">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <span>🛡️</span>
                <span>আপনার রোল ও সিস্টেম অনুমতিসমূহ</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">আপনার বর্তমান পদবি অনুযায়ী যেসব সুবিধার অ্যাক্সেস রয়েছে</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <span class="text-xs font-black text-slate-900 dark:text-white block">লগইন ও প্রোফাইল ব্যবস্থাপনা</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">মোবাইল নম্বর ও পাসওয়ার্ড দিয়ে যেকোনো ডিভাইস থেকে লগইন।</span>
                </div>
            </div>

            @if($user->isPrincipal() || $user->isPA())
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <span class="text-xs font-black text-slate-900 dark:text-white block">ভাউচার অডিট ও অনুমোদন</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">ক্যাশ মেমো যাচাই, অনুমোদন এবং ফান্ড রিচার্জ করার অধিকার।</span>
                </div>
            </div>
            @endif

            @if($user->isPrincipal())
            <div class="p-4 rounded-2xl bg-purple-50/70 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/60 flex items-start gap-3">
                <span class="text-xl">👑</span>
                <div>
                    <span class="text-xs font-black text-purple-950 dark:text-purple-300 block">সুপার এডমিন পূর্ণ নিয়ন্ত্রণ</span>
                    <span class="text-[11px] text-purple-800 dark:text-purple-400">ইউজার তৈরি, সম্পূর্ণ ডাটা রিসেট ও ব্যাকআপ ডাউনলোড/রিস্টোর।</span>
                </div>
            </div>
            @endif

            @if($user->isMessenger())
            <div class="p-4 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 flex items-start gap-3">
                <span class="text-xl">🛵</span>
                <div>
                    <span class="text-xs font-black text-emerald-950 dark:text-emerald-300 block">বাজার খরচ ও মেমো সাবমিশন</span>
                    <span class="text-[11px] text-emerald-800 dark:text-emerald-400">ক্যামেরা দিয়ে মেমোর ছবি তুলে ১-ক্লিকে হোয়াটসঅ্যাপে পাঠানো।</span>
                </div>
            </div>
            @endif

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                <span class="text-xl">✅</span>
                <div>
                    <span class="text-xs font-black text-slate-900 dark:text-white block">সামগ্রিক বাজার রিপোর্ট ও চেকলিস্ট</span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">তারিখ রেঞ্জ অনুযায়ী বাজার তালিকা ও শপিং চেকলিস্ট ভিউ।</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
