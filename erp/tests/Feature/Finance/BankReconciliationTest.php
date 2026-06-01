<?php

use App\Models\User;
use App\Modules\Core\Models\Tenant;
use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\BankTransaction;
use App\Modules\Finance\Models\Contact;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceItem;
use App\Modules\Finance\Models\Payment;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->tenant = Tenant::create(['name' => 'Bank Co', 'slug' => 'bank-co']);
    $this->admin  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->admin->assignRole('super-admin');
    $this->staff  = User::factory()->create(['tenant_id' => $this->tenant->id]);
    $this->staff->assignRole('staff');
});

test('bank accounts index is accessible', function () {
    $this->actingAs($this->admin)
        ->get('/finance/bank-accounts')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/BankAccounts/Index'));
});

test('can create a bank account', function () {
    $this->actingAs($this->admin)
        ->post('/finance/bank-accounts', [
            'name'            => 'Main Checking',
            'bank_name'       => 'First Bank',
            'account_number'  => '123456',
            'currency_code'   => 'USD',
            'opening_balance' => 5000,
        ])
        ->assertSessionHasNoErrors();

    expect(BankAccount::where('name', 'Main Checking')->where('tenant_id', $this->tenant->id)->exists())->toBeTrue();
});

test('bank account show page is accessible', function () {
    $account = BankAccount::create(['tenant_id' => $this->tenant->id, 'name' => 'Savings', 'currency_code' => 'USD', 'opening_balance' => 0]);

    $this->actingAs($this->admin)
        ->get("/finance/bank-accounts/{$account->id}")
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p->component('Finance/BankAccounts/Show'));
});

test('balance accessor includes transactions', function () {
    $account = BankAccount::create(['tenant_id' => $this->tenant->id, 'name' => 'Test', 'currency_code' => 'USD', 'opening_balance' => 1000]);
    BankTransaction::create(['tenant_id' => $this->tenant->id, 'bank_account_id' => $account->id, 'transaction_date' => now(), 'amount' => 500, 'reconciled' => false]);
    BankTransaction::create(['tenant_id' => $this->tenant->id, 'bank_account_id' => $account->id, 'transaction_date' => now(), 'amount' => -200, 'reconciled' => false]);

    expect($account->balance)->toBe(1300.0);
});

test('csv import creates bank transactions', function () {
    $account = BankAccount::create(['tenant_id' => $this->tenant->id, 'name' => 'Import Test', 'currency_code' => 'USD', 'opening_balance' => 0]);

    $csvContent = "date,description,amount,reference\n2026-06-01,Payment received,500.00,REF-001\n2026-06-02,Office supplies,-120.50,REF-002\n";
    $file = UploadedFile::fake()->createWithContent('statement.csv', $csvContent);

    $this->actingAs($this->admin)
        ->post("/finance/bank-accounts/{$account->id}/import", ['file' => $file])
        ->assertSessionHasNoErrors();

    expect(BankTransaction::where('bank_account_id', $account->id)->count())->toBe(2);
    expect((float) BankTransaction::where('bank_account_id', $account->id)->where('reference', 'REF-001')->first()->amount)->toBe(500.0);
    expect((float) BankTransaction::where('bank_account_id', $account->id)->where('reference', 'REF-002')->first()->amount)->toBe(-120.5);
});

test('reconciliation index shows unreconciled transactions', function () {
    $account = BankAccount::create(['tenant_id' => $this->tenant->id, 'name' => 'Recon', 'currency_code' => 'USD', 'opening_balance' => 0]);
    BankTransaction::create(['tenant_id' => $this->tenant->id, 'bank_account_id' => $account->id, 'transaction_date' => now(), 'amount' => 300, 'reconciled' => false]);
    BankTransaction::create(['tenant_id' => $this->tenant->id, 'bank_account_id' => $account->id, 'transaction_date' => now(), 'amount' => 100, 'reconciled' => true]);

    $this->actingAs($this->admin)
        ->get('/finance/reconciliation')
        ->assertStatus(200)
        ->assertInertia(fn ($p) => $p
            ->component('Finance/Reconciliation/Index')
            ->has('transactions.data', 1)
        );
});

test('can match a transaction to a payment', function () {
    $contact = Contact::create(['tenant_id' => $this->tenant->id, 'name' => 'C', 'type' => 'customer']);
    $invoice = Invoice::create(['tenant_id' => $this->tenant->id, 'contact_id' => $contact->id, 'issue_date' => now(), 'status' => 'sent']);
    InvoiceItem::create(['invoice_id' => $invoice->id, 'description' => 'S', 'quantity' => 1, 'unit_price' => 100, 'tax_rate' => 0]);
    $payment = Payment::create(['tenant_id' => $this->tenant->id, 'invoice_id' => $invoice->id, 'amount' => 100, 'payment_date' => now(), 'method' => 'bank_transfer']);

    $account = BankAccount::create(['tenant_id' => $this->tenant->id, 'name' => 'Match', 'currency_code' => 'USD', 'opening_balance' => 0]);
    $txn = BankTransaction::create(['tenant_id' => $this->tenant->id, 'bank_account_id' => $account->id, 'transaction_date' => now(), 'amount' => 100, 'reconciled' => false]);

    $this->actingAs($this->admin)
        ->post("/finance/reconciliation/{$txn->id}/match", ['payment_id' => $payment->id])
        ->assertSessionHasNoErrors();

    expect($txn->fresh()->reconciled)->toBeTrue();
    expect($txn->fresh()->payment_id)->toBe($payment->id);
});

test('can unmatch a reconciled transaction', function () {
    $account = BankAccount::create(['tenant_id' => $this->tenant->id, 'name' => 'Unmatch', 'currency_code' => 'USD', 'opening_balance' => 0]);
    $txn = BankTransaction::create(['tenant_id' => $this->tenant->id, 'bank_account_id' => $account->id, 'transaction_date' => now(), 'amount' => 200, 'reconciled' => true]);

    $this->actingAs($this->admin)
        ->post("/finance/reconciliation/{$txn->id}/unmatch")
        ->assertSessionHasNoErrors();

    expect($txn->fresh()->reconciled)->toBeFalse();
});

test('staff cannot create bank accounts', function () {
    $this->actingAs($this->staff)
        ->post('/finance/bank-accounts', ['name' => 'Staff Account', 'currency_code' => 'USD', 'opening_balance' => 0])
        ->assertStatus(403);
});

test('guest cannot access bank accounts', function () {
    $this->get('/finance/bank-accounts')->assertRedirect();
});
