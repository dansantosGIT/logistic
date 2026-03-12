<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\AccountRequest;

class AccountRequestReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $request;

    /**
     * Create a new message instance.
     */
    public function __construct(AccountRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New account request — ' . ($this->request->name ?? ''))
                    ->view('emails.account_request_received')
                    ->with(['request' => $this->request]);
    }
}
