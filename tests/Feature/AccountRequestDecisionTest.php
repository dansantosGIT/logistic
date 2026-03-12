<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\AccountRequestDecision;
use App\Models\AccountRequest;
use App\Models\User;

class AccountRequestDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_creates_user_and_queues_decision_mail()
    {
        Mail::fake();

        $ar = AccountRequest::create([
            'name' => 'Req User',
            'email' => 'requser@example.test',
            'password_hash' => bcrypt('secret'),
            'department' => 'Ops',
            'position' => null,
            'phone' => '09170000000',
            'message' => 'please',
            'proof_path' => null,
            'status' => 'pending',
            'requested_role' => 'requestor',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($admin)->post('/accounts/' . $ar->id . '/approve', ['admin_note' => 'Approved.']);
        $response->assertRedirect('/accounts');

        $this->assertDatabaseHas('users', ['email' => $ar->email]);
        $this->assertDatabaseHas('account_requests', ['id' => $ar->id, 'status' => 'approved']);

        Mail::assertQueued(AccountRequestDecision::class, function ($mail) use ($ar) {
            return $mail->request->id === $ar->id && $mail->decision === 'approved' && !empty($mail->generated_password);
        });
    }

    public function test_deny_updates_status_and_queues_denial_mail_with_admin_note()
    {
        Mail::fake();

        $ar = AccountRequest::create([
            'name' => 'Req User 2',
            'email' => 'requser2@example.test',
            'password_hash' => bcrypt('secret'),
            'department' => 'Ops',
            'position' => null,
            'phone' => '09170000001',
            'message' => 'no thanks',
            'proof_path' => null,
            'status' => 'pending',
            'requested_role' => 'requestor',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin2@example.test',
            'password' => Hash::make('adminpass'),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($admin)->post('/accounts/' . $ar->id . '/deny', ['admin_note' => 'Not a fit']);
        $response->assertRedirect('/accounts');

        $this->assertDatabaseHas('account_requests', ['id' => $ar->id, 'status' => 'rejected']);

        Mail::assertQueued(AccountRequestDecision::class, function ($mail) use ($ar) {
            return $mail->request->id === $ar->id && $mail->decision === 'denied' && $mail->admin_note === 'Not a fit';
        });
    }
}
