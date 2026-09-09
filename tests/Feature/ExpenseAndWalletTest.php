<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Expense;
use App\Models\FundRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseAndWalletTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('লগইন প্যানেল');
    }

    public function test_principal_can_view_executive_dashboard(): void
    {
        $principal = User::where('role', 'principal')->first();
        $response = $this->actingAs($principal)->get('/');
        $response->assertStatus(200);
        $response->assertSee('এক্সিকিউটিভ মনিটরিং ও ফান্ড ড্যাশবোর্ড');
    }

    public function test_messenger_can_view_mobile_dashboard(): void
    {
        $messenger = User::where('role', 'messenger')->first();
        $response = $this->actingAs($messenger)->get('/');
        $response->assertStatus(200);
        $response->assertSee('আমার পেটি-ক্যাশ ওয়ালেট');
    }

    public function test_messenger_can_submit_expense_and_deducts_wallet(): void
    {
        $messenger = User::where('role', 'messenger')->first();
        $wallet = $messenger->wallet;
        $initialBalance = $wallet->current_balance;

        $response = $this->actingAs($messenger)->post('/expenses', [
            'expense_date' => now()->toDateString(),
            'title' => 'টেস্ট বাজার খরচ',
            'items' => [
                ['name' => 'ইলিশ মাছ', 'category' => 'মাছ ও মাংস', 'quantity' => 1, 'unit' => 'কেজি', 'unit_price' => 1200],
                ['name' => 'পেঁয়াজ', 'category' => 'কাঁচাবাজার', 'quantity' => 2, 'unit' => 'কেজি', 'unit_price' => 70],
            ],
            'notes' => 'টেস্ট এন্ট্রি',
        ]);

        $response->assertRedirect();

        $wallet->refresh();
        $expectedBalance = $initialBalance - 1340.00; // 1200 + (2 * 70)
        $this->assertEquals($expectedBalance, $wallet->current_balance);

        $this->assertDatabaseHas('expenses', [
            'created_by' => $messenger->id,
            'title' => 'টেস্ট বাজার খরচ',
            'total_amount' => 1340.00,
        ]);
    }

    public function test_principal_can_top_up_messenger_wallet(): void
    {
        $principal = User::where('role', 'principal')->first();
        $messenger = User::where('role', 'messenger')->first();
        $wallet = $messenger->wallet;
        $initialBalance = $wallet->current_balance;

        $response = $this->actingAs($principal)->post('/wallets/top-up', [
            'wallet_id' => $wallet->id,
            'amount' => 2500.00,
            'notes' => 'জরুরি ফান্ড রিচার্জ',
        ]);

        $response->assertRedirect();
        $wallet->refresh();
        $this->assertEquals($initialBalance + 2500.00, $wallet->current_balance);
    }

    public function test_messenger_can_request_funds_and_principal_can_approve(): void
    {
        $messenger = User::where('role', 'messenger')->first();
        $principal = User::where('role', 'principal')->first();
        $wallet = $messenger->wallet;
        $initialBalance = $wallet->current_balance;

        // 1. Messenger requests funds
        $this->actingAs($messenger)->post('/fund-requests', [
            'amount' => 1000.00,
            'reason' => 'অতিরিক্ত খরচের জন্য ফান্ড প্রয়োজন',
        ]);

        $fundRequest = FundRequest::where('requested_by', $messenger->id)->latest()->first();
        $this->assertNotNull($fundRequest);
        $this->assertEquals('PENDING', $fundRequest->status);

        // 2. Principal approves request
        $this->actingAs($principal)->patch("/fund-requests/{$fundRequest->id}/action", [
            'action' => 'APPROVE',
            'notes' => 'অনুমোদিত',
        ]);

        $fundRequest->refresh();
        $wallet->refresh();

        $this->assertEquals('APPROVED', $fundRequest->status);
        $this->assertEquals($initialBalance + 1000.00, $wallet->current_balance);
    }

    public function test_day_wise_print_layout_renders(): void
    {
        $principal = User::where('role', 'principal')->first();
        $response = $this->actingAs($principal)->get('/reports/print-day?date=' . now()->toDateString());
        $response->assertStatus(200);
        $response->assertSee('দৈনিক বাজার খরচ ও ফান্ড ভাউচার হিসাব বিবরণী');
    }
}
