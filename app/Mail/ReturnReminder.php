<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\InventoryRequestItem;

class ReturnReminder extends Mailable
{
    use Queueable, SerializesModels;

    public InventoryRequestItem $item;
    public int $days;

    public function __construct(InventoryRequestItem $item, int $days)
    {
        $this->item = $item;
        $this->days = $days;
    }

    public function build()
    {
        if ($this->days < 0) {
            $subject = 'Return overdue by '.abs($this->days).' day'.(abs($this->days) > 1 ? 's' : '');
        } elseif ($this->days === 0) {
            $subject = 'Return expected today';
        } else {
            $subject = 'Reminder: expected return in '.$this->days.' day'.($this->days>1?'s':'');
        }

        return $this->subject($subject)
            ->view('emails.return_reminder')
            ->with([
                'item' => $this->item,
                'days' => $this->days,
            ]);
    }
}
