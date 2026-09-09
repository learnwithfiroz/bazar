<!DOCTYPE html>
<html lang="bn" class="h-full" x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark',
    notifOpen: false,
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
    }
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#15803d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Daily Expense & Fund Management') - বাজার ও ফান্ড</title>
    
    <!-- SolaimanLipi & Google Fonts (SolaimanLipi, Hind Siliguri, Plus Jakarta Sans) -->
    <link rel="stylesheet" href="https://fonts.maateen.me/solaiman-lipi/font.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Chart.js CDN for Visual Interactive Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Tailwind CSS CDN with Dark Mode Support -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"SolaimanLipi"', '"Hind Siliguri"', '"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(0, 0, 0, 0.05)',
                        'card': '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02)',
                        'glow': '0 0 20px rgba(34, 197, 94, 0.25)',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'SolaimanLipi', 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        [x-cloak] { display: none !important; }
        
        .touch-scroll {
            -webkit-overflow-scrolling: touch;
        }

        .pb-safe {
            padding-bottom: calc(5rem + env(safe-area-inset-bottom, 16px));
        }

        /* Subtle scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Security Watermark when inspecting attempt */
        .user-select-none {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body class="bg-slate-100/80 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col antialiased selection:bg-brand-500 selection:text-white transition-colors duration-300">

    @php
        $navPendingReviews = \App\Models\Expense::where('status', 'SUBMITTED')->count();
        $navPendingFunds = \App\Models\FundRequest::where('status', 'PENDING')->count();
        $navActiveDemands = \App\Models\BazaarDemand::whereIn('status', ['PENDING', 'IN_PROGRESS'])->count();
        $navTotalNotifs = $navPendingReviews + $navPendingFunds + $navActiveDemands;
    @endphp

    <!-- Top Sticky Header -->
    <header class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800 sticky top-0 z-40 shadow-sm transition">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-3">
                
                <!-- Logo & Brand -->
                <div class="flex items-center flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-tr from-brand-700 via-brand-600 to-emerald-500 flex items-center justify-center text-white font-black text-lg sm:text-xl shadow-md shadow-brand-600/30 group-hover:scale-105 transition flex-shrink-0">
                            🛒
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white leading-none tracking-tight whitespace-nowrap group-hover:text-brand-700 dark:group-hover:text-brand-400 transition">
                                ডেইলি বাজার ও ফান্ড
                            </span>
                            <span class="text-[9px] sm:text-[10px] text-slate-400 dark:text-slate-500 font-bold block whitespace-nowrap mt-1 leading-none">
                                Executive Expense Management
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Center Navigation -->
                @auth
                <nav class="hidden lg:flex items-center space-x-1 xl:space-x-1.5 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700 border border-brand-200/80 shadow-sm dark:bg-brand-950/50 dark:text-brand-300 dark:border-brand-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>📊</span>
                        <span>ড্যাশবোর্ড</span>
                    </a>
                    
                    <!-- Pre-Bazaar Demand / Shopping List -->
                    <a href="{{ route('bazaar-demands.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('bazaar-demands.*') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/80 shadow-sm dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>🛍️</span>
                        <span>শপিং লিস্ট</span>
                    </a>

                    @if(auth()->user()->isMessenger())
                    <a href="{{ route('expenses.create') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-brand-600 to-emerald-600 text-white hover:from-brand-700 hover:to-emerald-700 shadow-md shadow-brand-600/25 flex items-center gap-1.5 transition active:scale-[0.98] whitespace-nowrap">
                        <span>➕</span>
                        <span>খরচ এন্ট্রি</span>
                    </a>
                    @endif

                    <!-- Dedicated Voucher Wise Bill Check Link -->
                    @if(auth()->user()->canManageFunds() || auth()->user()->isPrincipal())
                    <a href="{{ route('expenses.voucher-check') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('expenses.voucher-check') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/80 shadow-sm dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>🔎</span>
                        <span>ভাউচার চেক</span>
                    </a>
                    @endif

                    <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('expenses.index') ? 'bg-brand-50 text-brand-700 border border-brand-200/80 shadow-sm dark:bg-brand-950/50 dark:text-brand-300 dark:border-brand-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>📝</span>
                        <span>খরচ তালিকা</span>
                    </a>

                    @if(!auth()->user()->isFamily())
                    <a href="{{ route('wallets.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('wallets.*') ? 'bg-brand-50 text-brand-700 border border-brand-200/80 shadow-sm dark:bg-brand-950/50 dark:text-brand-300 dark:border-brand-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>💳</span>
                        <span>ওয়ালেট</span>
                    </a>
                    @endif

                    <a href="{{ route('reports.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('reports.*') ? 'bg-brand-50 text-brand-700 border border-brand-200/80 shadow-sm dark:bg-brand-950/50 dark:text-brand-300 dark:border-brand-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>📈</span>
                        <span>রিপোর্ট</span>
                    </a>

                    @if(auth()->user()->isPrincipal())
                    <a href="{{ route('users.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('users.*') ? 'bg-purple-50 text-purple-700 border border-purple-200/80 shadow-sm dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 hover:bg-slate-100/80 dark:hover:bg-slate-800' }}">
                        <span>👥</span>
                        <span>ইউজার্স</span>
                    </a>
                    @endif
                </nav>
                @endauth

                <!-- User Profile Chip, Notifications, Dark Mode & Logout Button -->
                <div class="flex items-center space-x-2 sm:space-x-2.5 flex-shrink-0">
                    
                    <!-- Dark Mode Toggle Button -->
                    <button @click="toggleTheme()" title="থিম পরিবর্তন করুন (লাইট / ডার্ক)" class="p-2 sm:p-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-amber-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm flex items-center justify-center">
                        <span x-text="darkMode ? '☀️' : '🌙'"></span>
                    </button>

                    @auth
                    <!-- In-App Notification Center Bell -->
                    <div class="relative">
                        <button @click="notifOpen = !notifOpen" title="নোটিফিকেশন সেন্টার" class="relative p-2 sm:p-2.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm flex items-center justify-center">
                            <span>🔔</span>
                            @if($navTotalNotifs > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-rose-600 text-white rounded-full text-[10px] font-black flex items-center justify-center shadow-md animate-pulse">
                                {{ $navTotalNotifs }}
                            </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak class="absolute right-0 mt-2 w-72 sm:w-80 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-4 space-y-3 z-50">
                            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-2.5">
                                <h3 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>🔔</span>
                                    <span>নোটিফিকেশন সেন্টার</span>
                                </h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $navTotalNotifs }} টি নতুন</span>
                            </div>

                            <div class="space-y-2 text-xs">
                                @if($navPendingReviews > 0)
                                <a href="{{ route('expenses.voucher-check', ['status' => 'SUBMITTED']) }}" @click="notifOpen = false" class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex items-center justify-between block hover:bg-amber-100 transition">
                                    <span class="font-bold text-amber-900 dark:text-amber-300">⏳ {{ $navPendingReviews }} টি ভাউচার রিভিউ পেন্ডিং</span>
                                    <span class="text-[10px] font-black text-amber-700">চেক ➔</span>
                                </a>
                                @endif

                                @if($navPendingFunds > 0)
                                <a href="{{ route('wallets.index') }}" @click="notifOpen = false" class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 flex items-center justify-between block hover:bg-rose-100 transition">
                                    <span class="font-bold text-rose-900 dark:text-rose-300">🚨 {{ $navPendingFunds }} টি ফান্ড রিকোয়েস্ট পেন্ডিং</span>
                                    <span class="text-[10px] font-black text-rose-700">দেখুন ➔</span>
                                </a>
                                @endif

                                @if($navActiveDemands > 0)
                                <a href="{{ route('bazaar-demands.index') }}" @click="notifOpen = false" class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-900/60 flex items-center justify-between block hover:bg-indigo-100 transition">
                                    <span class="font-bold text-indigo-900 dark:text-indigo-300">🛍️ {{ $navActiveDemands }} টি বাজার শপিং লিস্ট চালু</span>
                                    <span class="text-[10px] font-black text-indigo-700">লিস্ট ➔</span>
                                </a>
                                @endif

                                @if($navTotalNotifs === 0)
                                <div class="py-4 text-center text-slate-400 text-xs">
                                    কোনো নতুন নোটিফিকেশন নেই।
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Profile Link Button -->
                    <a href="{{ route('profile.show') }}" title="প্রোফাইল সেটিংস" class="flex items-center gap-2 px-2.5 py-1.5 sm:px-3 sm:py-1.5 rounded-2xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 shadow-sm text-xs transition group flex-shrink-0">
                        <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white flex items-center justify-center text-[11px] font-black shadow-inner flex-shrink-0">
                            @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover rounded-xl">
                            @else
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="text-left hidden sm:block">
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-[11px] leading-tight group-hover:text-brand-700 dark:group-hover:text-brand-400 whitespace-nowrap">{{ auth()->user()->name }}</span>
                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 block whitespace-nowrap">{{ auth()->user()->role_display_name }}</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold sm:hidden whitespace-nowrap
                            {{ auth()->user()->isPrincipal() ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ auth()->user()->isPA() ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ auth()->user()->isMessenger() ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ auth()->user()->isFamily() ? 'bg-amber-100 text-amber-700' : '' }}
                        ">
                            {{ auth()->user()->isPrincipal() ? '👑 এডমিন' : (auth()->user()->isPA() ? '💼 পিএ' : (auth()->user()->isMessenger() ? '🛵 মেসেঞ্জার' : '🏡 ফ্যামিলি')) }}
                        </span>
                        <span class="text-slate-400 text-xs group-hover:text-brand-600">⚙️</span>
                    </a>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="inline flex-shrink-0" onsubmit="return confirm('আপনি কি নিশ্চিত যে আপনি অ্যাকাউন্ট থেকে লগআউট করতে চান?');">
                        @csrf
                        <button type="submit" title="লগআউট করুন" class="flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-2xl bg-gradient-to-r from-rose-50 to-red-50 hover:from-rose-100 hover:to-red-100 text-rose-700 hover:text-rose-800 border border-rose-200/80 hover:border-rose-300 text-xs font-bold shadow-sm transition active:scale-95 group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-rose-600 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline whitespace-nowrap">লগআউট</span>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-2xl bg-brand-600 text-white text-xs sm:text-sm font-bold hover:bg-brand-700 shadow-md shadow-brand-600/25 whitespace-nowrap flex-shrink-0">
                        লগইন
                    </a>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Notification Toast Messages -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 mt-3 w-full">
        @if(session('success'))
        <div class="bg-gradient-to-r from-emerald-500/10 via-emerald-50 to-teal-50 border border-emerald-300 text-emerald-900 p-3.5 sm:p-4 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 shadow-sm mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-black shadow-md shadow-emerald-600/30">
                    ✓
                </div>
                <span class="text-xs sm:text-sm font-bold">{{ session('success') }}</span>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                @if(session('whatsapp_url'))
                <a href="{{ session('whatsapp_url') }}" target="_blank" class="w-full sm:w-auto text-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/30 transition">
                    <span>💬 WhatsApp এ পাঠান</span>
                </a>
                @endif
                @if(session('whatsapp_fund_url'))
                <a href="{{ session('whatsapp_fund_url') }}" target="_blank" class="w-full sm:w-auto text-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/30 transition">
                    <span>💬 WhatsApp এ আবেদন পাঠান</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 sm:p-4 rounded-2xl flex items-center gap-2.5 shadow-sm mb-3">
            <span class="text-lg">⚠️</span>
            <span class="text-xs sm:text-sm font-bold">{{ session('warning') }}</span>
        </div>
        @endif

        @if(!empty($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-900 p-3.5 sm:p-4 rounded-2xl shadow-sm mb-3">
            <div class="font-bold text-xs uppercase tracking-wider mb-1 flex items-center gap-1.5">
                <span>🚫</span>
                <span>ভুল তথ্য পাওয়া গেছে:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5 font-medium text-rose-800">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Main Body Container -->
    <main class="flex-grow max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 sm:py-6 w-full pb-safe">
        @yield('content')
    </main>

    <!-- App-Like Mobile Bottom Navigation Bar -->
    @auth
    <div class="md:hidden fixed bottom-3 left-3 right-3 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl border border-slate-200/90 dark:border-slate-800 px-2 py-1.5 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)]">
        <div class="grid grid-cols-5 items-center text-center">
            
            <!-- Home / Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl transition {{ request()->routeIs('dashboard') ? 'text-brand-700 dark:text-brand-400 font-black' : 'text-slate-400 font-medium hover:text-slate-700' }}">
                <span class="text-lg">📊</span>
                <span class="text-[10px] mt-0.5">হোম</span>
            </a>

            <!-- Bazaar Demands / Shopping List -->
            <a href="{{ route('bazaar-demands.index') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl transition {{ request()->routeIs('bazaar-demands.*') ? 'text-indigo-700 dark:text-indigo-400 font-black' : 'text-slate-400 font-medium hover:text-slate-700' }}">
                <span class="text-lg">🛍️</span>
                <span class="text-[10px] mt-0.5">লিস্ট</span>
            </a>

            <!-- Add Expense (Messenger) or Voucher Check (Manager) or Expense List -->
            @if(auth()->user()->isMessenger())
            <a href="{{ route('expenses.create') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl {{ request()->routeIs('expenses.create') ? 'text-brand-700 dark:text-brand-400 font-black' : 'text-brand-600 font-bold' }}">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-500 text-white flex items-center justify-center -mt-5 shadow-lg shadow-brand-600/40 text-base border-2 border-white">
                    ➕
                </div>
                <span class="text-[10px] mt-0.5">খরচ</span>
            </a>
            @elseif(auth()->user()->canManageFunds())
            <a href="{{ route('expenses.voucher-check') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl transition {{ request()->routeIs('expenses.voucher-check') ? 'text-indigo-700 dark:text-indigo-400 font-black' : 'text-slate-400 font-medium hover:text-slate-700' }}">
                <span class="text-lg">🔎</span>
                <span class="text-[10px] mt-0.5">ভাউচার</span>
            </a>
            @else
            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl transition {{ request()->routeIs('expenses.index') ? 'text-brand-700 dark:text-brand-400 font-black' : 'text-slate-400 font-medium hover:text-slate-700' }}">
                <span class="text-lg">📝</span>
                <span class="text-[10px] mt-0.5">তালিকা</span>
            </a>
            @endif

            <!-- Reports & Print -->
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl transition {{ request()->routeIs('reports.*') ? 'text-brand-700 dark:text-brand-400 font-black' : 'text-slate-400 font-medium hover:text-slate-700' }}">
                <span class="text-lg">📈</span>
                <span class="text-[10px] mt-0.5">রিপোর্ট</span>
            </a>

            <!-- Profile, Settings & 1-Tap Logout on Mobile -->
            <a href="{{ route('profile.show') }}" class="flex flex-col items-center py-1 px-1 rounded-2xl transition {{ request()->routeIs('profile.*') ? 'text-purple-700 dark:text-purple-400 font-black' : 'text-slate-400 font-medium hover:text-slate-700' }}">
                <span class="text-lg">⚙️</span>
                <span class="text-[10px] mt-0.5">প্রোফাইল</span>
            </a>

        </div>
    </div>
    @endauth

    <!-- Desktop Footer with System IP & Security Tag -->
    <footer class="hidden md:block bg-white/60 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-800 py-6 text-center text-xs text-slate-500 dark:text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
            <p>© {{ date('Y') }} Daily Expense & Fund Management SaaS System. Designed for Principal & Executive Households.</p>
            <div class="flex items-center gap-3 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                <span>🛡️ SSL Secured</span>
                <span>⏱️ {{ date('h:i A') }}</span>
                <span>🌐 IP: {{ request()->ip() }}</span>
            </div>
        </div>
    </footer>

    <!-- Comprehensive Anti-Inspect & Anti-F12 Security Protection Script -->
    <script>
        // Disable Context Menu (Right-Click)
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Block Developer Tools Shortcuts (F12, Ctrl+Shift+I/J/C, Ctrl+U, Cmd+Option+I/J/C/U)
        document.addEventListener('keydown', function(e) {
            // F12 or keyCode 123
            if (e.key === 'F12' || e.keyCode === 123) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // Ctrl+U (View Source) or Ctrl+S (Save Page)
            if ((e.ctrlKey || e.metaKey) && (e.key === 'U' || e.key === 'u' || e.key === 'S' || e.key === 's')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, true);
    </script>

    @stack('scripts')
</body>
</html>
