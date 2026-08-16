<div>
    <div class="alert alert-warning">
        <div class="d-flex justify-content-between">
            <div>
                <i class="fas fa-exclamation-triangle"></i>
                <strong>{{ __('subscriptions::subscription.try_buy_only_few_days', ['days'=>$subscriptionItem->remainingTrialDays()]) }}</strong>
            </div>
            <div>

                <x-rpd::button
                    label="subscriptions::subscription.try_buy_keep_using"
                    target="keepUsing"
                    color="primary"
                    size="sm"
                    icon="coins"
                />
            </div>
        </div>
    </div>

    <x-rpd::modal
        name="keepUsing"
        title="subscriptions::subscription.try_buy_keep_using"
        action="toggleTrial"
    >
        <div>

            <x-coin.service-price :company="$company" :service_id="$service_id" :onprem="$onprem" :starting_date="$subscriptionItem->end_try" />


            <div class="py-4 text-center text-success">
                {{ __('subscriptions::subscription.try_buy_keep_using_expl') }}
            </div>



        </div>
    </x-rpd::modal>

</div>

