<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $messengerId = $request->input('messenger_id');

        $query = Expense::with(['creator', 'items', 'slips'])
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate);

        if ($messengerId) {
            $query->where('created_by', $messengerId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();
        $totalExpense = $expenses->sum('total_amount');

        // 1. Category breakdown
        $categoryQuery = DB::table('expense_items')
            ->join('expenses', 'expense_items.expense_id', '=', 'expenses.id')
            ->whereDate('expenses.expense_date', '>=', $startDate)
            ->whereDate('expenses.expense_date', '<=', $endDate);

        if ($messengerId) {
            $categoryQuery->where('expenses.created_by', $messengerId);
        }

        $categoryStats = $categoryQuery
            ->select('expense_items.category', DB::raw('SUM(expense_items.total_price) as total_spent'), DB::raw('COUNT(expense_items.id) as item_count'))
            ->groupBy('expense_items.category')
            ->orderBy('total_spent', 'desc')
            ->get();

        // 2. Date Range Consolidated Bazaar List (আইটেমভিত্তিক সামগ্রিক বাজার তালিকা)
        $bazaarItemsQuery = DB::table('expense_items')
            ->join('expenses', 'expense_items.expense_id', '=', 'expenses.id')
            ->whereDate('expenses.expense_date', '>=', $startDate)
            ->whereDate('expenses.expense_date', '<=', $endDate);

        if ($messengerId) {
            $bazaarItemsQuery->where('expenses.created_by', $messengerId);
        }

        $consolidatedBazaarList = $bazaarItemsQuery
            ->select(
                'expense_items.item_name',
                'expense_items.category',
                'expense_items.unit',
                DB::raw('SUM(expense_items.quantity) as total_quantity'),
                DB::raw('SUM(expense_items.total_price) as total_amount'),
                DB::raw('AVG(expense_items.unit_price) as avg_unit_price'),
                DB::raw('COUNT(expense_items.id) as frequency')
            )
            ->groupBy('expense_items.item_name', 'expense_items.category', 'expense_items.unit')
            ->orderBy('total_amount', 'desc')
            ->get();

        $messengers = User::where('role', 'messenger')->get();
        $selectedMessenger = $messengerId ? User::find($messengerId) : null;

        return view('reports.index', compact(
            'expenses',
            'totalExpense',
            'categoryStats',
            'consolidatedBazaarList',
            'startDate',
            'endDate',
            'messengerId',
            'messengers',
            'selectedMessenger'
        ));
    }

    /**
     * Daily / Single Voucher Printable or PDF View
     */
    public function printDay(Request $request)
    {
        $expenseId = $request->input('expense_id');
        $rawDate = $request->input('date');
        $messengerId = $request->input('messenger_id');

        $query = Expense::with(['creator', 'items', 'slips', 'comments.user']);

        if ($expenseId) {
            $query->where('id', $expenseId);
            $targetExpense = (clone $query)->first();
            $date = $targetExpense ? Carbon::parse($targetExpense->expense_date)->toDateString() : now()->toDateString();
        } else {
            try {
                $date = $rawDate ? Carbon::parse($rawDate)->toDateString() : now()->toDateString();
            } catch (\Exception $e) {
                $date = now()->toDateString();
            }

            $query->whereDate('expense_date', $date);

            if ($messengerId) {
                $query->where('created_by', $messengerId);
            }
        }

        $expenses = $query->get();
        $totalAmount = $expenses->sum('total_amount');
        $selectedMessenger = $messengerId ? User::find($messengerId) : ($expenses->first()->creator ?? null);

        // Get transactions for that day
        $transactions = WalletTransaction::with(['wallet.user', 'performer'])
            ->whereDate('created_at', $date)
            ->get();

        return view('reports.print_day', compact('expenses', 'date', 'totalAmount', 'transactions', 'selectedMessenger'));
    }

    /**
     * Date Range Printable / PDF Bazaar Report
     */
    public function printRange(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $messengerId = $request->input('messenger_id');

        $query = Expense::with(['creator', 'items', 'slips'])
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate);

        if ($messengerId) {
            $query->where('created_by', $messengerId);
        }

        $expenses = $query->orderBy('expense_date', 'asc')->get();
        $totalAmount = $expenses->sum('total_amount');

        // Consolidated Bazaar Items for the date range
        $bazaarItemsQuery = DB::table('expense_items')
            ->join('expenses', 'expense_items.expense_id', '=', 'expenses.id')
            ->whereDate('expenses.expense_date', '>=', $startDate)
            ->whereDate('expenses.expense_date', '<=', $endDate);

        if ($messengerId) {
            $bazaarItemsQuery->where('expenses.created_by', $messengerId);
        }

        $consolidatedBazaarList = $bazaarItemsQuery
            ->select(
                'expense_items.item_name',
                'expense_items.category',
                'expense_items.unit',
                DB::raw('SUM(expense_items.quantity) as total_quantity'),
                DB::raw('SUM(expense_items.total_price) as total_amount'),
                DB::raw('AVG(expense_items.unit_price) as avg_unit_price'),
                DB::raw('COUNT(expense_items.id) as frequency')
            )
            ->groupBy('expense_items.item_name', 'expense_items.category', 'expense_items.unit')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Category breakdown
        $categoryQuery = DB::table('expense_items')
            ->join('expenses', 'expense_items.expense_id', '=', 'expenses.id')
            ->whereDate('expenses.expense_date', '>=', $startDate)
            ->whereDate('expenses.expense_date', '<=', $endDate);

        if ($messengerId) {
            $categoryQuery->where('expenses.created_by', $messengerId);
        }

        $categoryStats = $categoryQuery
            ->select('expense_items.category', DB::raw('SUM(expense_items.total_price) as total_spent'), DB::raw('COUNT(expense_items.id) as item_count'))
            ->groupBy('expense_items.category')
            ->orderBy('total_spent', 'desc')
            ->get();

        $selectedMessenger = $messengerId ? User::find($messengerId) : null;
        $aggregatedItems = $consolidatedBazaarList;

        return view('reports.print_range', compact(
            'expenses',
            'totalAmount',
            'consolidatedBazaarList',
            'aggregatedItems',
            'categoryStats',
            'startDate',
            'endDate',
            'selectedMessenger'
        ));
    }

    /**
     * Export Expenses & Items into Excel / CSV with UTF-8 BOM
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $messengerId = $request->input('messenger_id');

        $query = Expense::with(['creator', 'items'])
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate);

        if ($messengerId) {
            $query->where('created_by', $messengerId);
        }

        $expenses = $query->orderBy('expense_date', 'asc')->get();

        $filename = "bazaar_expense_report_{$startDate}_to_{$endDate}.csv";

        return new StreamedResponse(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Microsoft Excel Bangla font support
            fputs($handle, "\xEF\xBB\xBF");

            // CSV Header Row
            fputcsv($handle, [
                'ক্রমিক',
                'তারিখ',
                'ভাউচার শিরোনাম',
                'ক্যাশ মেমো নং',
                'পণ্যের নাম',
                'ক্যাটাগরি',
                'পরিমাণ',
                'একক',
                'একক দর (টাকা)',
                'আইটেম মোট মূল্য (টাকা)',
                'ভাউচার মোট টাকা',
                'বাজারকারী',
                'স্ট্যাটাস'
            ]);

            $count = 1;
            foreach ($expenses as $expense) {
                $dateStr = date('d-m-Y', strtotime($expense->expense_date));
                $memoStr = $expense->memo_no ?: 'N/A';
                $creatorStr = $expense->creator->name ?? 'N/A';
                $statusStr = $expense->status_label;

                if ($expense->items->isNotEmpty()) {
                    foreach ($expense->items as $item) {
                        fputcsv($handle, [
                            $count++,
                            $dateStr,
                            $expense->title,
                            $memoStr,
                            $item->item_name,
                            $item->category,
                            $item->quantity,
                            $item->unit,
                            number_format($item->unit_price, 2),
                            number_format($item->total_price, 2),
                            number_format($expense->total_amount, 2),
                            $creatorStr,
                            $statusStr,
                        ]);
                    }
                } else {
                    fputcsv($handle, [
                        $count++,
                        $dateStr,
                        $expense->title,
                        $memoStr,
                        'N/A',
                        'N/A',
                        0,
                        'N/A',
                        0,
                        0,
                        number_format($expense->total_amount, 2),
                        $creatorStr,
                        $statusStr,
                    ]);
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
