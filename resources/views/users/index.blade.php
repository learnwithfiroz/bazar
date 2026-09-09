@extends('layouts.app')

@section('title', 'ইউজার ম্যানেজমেন্ট ও এক্সেস কন্ট্রোল')

@section('content')
<div class="space-y-5 sm:space-y-6" x-data="{ 
    createUserModal: false, 
    editUserModal: false, 
    searchQuery: '',
    selectedRoleFilter: 'all',
    userRole: 'messenger',
    editUser: { id: '', name: '', phone_number: '', email: '', role: 'messenger', is_active: 1, wallet_balance: 0, low_balance_limit: 500 },
    openEdit(u) {
        this.editUser = { 
            id: u.id, 
            name: u.name, 
            phone_number: u.phone_number, 
            email: u.email || '', 
            role: u.role,
            is_active: u.is_active ? 1 : 0,
            wallet_balance: (u.wallet && u.wallet.current_balance !== undefined) ? u.wallet.current_balance : 0,
            low_balance_limit: (u.wallet && u.wallet.low_balance_alert_limit !== undefined) ? u.wallet.low_balance_alert_limit : 500
        };
        this.editUserModal = true;
    },
    matchesFilter(user) {
        const matchesRole = this.selectedRoleFilter === 'all' || user.role === this.selectedRoleFilter;
        const q = this.searchQuery.toLowerCase();
        const matchesSearch = !q || 
            (user.name && user.name.toLowerCase().includes(q)) || 
            (user.phone_number && user.phone_number.includes(q)) || 
            (user.email && user.email.toLowerCase().includes(q));
        return matchesRole && matchesSearch;
    }
}">
    
    <!-- Top Hero Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-purple-950 text-white p-5 sm:p-7 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="space-y-1 relative z-10">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-purple-500/30 text-purple-300 text-[10px] font-bold border border-purple-400/30">
                    👑 সুপার এডমিন কন্ট্রোল সেন্টার
                </span>
                <span class="text-xs text-slate-300">👥 মোট সদস্য: {{ $totalUsers }} জন</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">ইউজার ম্যানেজমেন্ট ও এক্সেস কন্ট্রোল</h1>
            <p class="text-xs text-slate-300">সকল মেম্বারের রোল, লগইন ক্রেডেনশিয়াল, ওয়ালেট ব্যালেন্স এবং অনুমতি ব্যবস্থাপনা</p>
        </div>

        <button @click="createUserModal = true" class="w-full sm:w-auto py-3 px-5 bg-gradient-to-r from-brand-600 to-emerald-600 hover:from-brand-700 hover:to-emerald-700 text-white text-xs font-black rounded-2xl shadow-lg shadow-brand-600/30 flex items-center justify-center gap-2 transition active:scale-[0.98] relative z-10">
            <span>➕ নতুন ইউজার তৈরি করুন</span>
        </button>
    </div>

    <!-- Informative 4 KPI Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Total Members -->
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">মোট ইউজার</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 font-black text-sm flex items-center justify-center">👥</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalUsers }} জন</div>
            <span class="text-[10px] text-emerald-600 font-bold block">সচল: {{ $activeUsersCount }} জন</span>
        </div>

        <!-- Messengers & Total Balance Float -->
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">মেসেঞ্জার ওয়ালেট সঞ্চিত</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 font-black text-sm flex items-center justify-center">🛵</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700">৳ {{ number_format($totalMessengerBalance, 2) }}</div>
            <span class="text-[10px] text-slate-500 font-semibold block">{{ $messengerCount }} জন মেসেঞ্জার সক্রিয়</span>
        </div>

        <!-- PA / Managers -->
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">পিএ / ম্যানেজার</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 font-black text-sm flex items-center justify-center">💼</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-blue-700">{{ $paCount }} জন</div>
            <span class="text-[10px] text-slate-400 block">ভেরিফিকেশন ও রিভিউ</span>
        </div>

        <!-- Family Viewers -->
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">পারিবারিক সদস্য</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 font-black text-sm flex items-center justify-center">🏡</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-800">{{ $familyCount }} জন</div>
            <span class="text-[10px] text-slate-400 block">রিড-ওনলি হিসাব ভিউয়ার</span>
        </div>

    </div>

    <!-- Search & Role Filter Tabs (Optimized for 10,000+ Users) -->
    <div class="bg-white p-3 sm:p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-3 justify-between items-center">
        <!-- Server-Side Search Form -->
        <form method="GET" action="{{ route('users.index') }}" class="relative w-full md:w-80 flex items-center gap-2">
            @if(request('role'))
            <input type="hidden" name="role" value="{{ request('role') }}">
            @endif
            <div class="relative w-full">
                <span class="absolute left-3.5 top-2.5 text-slate-400 text-sm">🔍</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="নাম, ফোন বা ইমেইল দিয়ে খুঁজুন..." class="w-full pl-9 pr-8 py-2 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-purple-500 bg-slate-50 focus:bg-white">
                @if(request('search'))
                <a href="{{ route('users.index', ['role' => request('role')]) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold">✕</a>
                @endif
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-bold shadow-sm transition">
                খুঁজুন
            </button>
        </form>

        <!-- Segmented Role Filters -->
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 text-xs">
            <a href="{{ route('users.index', ['search' => request('search')]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ !request('role') ? 'bg-slate-900 text-white font-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                সকল ({{ $totalUsers }})
            </a>
            <a href="{{ route('users.index', ['role' => 'messenger', 'search' => request('search')]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ request('role') === 'messenger' ? 'bg-emerald-700 text-white font-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🛵 মেসেঞ্জার ({{ $messengerCount }})
            </a>
            <a href="{{ route('users.index', ['role' => 'pa', 'search' => request('search')]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ request('role') === 'pa' ? 'bg-blue-700 text-white font-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                💼 পিএ / ম্যানেজার ({{ $paCount }})
            </a>
            <a href="{{ route('users.index', ['role' => 'family', 'search' => request('search')]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ request('role') === 'family' ? 'bg-amber-700 text-white font-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🏡 পরিবার ({{ $familyCount }})
            </a>
            <a href="{{ route('users.index', ['role' => 'principal', 'search' => request('search')]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition {{ request('role') === 'principal' ? 'bg-purple-700 text-white font-black shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                👑 এডমিন ({{ $principalCount }})
            </a>
        </div>
    </div>

    <!-- 1. MOBILE USER CARDS (< md screens) -->
    <div class="md:hidden space-y-3">
        @foreach($users as $u)
        <div x-show="matchesFilter({{ $u->toJson() }})" class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white flex items-center justify-center text-sm font-black shadow-inner flex-shrink-0">
                        @if($u->avatar)
                        <img src="{{ asset('storage/' . $u->avatar) }}" class="w-full h-full object-cover rounded-2xl">
                        @else
                        {{ mb_substr($u->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-black text-slate-900">{{ $u->name }}</span>
                            @if(!$u->is_active)
                            <span class="text-[9px] font-bold bg-rose-100 text-rose-700 px-1.5 py-0.2 rounded">নিস্ক্রিয়</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-500 block">📞 {{ $u->phone_number }}</span>
                    </div>
                </div>

                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black 
                    {{ $u->isPrincipal() ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                    {{ $u->isPA() ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                    {{ $u->isMessenger() ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                    {{ $u->isFamily() ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                ">
                    {{ $u->role_display_name }}
                </span>
            </div>

            <!-- Informative Details Strip -->
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
                @if($u->isMessenger() && $u->wallet)
                <div class="flex justify-between items-center">
                    <span class="text-slate-600">ওয়ালেট ব্যালেন্স:</span>
                    <span class="font-black text-slate-900 text-sm">৳ {{ number_format($u->wallet->current_balance, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center text-[11px] text-slate-500">
                    <span>মোট বাজার খরচ এন্ট্রি:</span>
                    <span class="font-bold text-slate-800">{{ $u->expenses_count }} টি</span>
                </div>
                @if($u->email)
                <div class="flex justify-between items-center text-[11px] text-slate-500">
                    <span>ইমেইল:</span>
                    <span class="font-medium text-slate-700">{{ $u->email }}</span>
                </div>
                @endif
            </div>

            <!-- Action Buttons Grid on Mobile Card -->
            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-xs">
                <button type="button" @click="openEdit({{ $u->toJson() }})" class="py-2 bg-slate-900 text-white rounded-xl text-center font-bold shadow-sm flex items-center justify-center gap-1">
                    <span>✏️ এডিট</span>
                </button>
                
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $u->phone_number) }}" target="_blank" class="py-2 bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-xl text-center font-bold flex items-center justify-center gap-1">
                    <span>💬 WhatsApp</span>
                </a>

                @if($u->id !== auth()->id())
                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে ইউজার \'{{ $u->name }}\' ডিলিট করতে চান?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-center font-bold">
                        🗑️ মুছুন
                    </button>
                </form>
                @else
                <span class="py-2 text-center text-slate-400 font-bold bg-slate-100 rounded-xl">👑 আপনি</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- 2. DESKTOP USER TABLE (>= md screens) -->
    <div class="hidden md:block bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-5 sm:p-6 space-y-3">
        <h2 class="text-sm font-bold text-slate-900">সকল নিবন্ধিত ইউজার তালিকা</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-y border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">মেম্বার ও পরিচয়</th>
                        <th class="py-3.5 px-4">রোল / পদবি</th>
                        <th class="py-3.5 px-4">মোবাইল ও যোগাযোগ</th>
                        <th class="py-3.5 px-4">স্ট্যাটাস</th>
                        <th class="py-3.5 px-4 text-center">বাজার এন্ট্রি</th>
                        <th class="py-3.5 px-4 text-right">ওয়ালেট ব্যালেন্স</th>
                        <th class="py-3.5 px-4 text-center">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                    <tr x-show="matchesFilter({{ $u->toJson() }})" class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 text-white flex items-center justify-center text-xs font-black shadow-inner flex-shrink-0">
                                    @if($u->avatar)
                                    <img src="{{ asset('storage/' . $u->avatar) }}" class="w-full h-full object-cover rounded-2xl">
                                    @else
                                    {{ mb_substr($u->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 block text-sm">{{ $u->name }}</span>
                                    <span class="text-[10px] text-slate-400">যোগদান: {{ $u->created_at->format('d M, Y') }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black 
                                {{ $u->isPrincipal() ? 'bg-purple-100 text-purple-700 border border-purple-200' : '' }}
                                {{ $u->isPA() ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                {{ $u->isMessenger() ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $u->isFamily() ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                            ">
                                {{ $u->role_display_name }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="space-y-0.5">
                                <a href="tel:{{ $u->phone_number }}" class="font-bold text-slate-800 hover:text-brand-600 block">{{ $u->phone_number }}</a>
                                @if($u->email)
                                <span class="text-[11px] text-slate-400 block">{{ $u->email }}</span>
                                @endif
                                <div class="text-[10px] text-indigo-600 font-medium pt-0.5">
                                    <span>🌐 IP: {{ $u->last_login_ip ?: 'লোকাল' }}</span>
                                    <span class="text-slate-400">• {{ $u->last_login_at ? $u->last_login_at->format('d M, h:i A') : 'সক্রিয়' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $u->is_active ? '🟢 সচল' : '🔴 নিস্ক্রিয়' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <span class="font-bold text-slate-700">{{ $u->expenses_count }} টি</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-900 whitespace-nowrap">
                            @if($u->isMessenger() && $u->wallet)
                            ৳ {{ number_format($u->wallet->current_balance, 2) }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="openEdit({{ $u->toJson() }})" class="py-1 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition">
                                    ✏️ এডিট
                                </button>
                                <a href="{{ route('users.edit', $u->id) }}" class="py-1 px-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-xs border border-slate-200" title="সম্পূর্ণ এডিটর">
                                    ⚙️
                                </a>

                                @if($u->id !== auth()->id())
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে ইউজার \'{{ $u->name }}\' ডিলিট করতে চান?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="py-1 px-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition" title="ডিলিট">
                                        🗑️
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Server-Side Pagination Links for 10,000+ Users -->
        <div class="pt-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create User Modal -->
    <div x-show="createUserModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="createUserModal = false" class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>➕</span>
                    <span>নতুন ইউজার তৈরি করুন</span>
                </h3>
                <button @click="createUserModal = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-3.5">
                @csrf
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ইউজার রোল / পদবি *</label>
                    <select name="role" x-model="userRole" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                        <option value="messenger">🛵 বাজার মেসেঞ্জার (Staff - খরচ এন্ট্রি ও ওয়ালেট)</option>
                        <option value="pa">💼 প্রিন্সিপালের পিএ (Manager - ভেরিফিকেশন ও মনিটরিং)</option>
                        <option value="family">🏡 পরিবারের সদস্য (Viewer - রিড-ওনলি ড্যাশবোর্ড)</option>
                        <option value="principal">👑 প্রিন্সিপাল (Super Admin - সম্পূর্ণ নিয়ন্ত্রণ)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">পূর্ণ নাম *</label>
                    <input type="text" name="name" required placeholder="যেমন: মোঃ আব্দুর রহিম" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">মোবাইল নম্বর (লগইন আইডি) *</label>
                    <input type="text" name="phone_number" required placeholder="যেমন: 01711000000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ইমেইল এড্রেস (ঐচ্ছিক)</label>
                    <input type="email" name="email" placeholder="যেমন: user@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">পাসওয়ার্ড (কমপক্ষে ৬ অক্ষর) *</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500">
                </div>

                <div x-show="userRole === 'messenger'" class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-1">
                    <label class="block text-[11px] font-bold text-emerald-900 uppercase">মেসেঞ্জারের প্রারম্ভিক ওয়ালেট ব্যালেন্স (৳)</label>
                    <input type="number" step="0.01" min="0" name="initial_balance" value="0" placeholder="0.00" class="w-full px-3 py-2 rounded-xl border border-emerald-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-emerald-500 bg-white">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="createUserModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">বাতিল</button>
                    <button type="submit" class="py-2.5 px-5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-md">ইউজার তৈরি করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal (Super Admin Comprehensive Edit) -->
    <div x-show="editUserModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="editUserModal = false" class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>✏️</span>
                    <span>ইউজারের সকল ডাটা পরিবর্তন</span>
                </h3>
                <button @click="editUserModal = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 font-bold text-lg flex items-center justify-center">&times;</button>
            </div>

            <form :action="`/users/${editUser.id}`" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ইউজার রোল / পদবি *</label>
                    <select name="role" x-model="editUser.role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                        <option value="messenger">🛵 বাজার মেসেঞ্জার (Staff)</option>
                        <option value="pa">💼 প্রিন্সিপালের পিএ (Manager)</option>
                        <option value="family">🏡 পরিবারের সদস্য (Viewer)</option>
                        <option value="principal">👑 প্রিন্সিপাল (Super Admin)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">পূর্ণ নাম *</label>
                    <input type="text" name="name" x-model="editUser.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">মোবাইল নম্বর *</label>
                    <input type="text" name="phone_number" x-model="editUser.phone_number" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ইমেইল এড্রেস (ঐচ্ছিক)</label>
                    <input type="email" name="email" x-model="editUser.email" placeholder="user@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">অ্যাকাউন্ট স্ট্যাটাস *</label>
                    <select name="is_active" x-model="editUser.is_active" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                        <option value="1">🟢 সচল (Active)</option>
                        <option value="0">🔴 নিস্ক্রিয় (Inactive)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">🔑 নতুন পাসওয়ার্ড সেট করুন (ঐচ্ছিক)</label>
                    <input type="password" name="password" placeholder="অপরিবর্তিত রাখতে খালি রাখুন" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Messenger Wallet Balance Direct Adjustment in Modal -->
                <div x-show="editUser.role === 'messenger'" class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-2">
                    <label class="block text-[10px] font-bold text-emerald-950 uppercase">মেসেঞ্জারের ওয়ালেট ব্যালেন্স সরাসরি সংশোধন (৳)</label>
                    <input type="number" step="0.01" name="wallet_balance" x-model="editUser.wallet_balance" class="w-full px-3 py-2 rounded-xl border border-emerald-300 text-xs sm:text-sm font-black focus:ring-2 focus:ring-emerald-500 bg-white">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="editUserModal = false" class="py-2.5 px-4 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">বাতিল</button>
                    <button type="submit" class="py-2.5 px-5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-md">সকল ডাটা আপডেট করুন</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
