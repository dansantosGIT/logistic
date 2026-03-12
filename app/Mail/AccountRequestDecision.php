<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\AccountRequest;

class AccountRequestDecision extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $request;
    public $decision;
    public $admin_note;
    public $generated_password;

    public function __construct(AccountRequest $request, string $decision = 'approved', $admin_note = null, $generated_password = null)
    {
        $this->request = $request;
        $this->decision = $decision;
        $this->admin_note = $admin_note;
        $this->generated_password = $generated_password;
    }

    public function build()
    {
        $sub = $this->decision === 'approved' ? 'Account request approved' : 'Account request denied';
        return $this->subject($sub . ' — ' . ($this->request->name ?? ''))
                    ->view('emails.account_request_decision')
                    ->with([
                        'request' => $this->request,
                        'decision' => $this->decision,
                        'admin_note' => $this->admin_note,
                        'generated_password' => $this->generated_password,
                    ]);
    }
}
