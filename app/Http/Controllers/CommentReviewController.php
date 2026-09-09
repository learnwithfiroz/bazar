<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentReviewController extends Controller
{
    public function storeComment(Request $request, Expense $expense)
    {
        $request->validate([
            'message'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:expense_comments,id',
        ]);

        ExpenseComment::create([
            'expense_id' => $expense->id,
            'user_id'    => Auth::id(),
            'parent_id'  => $request->parent_id,
            'message'    => $request->message,
        ]);

        return back()->with('success', 'মন্তব্য সফলভাবে পোস্ট করা হয়েছে।');
    }

    public function updateStatus(Request $request, Expense $expense)
    {
        $request->validate([
            'status' => 'required|in:SUBMITTED,REVIEWED,FLAGGED',
        ]);

        if (!Auth::user()->canManageFunds()) {
            abort(403, 'অনুমোদন বা স্ট্যাটাস পরিবর্তনের অধিকার নেই।');
        }

        $expense->update([
            'status' => $request->status,
        ]);

        return back()->with('success', "বিলের স্ট্যাটাস পরিবর্তন করে '{$expense->status_label}' করা হয়েছে।");
    }

    public function destroyComment(ExpenseComment $comment)
    {
        if (!Auth::user()->isPrincipal() && $comment->user_id !== Auth::id()) {
            abort(403, 'মন্তব্য ডিলিট করার অধিকার নেই।');
        }

        $comment->delete();
        return back()->with('success', 'মন্তব্যটি মুছে ফেলা হয়েছে।');
    }
}
