<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountRequestReceived;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_account_request_and_queues_admin_mail()
    {
        Mail::fake();

        $post = [
            'name' => 'Test Applicant',
            'email' => 'applicant@example.test',
            'phone' => '09171234567',
            'department' => 'Ops',
            'role' => 'requestor',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            'website' => '',
        ];

        $response = $this->post('/register', $post);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('account_requests', ['email' => 'applicant@example.test']);

        Mail::assertQueued(AccountRequestReceived::class, function ($mail) {
            return isset($mail->request) && $mail->request->email === 'applicant@example.test';
        });
    }

    public function test_resubmit_after_rejected_updates_existing_request()
    {
        Mail::fake();

        // create a prior rejected request
        $existing = \App\Models\AccountRequest::create([
            'name' => 'Old Applicant',
            'email' => 'resubmit@example.test',
            'password_hash' => bcrypt('oldpass'),
            'department' => 'Ops',
            'position' => null,
            'phone' => '09170000000',
            'message' => 'old',
            'proof_path' => null,
            'status' => 'rejected',
            'requested_role' => 'requestor',
            'justification' => null,
        ]);

        $post = [
            'name' => 'New Applicant',
            'email' => 'resubmit@example.test',
            'phone' => '09171234567',
            'department' => 'Ops',
            'role' => 'requestor',
            'password' => 'newpass',
            'password_confirmation' => 'newpass',
            'website' => '',
        ];

        $response = $this->post('/register', $post);
        $response->assertSessionHas('success');

        // still only one row, and its status should be pending and name updated
        $this->assertDatabaseCount('account_requests', 1);
        $this->assertDatabaseHas('account_requests', ['email' => 'resubmit@example.test', 'status' => 'pending', 'name' => 'New Applicant']);

        Mail::assertQueued(AccountRequestReceived::class, function ($mail) {
            return isset($mail->request) && $mail->request->email === 'resubmit@example.test';
        });
    }

    public function test_cannot_register_when_pending_request_exists()
    {
        Mail::fake();

        // create a prior pending request
        $existing = \App\Models\AccountRequest::create([
            'name' => 'Pending Applicant',
            'email' => 'pending@example.test',
            'password_hash' => bcrypt('oldpass'),
            'department' => 'Ops',
            'position' => null,
            'phone' => '09170000000',
            'message' => 'old',
            'proof_path' => null,
            'status' => 'pending',
            'requested_role' => 'requestor',
            'justification' => null,
        ]);

        $post = [
            'name' => 'New Applicant',
            'email' => 'pending@example.test',
            'phone' => '09171234567',
            'department' => 'Ops',
            'role' => 'requestor',
            'password' => 'newpass',
            'password_confirmation' => 'newpass',
            'website' => '',
        ];

        $response = $this->post('/register', $post);
        $response->assertSessionHasErrors('email');

        // ensure still one row and unchanged status
        $this->assertDatabaseHas('account_requests', ['email' => 'pending@example.test', 'status' => 'pending', 'name' => 'Pending Applicant']);
    }
}
