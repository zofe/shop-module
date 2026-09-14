<?php

namespace App\Modules\Shop\Commands;

use App\Modules\Shop\Services\SubscriptionService;
use Illuminate\Console\Command;

class RenewSubscriptionsCommand extends Command
{
    protected $signature = 'shop:renew-subscriptions {--date= : Pretend today is this date (Y-m-d)}';

    protected $description = 'Create a renewal order for every subscription managed by the shop whose next billing date has come';

    public function handle(): int
    {
        $on = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : now();
        $orders = SubscriptionService::renewDue($on);

        foreach ($orders as $order) {
            $this->line("renewal order {$order->shortId} for subscription {$order->subscription->shortId}: {$order->total}");
        }
        $this->info(count($orders) . ' renewal order(s) created.');

        return self::SUCCESS;
    }
}
