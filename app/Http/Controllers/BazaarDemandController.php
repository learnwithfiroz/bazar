<?php

namespace App\Http\Controllers;

use App\Models\BazaarDemand;
use App\Models\BazaarDemandItem;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BazaarDemandController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all'); // 'active', 'history', 'all'
        
        $query = BazaarDemand::with(['creator', 'assignee', 'items'])
            ->latest('target_date')
            ->latest('id');

        if ($tab === 'active') {
            $query->whereIn('status', ['PENDING', 'IN_PROGRESS']);
        } elseif ($tab === 'history') {
            $query->where('status', 'COMPLETED');
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('target_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('target_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%")
                  ->orWhereHas('items', function ($iq) use ($term) {
                      $iq->where('item_name', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $demands = $query->paginate(12)->withQueryString();

        // High Level History Metrics
        $totalCount = BazaarDemand::count();
        $activeCount = BazaarDemand::whereIn('status', ['PENDING', 'IN_PROGRESS'])->count();
        $historyCompletedCount = BazaarDemand::where('status', 'COMPLETED')->count();
        $totalPurchasedItems = BazaarDemandItem::where('is_purchased', true)->count();

        $messengers = User::where('role', 'messenger')->get();
        $creators = User::whereIn('role', ['principal', 'pa', 'family'])->get();

        return view('bazaar_demands.index', compact(
            'demands',
            'messengers',
            'creators',
            'tab',
            'totalCount',
            'activeCount',
            'historyCompletedCount',
            'totalPurchasedItems'
        ));
    }

    public function create()
    {
        $messengers = User::where('role', 'messenger')->get();
        $defaultCategories = ['কাঁচাবাজার', 'মাছ ও মাংস', 'শাকসবজি', 'মুদিখানা', 'ফলমূল', 'ঔষধ ও চিকিৎসা', 'অন্যান্য'];

        $suggestedItems = DB::table('expense_items')
            ->select('item_name', 'category', 'unit', DB::raw('AVG(unit_price) as avg_price'), DB::raw('COUNT(*) as usage_count'))
            ->groupBy('item_name', 'category', 'unit')
            ->orderByDesc('usage_count')
            ->take(100)
            ->get();

        return view('bazaar_demands.create', compact('messengers', 'defaultCategories', 'suggestedItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'target_date'  => 'required|date',
            'assigned_to'  => 'nullable|exists:users,id',
            'items'        => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit'     => 'required|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $demand = DB::transaction(function () use ($request) {
            $demand = BazaarDemand::create([
                'created_by'  => Auth::id(),
                'assigned_to' => $request->assigned_to,
                'title'       => $request->title,
                'target_date' => $request->target_date,
                'status'      => 'PENDING',
                'notes'       => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                BazaarDemandItem::create([
                    'bazaar_demand_id' => $demand->id,
                    'item_name'        => $itemData['name'],
                    'category'         => $itemData['category'] ?? 'কাঁচাবাজার',
                    'quantity'         => (float) $itemData['quantity'],
                    'unit'             => $itemData['unit'] ?? 'কেজি',
                    'estimated_price'  => isset($itemData['estimated_price']) ? (float) $itemData['estimated_price'] : null,
                    'is_purchased'     => false,
                    'notes'            => $itemData['notes'] ?? null,
                ]);
            }

            return $demand;
        });

        // Generate WhatsApp notification text for messenger
        $messenger = $demand->assigned_to ? User::find($demand->assigned_to) : User::where('role', 'messenger')->first();
        $messengerPhone = $messenger->phone_number ?? '';
        
        $itemsText = "";
        foreach ($demand->items as $idx => $it) {
            $n = $idx + 1;
            $itemsText .= "{$n}. {$it->item_name} ({$it->quantity} {$it->unit})\n";
        }

        $msg = "🛒 *বাজারের শপিং ডিমান্ড তালিকা*\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📋 *শিরোনাম:* {$demand->title}\n";
        $msg .= "📅 *তারিখ:* " . date('d F, Y', strtotime($demand->target_date)) . "\n";
        $msg .= "👤 *তৈরি করেছেন:* " . auth()->user()->name . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🛍️ *প্রয়োজনীয় পণ্যের তালিকা:*\n{$itemsText}\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔗 *অনলাইনে চেকলিস্ট দেখতে:* " . url("/bazaar-demands/{$demand->id}") . "\n";

        $whatsappUrl = WhatsAppService::formatWhatsAppUrl($messengerPhone, $msg);

        return redirect()->route('bazaar-demands.show', $demand)->with([
            'success' => 'বাজারের ডিমান্ড / শপিং লিস্ট সফলভাবে তৈরি হয়েছে।',
            'whatsapp_url' => $whatsappUrl,
        ]);
    }

    public function show(BazaarDemand $demand)
    {
        $demand->load(['creator', 'assignee', 'items']);
        return view('bazaar_demands.show', compact('demand'));
    }

    public function print(BazaarDemand $demand)
    {
        $demand->load(['creator', 'assignee', 'items']);
        return view('bazaar_demands.print', compact('demand'));
    }

    public function toggleItem(Request $request, BazaarDemandItem $item)
    {
        $item->is_purchased = !$item->is_purchased;
        $item->save();

        $demand = $item->demand;
        $totalItems = $demand->items()->count();
        $purchasedCount = $demand->items()->where('is_purchased', true)->count();

        if ($purchasedCount === 0) {
            $demand->status = 'PENDING';
        } elseif ($purchasedCount === $totalItems) {
            $demand->status = 'COMPLETED';
        } else {
            $demand->status = 'IN_PROGRESS';
        }
        $demand->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_purchased' => $item->is_purchased,
                'purchased_count' => $purchasedCount,
                'total_items' => $totalItems,
                'status' => $demand->status,
                'status_label' => $demand->status_label,
            ]);
        }

        return back();
    }

    public function destroy(BazaarDemand $demand)
    {
        if (Auth::user()->isFamily() && $demand->created_by !== Auth::id()) {
            abort(403, 'অনুমতি নেই।');
        }

        $demand->delete();
        return redirect()->route('bazaar-demands.index')->with('success', 'বাজারের ডিমান্ড লিস্টটি ডিলিট করা হয়েছে।');
    }
}
