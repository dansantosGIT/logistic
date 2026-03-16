<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\InventoryRequestItem;
use App\Models\User;
use App\Models\ReminderLog;
use Illuminate\Support\Facades\Log;
use App\Mail\ReturnReminder;
use Illuminate\Support\Facades\Mail;

class SendReturnReminders extends Command
{
    protected $signature = 'reminders:send-expected-returns';
    protected $description = 'Send reminder emails for expected return dates (due-soon & overdue)';

    public function handle()
    {
        $this->info('Starting expected-return reminders');

        $thresholds = [4, 1]; // days before due
        $useLogMailer = filter_var(env('MAIL_FORCE_LOG', false), FILTER_VALIDATE_BOOLEAN);

        // handle due-soon reminders
        foreach ($thresholds as $days) {
            $targetDate = Carbon::now()->addDays($days)->toDateString();

            $items = InventoryRequestItem::with('request')
                ->whereNotNull('return_date')
                ->whereDate('return_date', $targetDate)
                ->where(function($q){
                    $q->whereIn('status', ['approved','waiting'])
                      ->orWhere('issued_quantity', '>', 0);
                })
                ->get();

            foreach ($items as $item) {
                try {
                    // skip if we already sent this exact reminder (daysRel) for this item
                    $daysRel = Carbon::now()->diffInDays($item->return_date, false);
                    if (ReminderLog::where('inventory_request_item_id', $item->id)->where('days', $daysRel)->exists()) {
                        $this->line('Already sent reminder for item id='.$item->id.' (in '.$daysRel.' days)');
                        continue;
                    }
                    $emails = [];

                    // requestor (if linked to a user)
                    $req = $item->request;
                    if ($req && !empty($req->requester_user_id)) {
                        $u = User::find($req->requester_user_id);
                        if ($u && !empty($u->email)) $emails[] = $u->email;
                    }

                    // admins
                    $adminEmails = User::whereRaw("lower(role) = 'admin'")
                        ->whereNotNull('email')
                        ->pluck('email')
                        ->toArray();

                    $emails = array_values(array_unique(array_merge($emails, $adminEmails)));

                    if (empty($emails)) {
                        $this->line('No recipients for item id='.$item->id); continue;
                    }

                    // Send synchronously so reminders are delivered when the scheduled command runs
                    if ($useLogMailer) {
                        Log::info('Simulating reminder send (log mailer)', ['item_id' => $item->id, 'days' => $daysRel, 'recipients' => $emails]);
                        Mail::mailer('log')->to($emails)->send(new ReturnReminder($item, $daysRel));
                    } else {
                        Mail::to($emails)->send(new ReturnReminder($item, $daysRel));
                    }
                    ReminderLog::create(['inventory_request_item_id' => $item->id, 'days' => $daysRel]);
                    $this->line('Sent reminder for item id='.$item->id.' (in '.$daysRel.' days)');
                } catch (\Throwable $e) {
                    $this->error('Failed to send reminder for item id='.$item->id.': '.$e->getMessage());
                }
            }
        }

        // handle overdue reminders (items with return_date before today)
        // limit to items overdue within the last 30 days to avoid long-tail spam
        $overdueWindowDays = 30;
        $today = Carbon::now()->startOfDay();
        $minDate = $today->copy()->subDays($overdueWindowDays)->toDateString();

        $overdueItems = InventoryRequestItem::with('request')
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', $today->toDateString())
            ->whereDate('return_date', '>=', $minDate)
            ->where(function($q){
                $q->whereIn('status', ['approved','waiting'])
                  ->orWhere('issued_quantity', '>', 0);
            })
            ->get();

        foreach ($overdueItems as $item) {
            try {
                $emails = [];
                $req = $item->request;
                if ($req && !empty($req->requester_user_id)) {
                    $u = User::find($req->requester_user_id);
                    if ($u && !empty($u->email)) $emails[] = $u->email;
                }

                $adminEmails = User::whereRaw("lower(role) = 'admin'")
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();
                $emails = array_values(array_unique(array_merge($emails, $adminEmails)));

                if (empty($emails)) {
                    $this->line('No recipients for overdue item id='.$item->id); continue;
                }

                $daysRel = Carbon::now()->diffInDays($item->return_date, false);
                if (ReminderLog::where('inventory_request_item_id', $item->id)->where('days', $daysRel)->exists()) {
                    $this->line('Already sent overdue reminder for item id='.$item->id.' ('.$daysRel.' days)');
                    continue;
                }
                if ($useLogMailer) {
                    Log::info('Simulating overdue reminder send (log mailer)', ['item_id' => $item->id, 'days' => $daysRel, 'recipients' => $emails]);
                    Mail::mailer('log')->to($emails)->send(new ReturnReminder($item, $daysRel));
                } else {
                    Mail::to($emails)->send(new ReturnReminder($item, $daysRel));
                }
                ReminderLog::create(['inventory_request_item_id' => $item->id, 'days' => $daysRel]);
                $this->line('Sent overdue reminder for item id='.$item->id.' ('.$daysRel.' days)');
            } catch (\Throwable $e) {
                $this->error('Failed to send overdue reminder for item id='.$item->id.': '.$e->getMessage());
            }
        }

        $this->info('Expected-return reminders complete');
        return 0;
    }
}
