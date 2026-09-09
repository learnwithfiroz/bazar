<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\FundRequest;

class WhatsAppService
{
    /**
     * Generate 1-Click WhatsApp deep-link for daily expense submission with auto-typed Bangla message.
     */
    public static function generateExpenseReportLink(Expense $expense, ?string $recipientPhone = null, ?float $currentBalance = null): string
    {
        $dateFormatted = date('d F, Y', strtotime($expense->expense_date));
        $creatorName = $expense->creator->name ?? 'বাজার মেসেঞ্জার';

        $itemsText = "";
        foreach ($expense->items as $index => $item) {
            $num = $index + 1;
            $itemQty = (float) $item->quantity;
            $unitPrice = number_format((float) $item->unit_price, 2);
            $totalPrice = number_format((float) $item->total_price, 2);
            
            $itemsText .= "{$num}. *{$item->item_name}* ({$itemQty} {$item->unit} × ৳{$unitPrice}) = ৳{$totalPrice}\n";
        }

        $viewUrl = url("/expenses/{$expense->id}");

        $message = "🛒 *দৈনিক বাজার খরচের হিসাব বিবরণী*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📅 *তারিখ:* {$dateFormatted}\n";
        $message .= "👤 *বাজারকারী:* {$creatorName}\n";
        if ($expense->memo_no) {
            $message .= "🧾 *ক্যাশ মেমো / বিল নং:* {$expense->memo_no}\n";
        }
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 *ক্রয়কৃত পণ্যের তালিকা:*\n";
        $message .= $itemsText ?: "কোনো পণ্য যুক্ত করা হয়নি\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💰 *সর্বমোট বাজার খরচ:* ৳ " . number_format($expense->total_amount, 2) . "\n";
        
        if ($currentBalance !== null) {
            $message .= "💳 *অবশিষ্ট ওয়ালেট ব্যালেন্স:* ৳ " . number_format($currentBalance, 2) . "\n";
        }

        if ($expense->notes) {
            $message .= "📝 *নোট:* {$expense->notes}\n";
        }

        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📸 *ক্যাশ মেমোর ছবি ও পূর্ণাঙ্গ ভাউচার দেখতে:* \n";
        $message .= "🔗 {$viewUrl}\n\n";
        $message .= "_অটোমেটিক দৈনিক বাজার ও ফান্ড ম্যানেজমেন্ট সফটওয়্যার_";

        return self::formatWhatsAppUrl($recipientPhone, $message);
    }

    /**
     * Generate 1-Click WhatsApp deep-link for fund request.
     */
    public static function generateFundRequestLink(FundRequest $request, ?string $recipientPhone = null): string
    {
        $requesterName = $request->requester->name ?? 'বাজার মেসেঞ্জার';
        $currentBalance = $request->wallet->current_balance ?? 0;
        $walletUrl = url("/wallets");

        $message = "🚨 *ফান্ড বা টাকা রিচার্জের আবেদন*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "👤 *আবেদনকারী:* {$requesterName}\n";
        $message .= "💰 *প্রয়োজনীয় ফান্ডের পরিমাণ:* ৳ " . number_format($request->amount, 2) . "\n";
        $message .= "💳 *হাতে থাকা বর্তমান ব্যালেন্স:* ৳ " . number_format($currentBalance, 2) . "\n";
        if ($request->reason) {
            $message .= "📝 *ফান্ডের কারণ:* {$request->reason}\n";
        }
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🔗 *অনুমোদন করতে ভিজিট করুন:*\n{$walletUrl}\n\n";
        $message .= "_জরুরি ফান্ড নোটিফিকেশন সিস্টেম_";

        return self::formatWhatsAppUrl($recipientPhone, $message);
    }

    /**
     * Normalize Bangladeshi phone number and build https://wa.me/ URL.
     */
    public static function formatWhatsAppUrl(?string $phone, string $message): string
    {
        $phoneParam = "";
        if ($phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (!empty($cleanPhone)) {
                // If it starts with 01..., prepend 88
                if (str_starts_with($cleanPhone, '01')) {
                    $cleanPhone = '88' . $cleanPhone;
                } elseif (!str_starts_with($cleanPhone, '88')) {
                    $cleanPhone = '88' . ltrim($cleanPhone, '0');
                }
                $phoneParam = "{$cleanPhone}";
            }
        }

        $encodedText = rawurlencode($message);
        if ($phoneParam) {
            return "https://wa.me/{$phoneParam}?text={$encodedText}";
        }
        return "https://wa.me/?text={$encodedText}";
    }
}
