<?php

namespace App\Modules\Shop\Commands;

use App\Modules\Shop\Services\SubscriptionService;
use Illuminate\Console\Command;

class BillSubscriptionsCommand extends Command
{
    protected $signature = 'shop:bill-subscriptions {--date= : Pretend today is this date (Y-m-d)}';

    protected $description = 'Create the pending payment of every subscription whose billing date has come; mark past due the ones unpaid beyond the grace period';

    public function handle(): int
    {
        $on = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : now();

        $billed = SubscriptionService::billDue($on);
        foreach ($billed as [$subscription, $payment]) {
            $this->line("subscription {$subscription->shortId}: payment " . ($payment->id ?? '?') . " pending, {$subscription->period} fee");
        }
        $pastDue = SubscriptionService::markPastDue($on);

        $this->info(count($billed) . ' payment(s) created, ' . $pastDue . ' subscription(s) past due.');

        return self::SUCCESS;
    }
}
