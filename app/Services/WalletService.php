<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\FundRequest;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * Deduct expense amount from messenger wallet atomically.
     */
    public function deductExpense(int $userId, float $amount, Expense $expense): WalletTransaction
    {
        return DB::transaction(function () use ($userId, $amount, $expense) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $userId,
                    'current_balance' => 0.00,
                    'low_balance_alert_limit' => 500.00,
                ]);
            }

            $balanceBefore = (float) $wallet->current_balance;
            $balanceAfter = $balanceBefore - $amount;

            $wallet->current_balance = $balanceAfter;
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id'       => $wallet->id,
                'performed_by'    => $userId,
                'type'            => 'DEBIT',
                'amount'          => $amount,
                'balance_before'  => $balanceBefore,
                'balance_after'   => $balanceAfter,
                'reference_type'  => Expense::class,
                'reference_id'    => $expense->id,
                'notes'           => "বাজার খরচ: {$expense->title} (বিল #{$expense->id})",
            ]);
        });
    }

    /**
     * Reverse/Refund expense when Super Admin deletes an expense.
     */
    public function reverseExpense(Expense $expense, int $performedBy): void
    {
        DB::transaction(function () use ($expense, $performedBy) {
            $wallet = Wallet::where('user_id', $expense->created_by)->lockForUpdate()->first();
            if ($wallet) {
                $refundAmount = (float) $expense->total_amount;
                $balanceBefore = (float) $wallet->current_balance;
                $balanceAfter = $balanceBefore + $refundAmount;

                $wallet->current_balance = $balanceAfter;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id'       => $wallet->id,
                    'performed_by'    => $performedBy,
                    'type'            => 'CREDIT',
                    'amount'          => $refundAmount,
                    'balance_before'  => $balanceBefore,
                    'balance_after'   => $balanceAfter,
                    'reference_type'  => null,
                    'reference_id'    => null,
                    'notes'           => "বিল ডিলিট ও রিফান্ড: {$expense->title} (বিল #{$expense->id})",
                ]);
            }
        });
    }

    /**
     * Top-up messenger wallet (by Principal / PA).
     */
    public function topUpWallet(int $walletId, int $performedBy, float $amount, string $notes = ''): WalletTransaction
    {
        return DB::transaction(function () use ($walletId, $performedBy, $amount, $notes) {
            $wallet = Wallet::lockForUpdate()->findOrFail($walletId);

            $balanceBefore = (float) $wallet->current_balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->current_balance = $balanceAfter;
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id'       => $wallet->id,
                'performed_by'    => $performedBy,
                'type'            => 'CREDIT',
                'amount'          => $amount,
                'balance_before'  => $balanceBefore,
                'balance_after'   => $balanceAfter,
                'reference_type'  => null,
                'reference_id'    => null,
                'notes'           => $notes ?: 'ফান্ড রিচার্জ (টপ-আপ)',
            ]);
        });
    }

    /**
     * Approve fund request and credit wallet.
     */
    public function approveFundRequest(int $requestId, int $reviewerId, ?float $overrideAmount = null, string $notes = ''): FundRequest
    {
        return DB::transaction(function () use ($requestId, $reviewerId, $overrideAmount, $notes) {
            $fundRequest = FundRequest::lockForUpdate()->findOrFail($requestId);

            if ($fundRequest->status !== 'PENDING') {
                throw new Exception('এই ফান্ড রিকোয়েস্টটি ইতিমধ্যে প্রসেস করা হয়েছে।');
            }

            $amountToCredit = $overrideAmount ?? (float) $fundRequest->amount;
            $wallet = Wallet::lockForUpdate()->findOrFail($fundRequest->wallet_id);

            $balanceBefore = (float) $wallet->current_balance;
            $balanceAfter = $balanceBefore + $amountToCredit;

            $wallet->current_balance = $balanceAfter;
            $wallet->save();

            $fundRequest->update([
                'status' => 'APPROVED',
                'action_by' => $reviewerId,
                'action_note' => $notes ?: 'ফান্ড রিকোয়েস্ট অনুমোদিত হয়েছে।',
                'action_at' => now(),
            ]);

            WalletTransaction::create([
                'wallet_id'       => $wallet->id,
                'performed_by'    => $reviewerId,
                'type'            => 'CREDIT',
                'amount'          => $amountToCredit,
                'balance_before'  => $balanceBefore,
                'balance_after'   => $balanceAfter,
                'reference_type'  => FundRequest::class,
                'reference_id'    => $fundRequest->id,
                'notes'           => "ফান্ড রিকোয়েস্ট অনুমোদন #{$fundRequest->id}" . ($notes ? " - {$notes}" : ''),
            ]);

            return $fundRequest;
        });
    }

    /**
     * Reject a fund request.
     */
    public function rejectFundRequest(int $requestId, int $reviewerId, string $notes = ''): FundRequest
    {
        $fundRequest = FundRequest::findOrFail($requestId);
        $fundRequest->update([
            'status' => 'REJECTED',
            'action_by' => $reviewerId,
            'action_note' => $notes ?: 'ফান্ড রিকোয়েস্ট প্রত্যাখ্যান করা হয়েছে।',
            'action_at' => now(),
        ]);
        return $fundRequest;
    }
}
