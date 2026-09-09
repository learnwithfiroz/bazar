<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseComment;
use App\Models\ExpenseItem;
use App\Models\FundRequest;
use App\Models\Slip;
use App\Models\SystemSetting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemResetController extends Controller
{
    /**
     * Full System Data Reset for Super Admin (Principal)
     */
    public function resetAll(Request $request)
    {
        $user = Auth::user();

        if (!$user->isPrincipal()) {
            abort(403, 'শুধুমাত্র সুপার এডমিন সম্পূর্ণ ডাটা রিসেট করার অধিকার রাখেন।');
        }

        // Validate super admin password for safety
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, $user->password)) {
            return back()->withErrors(['admin_password' => 'সুপার এডমিনের পাসওয়ার্ড সঠিক নয়। ডাটা রিসেট বাতিল করা হয়েছে।']);
        }

        DB::transaction(function () {
            // 1. Delete all physical slip image files from disk
            $slips = Slip::all();
            foreach ($slips as $slip) {
                if ($slip->image_path) {
                    $oldPath = storage_path('app/public/' . $slip->image_path);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }

            // 2. Truncate / Delete all operational expense & transaction tables
            Slip::query()->delete();
            ExpenseItem::query()->delete();
            ExpenseComment::query()->delete();
            Expense::query()->delete();
            FundRequest::query()->delete();
            WalletTransaction::query()->delete();

            // 3. Reset all messenger wallets balance to 0.00
            Wallet::query()->update([
                'current_balance' => 0.00,
            ]);
        });

        return redirect()->route('dashboard')->with('success', 'সিস্টেমের সমস্ত খরচের হিসাব, মেমো ছবি, ফান্ড রিকোয়েস্ট ও ট্রানজেকশন সফলভাবে সম্পূর্ণ রিসেট (Clear) করা হয়েছে। ইউজার অ্যাকাউন্টসমূহ অক্ষত রয়েছে।');
    }

    /**
     * 1-Click Database Backup Download for Super Admin
     */
    public function downloadBackup(): BinaryFileResponse
    {
        if (!Auth::user()->isPrincipal()) {
            abort(403, 'শুধুমাত্র সুপার এডমিন ডেটাবেস ব্যাকআপ ডাউনলোড করতে পারবেন।');
        }

        $dbPath = database_path('database.sqlite');
        
        if (!file_exists($dbPath)) {
            touch($dbPath);
        }

        $filename = 'bazaar_expense_db_backup_' . date('Y_m_d_His') . '.sqlite';

        return response()->download($dbPath, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    /**
     * Upload & Restore Database Backup File for Super Admin
     */
    public function uploadBackup(Request $request)
    {
        $user = Auth::user();

        if (!$user->isPrincipal()) {
            abort(403, 'শুধুমাত্র সুপার এডমিন ডেটাবেস ব্যাকআপ আপলোড/রিস্টোর করতে পারবেন।');
        }

        $request->validate([
            'backup_file'    => 'required|file|max:51200', // max 50MB
            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->admin_password, $user->password)) {
            return back()->withErrors(['admin_password' => 'সুপার এডমিনের পাসওয়ার্ড সঠিক নয়। ব্যাকআপ রিস্টোর বাতিল করা হয়েছে।']);
        }

        $file = $request->file('backup_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['sqlite', 'db', 'sqlite3'])) {
            $destPath = database_path('database.sqlite');
            
            // Backup current before replacing
            if (file_exists($destPath)) {
                copy($destPath, database_path('database_backup_before_restore.sqlite'));
            }

            // Move uploaded file to database.sqlite
            $file->move(database_path(), 'database.sqlite');

            // Clear system cache
            try {
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
            } catch (\Exception $e) {
                // ignore
            }

            return back()->with('success', 'অভিনন্দন! ডেটাবেস ব্যাকআপ ফাইলটি সফলভাবে আপলোড এবং সম্পূর্ণ ডাটা রিস্টোর হয়েছে।');
        }

        if ($ext === 'sql') {
            try {
                $sqlContent = file_get_contents($file->getRealPath());
                DB::unprepared($sqlContent);
                return back()->with('success', 'অভিনন্দন! SQL ব্যাকআপ ফাইলটি সফলভাবে ডেটাবেসে ইমপোর্ট এবং রিস্টোর সম্পন্ন হয়েছে।');
            } catch (\Exception $e) {
                return back()->withErrors(['backup_file' => 'SQL ফাইল ইমপোর্ট করতে সমস্যা হয়েছে: ' . $e->getMessage()]);
            }
        }

        return back()->withErrors(['backup_file' => 'শুধুমাত্র .sqlite, .db অথবা .sql ব্যাকআপ ফাইল আপলোড করা যাবে।']);
    }

    /**
     * Update Monthly Budget Limit
     */
    public function updateBudget(Request $request)
    {
        if (!Auth::user()->isPrincipal() && !Auth::user()->isPA()) {
            abort(403, 'অনুমতি নেই।');
        }

        $request->validate([
            'monthly_budget' => 'required|numeric|min:0',
        ]);

        SystemSetting::set('monthly_budget_limit', (float) $request->monthly_budget);

        return back()->with('success', 'মাসিক বাজেট সিলিং সফলভাবে ৳ ' . number_format($request->monthly_budget, 2) . ' তে আপডেট করা হয়েছে।');
    }
}
