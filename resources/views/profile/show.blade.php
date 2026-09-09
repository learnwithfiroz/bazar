@extends('layouts.app')

@section('title', 'আমার প্রোফাইল ও সেটিংস - ' . $user->name)

@section('content')
<div class="max-w-7xl mx-auto space-y-5 sm:space-y-6" x-data="{ 
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
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 rounded-3xl p-5 sm:p-7 lg:p-8 text-white shadow-2xl relative overflow-hidden border border-slate-800">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-0 right-1/3 w-48 h-48 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row items-center lg:items-start justify-between gap-6 relative z-10 text-center lg:text-left">
            
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 flex-grow">
                <!-- Avatar Display with Live Status -->
                <div class="relative group flex-shrink-0">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-slate-800/90 backdrop-blur-md border-2 border-indigo-400/50 p-1.5 flex items-center justify-center shadow-2xl overflow-hidden ring-4 ring-white/10">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" class="w-full h-full object-cover rounded-2xl">
                        </template>
                        <template x-if="!avatarPreview">
                            <span class="text-4xl sm:text-5xl font-black text-indigo-300">{{ mb_substr($user->name, 0, 1) }}</span>
                        </template>
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 border-2 border-slate-950 rounded-full ring-2 ring-emerald-400/50 flex items-center justify-center text-[10px] text-white font-black" title="অ্যাক্টিভ ও ভেরিফাইড">✓</span>
                </div>

                <!-- Profile Info Body -->
                <div class="space-y-3 flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 justify-center sm:justify-start flex-wrap">
                        <h1 class="text-lg sm:text-2xl lg:text-2xl font-black tracking-tight text-white leading-tight">{{ $user->name }}</h1>
                        
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black w-fit mx-auto sm:mx-0 shadow-sm
                            {{ $user->isPrincipal() ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-purple-500/30' : '' }}
                            {{ $user->isPA() ? 'bg-gradient-to-r from-blue-500 to-cyan-600 text-white shadow-blue-500/30' : '' }}
                            {{ $user->isMessenger() ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-emerald-500/30' : '' }}
                            {{ $user->isFamily() ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-amber-500/30' : '' }}
                        ">
                            {{ $user->isPrincipal() ? '👑 ' . $user->role_display_name : ($user->isPA() ? '💼 ' . $user->role_display_name : ($user->isMessenger() ? '🛵 ' . $user->role_display_name : '🏡 ' . $user->role_display_name)) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs text-slate-300">
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
            </div>

            <!-- Messenger Wallet Pill or Admin Budget Status -->
            <div class="flex-shrink-0 w-full sm:w-auto flex justify-center lg:justify-end">
                @if($user->isMessenger() && $userWallet)
                <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md px-6 py-4 rounded-3xl border border-white/20 text-center sm:text-right shadow-xl">
                    <span class="text-[10px] text-emerald-300 uppercase font-black tracking-wider block">হাতে নগদ ওয়ালেট ব্যালেন্স</span>
                    <span class="text-2xl sm:text-3xl font-black text-emerald-400 mt-0.5 block">৳ {{ number_format($userWallet->current_balance, 2) }}</span>
                </div>
                @else
                <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-md px-6 py-4 rounded-3xl border border-white/20 text-center sm:text-right shadow-xl">
                    <span class="text-[10px] text-indigo-300 uppercase font-black tracking-wider block">নিরাপত্তা ও এনক্রিপশন</span>
                    <span class="text-xl font-black text-emerald-400 mt-0.5 block flex items-center justify-center sm:justify-end gap-1.5">
                        <span>🛡️</span>
                        <span>সক্রিয় ও সুরক্ষিত</span>
                    </span>
                </div>
                @endif
            </div>

        </div>

    </div>

    <!-- 4 Balanced Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>মোট এন্ট্রি</span>
                <span class="text-base">📝</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $userExpensesCount }} টি</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">বাজার খরচের ভাউচার</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>মোট বাজার খরচ</span>
                <span class="text-base">💰</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-indigo-700 dark:text-indigo-400">৳ {{ number_format($userExpensesTotal, 2) }}</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">সর্বমোট সাবমিটকৃত বিল</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>মাসিক বাজেট লিমিট</span>
                <span class="text-base">🎯</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400">৳ {{ number_format($monthlyBudgetLimit, 2) }}</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">মাসের সর্বোচ্চ সিলিং</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1">
            <div class="flex justify-between items-center text-[10px] font-black uppercase text-slate-400">
                <span>অ্যাকাউন্ট স্ট্যাটাস</span>
                <span class="text-base">🛡️</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600">ভেরিফাইড</div>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium block">সিস্টেম অথরাইজড মেম্বার</span>
        </div>
    </div>

    <!-- Desktop Master-Detail Layout (Two-Column on >= lg screens) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
        
        <!-- Left Column: Vertical Navigation Sidebar (Sticky on Desktop) -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-4 lg:sticky lg:top-20">
            <div class="bg-white dark:bg-slate-900 p-3 sm:p-4 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-1.5">
                <span class="text-[10px] font-black uppercase text-slate-400 px-3 py-1.5 block tracking-wider">মেনু ও সেটিংস</span>

                <button type="button" @click="activeTab = 'info'" :class="activeTab === 'info' ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/30' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-700'" class="w-full px-4 py-3 rounded-2xl text-xs transition flex items-center justify-between group text-left">
                    <span class="flex items-center gap-2.5">
                        <span class="text-base">👤</span>
                        <span>ব্যক্তিগত তথ্য ও ছবি</span>
                    </span>
                    <span :class="activeTab === 'info' ? 'text-white' : 'text-slate-400'" class="text-xs group-hover:translate-x-0.5 transition-transform">➔</span>
                </button>
                
                <button type="button" @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/30' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-700'" class="w-full px-4 py-3 rounded-2xl text-xs transition flex items-center justify-between group text-left">
                    <span class="flex items-center gap-2.5">
                        <span class="text-base">🔐</span>
                        <span>পাসওয়ার্ড ও নিরাপত্তা</span>
                    </span>
                    <span :class="activeTab === 'security' ? 'text-white' : 'text-slate-400'" class="text-xs group-hover:translate-x-0.5 transition-transform">➔</span>
                </button>

                @if($user->isPrincipal() || $user->isPA())
                <button type="button" @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/30' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-700'" class="w-full px-4 py-3 rounded-2xl text-xs transition flex items-center justify-between group text-left">
                    <span class="flex items-center gap-2.5">
                        <span class="text-base">⚙️</span>
                        <span>সিস্টেম ব্যাকআপ ও সেটিংস</span>
                    </span>
                    <span :class="activeTab === 'settings' ? 'text-white' : 'text-slate-400'" class="text-xs group-hover:translate-x-0.5 transition-transform">➔</span>
                </button>
                @endif

                <button type="button" @click="activeTab = 'activity'" :class="activeTab === 'activity' ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/30' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-700'" class="w-full px-4 py-3 rounded-2xl text-xs transition flex items-center justify-between group text-left">
                    <span class="flex items-center gap-2.5">
                        <span class="text-base">📊</span>
                        <span>অ্যাক্টিভিটি ও পারমিশন</span>
                    </span>
                    <span :class="activeTab === 'activity' ? 'text-white' : 'text-slate-400'" class="text-xs group-hover:translate-x-0.5 transition-transform">➔</span>
                </button>
            </div>

            <!-- Quick Logout Button on Sidebar -->
            <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি অ্যাকাউন্ট থেকে লগআউট করতে চান?');">
                @csrf
                <button type="submit" class="w-full py-3 px-4 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60 rounded-2xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <span>🚪</span>
                    <span>অ্যাকাউন্ট থেকে লগআউট</span>
                </button>
            </form>
        </div>

        <!-- Right Column: Detail Form & Content Area -->
        <div class="lg:col-span-8 xl:col-span-9">

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
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block">JPG, PNG বা WebP ফরম্যাট (সর্বোচ্চ ১০ মেগাবাইট)</span>
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
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">নতুন পাসওয়ার্ড * (সর্বনিম্ন ৬ অক্ষর)</label>
                        <div class="relative">
                            <input :type="showNewPass ? 'text' : 'password'" name="new_password" x-model="newPasswordValue" required placeholder="নতুন শক্তিশালী পাসওয়ার্ড লিখুন" class="w-full px-4 py-3 pr-12 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                            <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-700 text-xs">
                                <span x-text="showNewPass ? '🙈 লুকান' : '👁️ দেখুন'"></span>
                            </button>
                        </div>

                        <!-- Live Strength Meter -->
                        <div class="mt-2 space-y-1" x-show="newPasswordValue.length > 0">
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full transition-all duration-300" :style="`width: ${getPasswordStrength()}%;`" :class="{
                                    'bg-rose-500': getPasswordStrength() < 50,
                                    'bg-amber-500': getPasswordStrength() >= 50 && getPasswordStrength() < 80,
                                    'bg-emerald-500': getPasswordStrength() >= 80
                                }"></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold">
                                <span class="text-slate-400">পাসওয়ার্ডের শক্তি:</span>
                                <span :class="{
                                    'text-rose-500': getPasswordStrength() < 50,
                                    'text-amber-500': getPasswordStrength() >= 50 && getPasswordStrength() < 80,
                                    'text-emerald-500': getPasswordStrength() >= 80
                                }" x-text="getPasswordStrength() < 50 ? 'দুর্বল (Weak)' : (getPasswordStrength() < 80 ? 'মাঝারি (Medium)' : 'খুব শক্তিশালী (Strong)')"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">নতুন পাসওয়ার্ড নিশ্চিত করুন *</label>
                        <div class="relative">
                            <input :type="showConfirmPass ? 'text' : 'password'" name="new_password_confirmation" required placeholder="নতুন পাসওয়ার্ডটি পুনরায় লিখুন" class="w-full px-4 py-3 pr-12 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm focus:ring-2 focus:ring-purple-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                            <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3.5 top-3.5 text-slate-400 hover:text-slate-700 text-xs">
                                <span x-text="showConfirmPass ? '🙈 লুকান' : '👁️ দেখুন'"></span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-black rounded-2xl text-xs sm:text-sm shadow-xl shadow-purple-600/25 transition active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>🔒 পাসওয়ার্ড আপডেট করুন</span>
                        <span>➔</span>
                    </button>
                </form>
            </div>

            <!-- Tab 3: System Backup, Settings & Reset -->
            @if($user->isPrincipal() || $user->isPA())
            <div x-show="activeTab === 'settings'" x-cloak class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-6">
                
                <!-- Section 1: Monthly Budget Setting -->
                <div class="space-y-3 border-b border-slate-100 dark:border-slate-800 pb-6">
                    <div class="space-y-0.5">
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>🎯</span>
                            <span>মাসিক বাজেট সিলিং ও সতর্কতা সীমা</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">প্রতি মাসে মোট খরচের সর্বোচ্চ লক্ষ্যমাত্রা নির্ধারণ করুন</p>
                    </div>

                    <form action="{{ route('system.budget') }}" method="POST" class="flex flex-col sm:flex-row gap-3 pt-2">
                        @csrf
                        <div class="relative flex-grow">
                            <span class="absolute left-4 top-3 text-slate-400 text-xs font-bold">৳</span>
                            <input type="number" step="100" name="monthly_budget_limit" value="{{ $monthlyBudgetLimit }}" required class="w-full pl-9 pr-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm font-black focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                        </div>
                        <button type="submit" class="py-3 px-6 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-xs transition shadow-sm whitespace-nowrap">
                            💾 বাজেট আপডেট
                        </button>
                    </form>
                </div>

                <!-- Section 2: 1-Click Database Backup Download -->
                <div class="space-y-3 border-b border-slate-100 dark:border-slate-800 pb-6">
                    <div class="space-y-0.5">
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>💾</span>
                            <span>১-ক্লিক ডাটাবেজ ব্যাকআপ ডাউনলোড</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">আপনার সকল হিসাব, ভাউচার ও ট্রানজেকশনের সম্পূর্ণ ডাটাবেজ ব্যাকআপ ফাইল সেভ করে রাখুন</p>
                    </div>

                    <div class="p-4 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-2xl border border-indigo-100 dark:border-indigo-900/60 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <span class="text-xs font-bold text-indigo-950 dark:text-indigo-200 block">পূর্ণাঙ্গ সিস্টেম ডাটাবেজ ফাইল</span>
                            <span class="text-[11px] text-indigo-700 dark:text-indigo-400">ইউজার, খরচ, ভাউচার ও ওয়ালেট ডাটা সহ</span>
                        </div>
                        <a href="{{ route('system.backup') }}" class="py-2.5 px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-md shadow-indigo-600/30 flex items-center gap-2 transition">
                            <span>📥 ব্যাকআপ ডাউনলোড</span>
                            <span>➔</span>
                        </a>
                    </div>
                </div>

                <!-- Section 3: Restore / Upload Backup File -->
                @if($user->isPrincipal())
                <div class="space-y-3 border-b border-slate-100 dark:border-slate-800 pb-6">
                    <div class="space-y-0.5">
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>📂</span>
                            <span>ব্যাকআপ ফাইল আপলোড ও রিস্টোর</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">পূর্বের ব্যাকআপ ফাইল থেকে সম্পূর্ণ ডাটাবেজ রিস্টোর করুন</p>
                    </div>

                    <form action="{{ route('system.backup-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3" onsubmit="return confirm('সতর্কতা: ব্যাকআপ ফাইল আপলোড করলে বর্তমান ডাটা পরিবর্তিত হবে। আপনি কি নিশ্চিত?');">
                        @csrf
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700">
                            <input type="file" name="backup_file" required accept=".sqlite,.sql,.db" class="w-full text-xs text-slate-700 dark:text-slate-300">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">নিরাপত্তা নিশ্চিত করতে সুপার এডমিনের পাসওয়ার্ড লিখুন *</label>
                            <input type="password" name="admin_password" required placeholder="সুপার এডমিনের পাসওয়ার্ড" class="w-full px-4 py-2.5 rounded-2xl border border-slate-300 dark:border-slate-700 text-xs sm:text-sm bg-slate-50/50 dark:bg-slate-800 dark:text-white">
                        </div>

                        <button type="submit" class="py-3 px-6 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-xs transition shadow-sm flex items-center gap-2">
                            <span>🚀 আপলোড ও রিস্টোর করুন</span>
                        </button>
                    </form>
                </div>

                <!-- Section 4: Emergency Full System Reset -->
                <div class="p-5 bg-rose-50/80 dark:bg-rose-950/30 rounded-3xl border border-rose-200 dark:border-rose-900/60 space-y-4">
                    <div class="space-y-1">
                        <span class="text-xs sm:text-sm font-black text-rose-900 dark:text-rose-300 flex items-center gap-2">
                            <span>⚠️</span>
                            <span>জরুরি সিস্টেম রিসেট (Super Admin Only)</span>
                        </span>
                        <p class="text-[11px] text-rose-700 dark:text-rose-400">সমস্ত খরচ, ক্যাশ মেমোর ছবি এবং মেসেঞ্জারের লেনদেন মুছে সিস্টেম ফ্রেশ করা হবে। ইউজার অ্যাকাউন্ট ও পাসওয়ার্ড অপরিবর্তিত থাকবে।</p>
                    </div>

                    <form action="{{ route('system.reset-all') }}" method="POST" onsubmit="return confirm('⚠️ চূড়ান্ত সতর্কতা: আপনি কি নিশ্চিত যে আপনি সমস্ত খরচের ডাটা মুছে সিস্টেম রিসেট করতে চান? এই কাজটি রিভার্স করা যাবে না!');" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-rose-900 dark:text-rose-300 uppercase mb-1">রিসেট কনফার্ম করতে সুপার এডমিনের পাসওয়ার্ড লিখুন *</label>
                            <input type="password" name="admin_password" required placeholder="সুপার এডমিনের পাসওয়ার্ড" class="w-full px-4 py-2.5 rounded-2xl border border-rose-300 dark:border-rose-800 text-xs sm:text-sm focus:ring-2 focus:ring-rose-500 bg-white dark:bg-slate-900 dark:text-white">
                        </div>

                        <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl text-xs shadow-lg shadow-rose-600/30 transition active:scale-[0.99] flex items-center justify-center gap-2">
                            <span>🗑️ সমস্ত বাজার ডাটা মুছে সিস্টেম রিসেট করুন</span>
                        </button>
                    </form>
                </div>
                @endif

            </div>
            @endif

            <!-- Tab 4: Activity & Permissions Matrix -->
            <div x-show="activeTab === 'activity'" x-cloak class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 flex justify-between items-center">
                    <div class="space-y-0.5">
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>📊</span>
                            <span>অ্যাকাউন্ট পারমিশন ও অ্যাক্টিভিটি ম্যাট্রিক্স</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">আপনার রোলের আওতাধীন সিস্টেম ক্ষমতা ও নিরাপত্তা লগের বিবরণ</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">রোল অ্যাক্সেস পারমিশন:</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">📊 ড্যাশবোর্ড ও এনালিটিক্স ভিউ</span>
                            <span class="text-emerald-600 font-black">অনুমোদিত ✓</span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">🛍️ বাজার ডিমান্ড তৈরি</span>
                            <span class="text-emerald-600 font-black">অনুমোদিত ✓</span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">🔎 ভাউচার পরীক্ষণ ও অনুমোদন</span>
                            <span class="{{ $user->canManageFunds() ? 'text-emerald-600' : 'text-slate-400' }} font-black">
                                {{ $user->canManageFunds() ? 'অনুমোদিত ✓' : 'অনুমতি নেই ✕' }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">💳 ওয়ালেট ফান্ড টপ-আপ</span>
                            <span class="{{ $user->canManageFunds() ? 'text-emerald-600' : 'text-slate-400' }} font-black">
                                {{ $user->canManageFunds() ? 'অনুমোদিত ✓' : 'অনুমতি নেই ✕' }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">👥 ইউজার ও মেম্বার ম্যানেজমেন্ট</span>
                            <span class="{{ $user->isPrincipal() ? 'text-emerald-600' : 'text-slate-400' }} font-black">
                                {{ $user->isPrincipal() ? 'অনুমোদিত ✓' : 'অনুমতি নেই ✕' }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <span class="font-bold text-slate-700 dark:text-slate-200">⚙️ সিস্টেম ব্যাকআপ ও ডাটা রিসেট</span>
                            <span class="{{ $user->isPrincipal() ? 'text-emerald-600' : 'text-slate-400' }} font-black">
                                {{ $user->isPrincipal() ? 'অনুমোদিত ✓' : 'অনুমতি নেই ✕' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection
