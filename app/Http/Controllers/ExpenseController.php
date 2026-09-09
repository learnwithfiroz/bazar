<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\Slip;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $query = Expense::with(['creator', 'items', 'slips', 'comments'])->latest('expense_date')->latest('id');

        if ($request->filled('date')) {
            $query->where('expense_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if (Auth::user()->isMessenger()) {
            $query->where('created_by', Auth::id());
        }

        $expenses = $query->paginate(15)->withQueryString();
        $messengers = User::where('role', 'messenger')->get();

        return view('expenses.index', compact('expenses', 'messengers'));
    }

    /**
     * Dedicated Voucher-wise Bill Verification & Audit Portal
     */
    public function voucherCheck(Request $request)
    {
        $query = Expense::with(['creator', 'items', 'slips', 'comments.user', 'comments.replies.user'])
            ->latest('expense_date')
            ->latest('id');

        if ($request->filled('date')) {
            $query->where('expense_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('messenger_id')) {
            $query->where('created_by', $request->messenger_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('memo_no', 'like', "%{$term}%")
                  ->orWhere('vendor_name', 'like', "%{$term}%")
                  ->orWhereHas('items', function ($iq) use ($term) {
                      $iq->where('item_name', 'like', "%{$term}%");
                  });
            });
        }

        if (Auth::user()->isMessenger()) {
            $query->where('created_by', Auth::id());
        }

        $expenses = $query->paginate(12)->withQueryString();

        $baseQuery = Expense::query();
        if (Auth::user()->isMessenger()) {
            $baseQuery->where('created_by', Auth::id());
        }

        $pendingCount = (clone $baseQuery)->where('status', 'SUBMITTED')->count();
        $reviewedCount = (clone $baseQuery)->where('status', 'REVIEWED')->count();
        $flaggedCount = (clone $baseQuery)->where('status', 'FLAGGED')->count();
        $totalAmountReviewed = (clone $baseQuery)->where('status', 'REVIEWED')->sum('total_amount');

        $messengers = User::where('role', 'messenger')->get();

        return view('expenses.voucher_check', compact(
            'expenses',
            'pendingCount',
            'reviewedCount',
            'flaggedCount',
            'totalAmountReviewed',
            'messengers'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['current_balance' => 0.00, 'low_balance_alert_limit' => 500.00]
        );

        $defaultCategories = ['কাঁচাবাজার', 'মাছ ও মাংস', 'শাকসবজি', 'মুদিখানা', 'ফলমূল', 'ঔষধ ও চিকিৎসা', 'যাতায়াত', 'অন্যান্য'];

        // Safe fetch dynamic auto-suggestions
        try {
            $suggestedItems = DB::table('expense_items')
                ->select('item_name', 'category', 'unit', DB::raw('AVG(unit_price) as avg_price'), DB::raw('COUNT(*) as usage_count'))
                ->groupBy('item_name', 'category', 'unit')
                ->orderByDesc('usage_count')
                ->take(100)
                ->get();
        } catch (\Throwable $e) {
            $suggestedItems = collect();
        }

        try {
            $vendorSuggestions = DB::table('expenses')
                ->whereNotNull('vendor_name')
                ->where('vendor_name', '!=', '')
                ->select('vendor_name')
                ->distinct()
                ->take(30)
                ->pluck('vendor_name');
        } catch (\Throwable $e) {
            $vendorSuggestions = collect();
        }

        $prefilledItems = [];
        if ($request->filled('demand_id')) {
            $demand = \App\Models\BazaarDemand::with('items')->find($request->demand_id);
            if ($demand) {
                foreach ($demand->items as $it) {
                    $prefilledItems[] = [
                        'name' => $it->item_name,
                        'category' => $it->category,
                        'quantity' => (float) $it->quantity,
                        'unit' => $it->unit,
                        'unit_price' => (float) ($it->estimated_price ?? 0),
                    ];
                }
            }
        }

        return view('expenses.create', compact('wallet', 'defaultCategories', 'suggestedItems', 'vendorSuggestions', 'prefilledItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'title'        => 'required|string|max:255',
            'memo_no'      => 'nullable|string|max:100',
            'vendor_name'  => 'nullable|string|max:150',
            'items'        => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.category' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit'     => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'slip_photos'  => 'nullable|array',
            'notes'        => 'nullable|string',
        ]);

        $user = Auth::user();

        // Calculate total amount
        $calculatedTotal = 0;
        foreach ($request->items as $itemData) {
            $calculatedTotal += ((float) $itemData['quantity']) * ((float) $itemData['unit_price']);
        }

        // DB Transaction for atomic saving & wallet deduction
        $expense = DB::transaction(function () use ($request, $user, $calculatedTotal) {
            $expense = Expense::create([
                'created_by'   => $user->id,
                'expense_date' => $request->expense_date,
                'title'        => $request->title,
                'memo_no'      => $request->memo_no,
                'vendor_name'  => $request->vendor_name,
                'total_amount' => $calculatedTotal,
                'status'       => 'SUBMITTED',
                'notes'        => $request->notes,
            ]);

            // Save items
            foreach ($request->items as $itemData) {
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                ExpenseItem::create([
                    'expense_id'  => $expense->id,
                    'item_name'   => $itemData['name'],
                    'category'    => $itemData['category'] ?? 'কাঁচাবাজার',
                    'quantity'    => $qty,
                    'unit'        => $itemData['unit'],
                    'unit_price'  => $price,
                    'total_price' => $qty * $price,
                ]);
            }

            // Safe Slips/Attachments Upload (Saved to both storage and public)
            if ($request->hasFile('slip_photos')) {
                $storageDir = storage_path('app/public/slips');
                $publicDir = public_path('storage/slips');
                if (!file_exists($storageDir)) @mkdir($storageDir, 0775, true);
                if (!file_exists($publicDir)) @mkdir($publicDir, 0775, true);

                foreach ($request->file('slip_photos') as $file) {
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'pdf'])) {
                        continue;
                    }

                    $fileName = Str::random(40) . '.' . $ext;
                    $file->move($storageDir, $fileName);
                    @copy($storageDir . '/' . $fileName, $publicDir . '/' . $fileName);
                    $path = 'slips/' . $fileName;

                    Slip::create([
                        'expense_id'    => $expense->id,
                        'image_path'    => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size'     => @filesize($storageDir . '/' . $fileName) ?: 0,
                    ]);
                }
            }

            // Deduct from Messenger wallet
            $this->walletService->deductExpense($user->id, $calculatedTotal, $expense);

            return $expense;
        });

        // WhatsApp integration link
        $principal = User::where('role', 'principal')->first();
        $principalPhone = $principal->phone_number ?? '';
        $wallet = Wallet::where('user_id', $user->id)->first();
        $whatsappUrl = WhatsAppService::generateExpenseReportLink($expense, $principalPhone, (float) ($wallet->current_balance ?? 0));

        if ($request->boolean('send_whatsapp_now')) {
            return redirect()->away($whatsappUrl);
        }

        return redirect()->route('expenses.show', $expense)->with([
            'success' => 'দৈনিক খরচের হিসাব ও বিল সফলভাবে সংরক্ষিত হয়েছে এবং ওয়ালেট থেকে কাটা হয়েছে।',
            'whatsapp_url' => $whatsappUrl,
        ]);
    }

    public function show(Expense $expense)
    {
        $expense->load(['creator', 'items', 'slips', 'comments.user', 'comments.replies.user']);
        
        $principal = User::where('role', 'principal')->first();
        $principalPhone = $principal->phone_number ?? '';
        $creatorWallet = Wallet::where('user_id', $expense->created_by)->first();
        $whatsappUrl = WhatsAppService::generateExpenseReportLink($expense, $principalPhone, (float) ($creatorWallet->current_balance ?? 0));

        return view('expenses.show', compact('expense', 'whatsappUrl'));
    }

    /**
     * Direct 1-Click WhatsApp Redirect for Expense Report to Principal
     */
    public function whatsapp(Expense $expense)
    {
        $expense->load(['creator', 'items', 'slips']);

        $principal = User::where('role', 'principal')->first();
        $principalPhone = $principal->phone_number ?? '';
        $creatorWallet = Wallet::where('user_id', $expense->created_by)->first();
        $whatsappUrl = WhatsAppService::generateExpenseReportLink(
            $expense,
            $principalPhone,
            (float) ($creatorWallet->current_balance ?? 0)
        );

        return redirect()->away($whatsappUrl);
    }

    public function destroy(Expense $expense)
    {
        if (!Auth::user()->isPrincipal() && !Auth::user()->isPA()) {
            abort(403, 'খরচের ভাউচার ডিলিট করার অনুমতি নেই।');
        }

        DB::transaction(function () use ($expense) {
            foreach ($expense->slips as $slip) {
                if ($slip->image_path) {
                    $storagePath = storage_path('app/public/' . $slip->image_path);
                    $publicPath = public_path('storage/' . $slip->image_path);
                    if (file_exists($storagePath)) @unlink($storagePath);
                    if (file_exists($publicPath)) @unlink($publicPath);
                }
            }

            $expense->items()->delete();
            $expense->slips()->delete();
            $expense->comments()->delete();
            $expense->delete();
        });

        return redirect()->route('expenses.index')->with('success', 'ভাউচারটি সফলভাবে ডিলিট করা হয়েছে।');
    }
}
