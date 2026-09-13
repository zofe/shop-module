
    <x-rpd::view title="subscriptions::subscription.subscription">

        <x-slot name="buttons">
            @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('subscriptions.subscriptions.table') }}" class="btn btn-primary">{{ __('companies::company.to_list') }}</a>
            @endif
        </x-slot>


        <x-rpd::modal
            name="changeItem"
            title="global.edit"
            action="saveSubscriptionItem"
        >
            <div class="row">
                <x-rpd::input col="col-md-3" model="name" label="subscriptions::subscription.service_name" />
                <x-rpd::input col="col-md-3" model="prd_code" label="subscriptions::subscription.service_code" />
                <x-rpd::input col="col-md-3" model="coin_price" label="subscriptions::subscription.coin_price" />
{{--                <x-rpd::input col="col-3" model="qty" label="global.qty" />--}}
            </div>
        </x-rpd::modal>


        <x-rpd::modal
            name="changeItemPrice"
            title="global.edit"
            action="saveSubscriptionItem"
        >
            <div class="row">
                <div class="col-3">
                    <x-rpd::label label="subscriptions::subscription.service_name"/>
                    <div>{{ $name }}</div>
                </div>
                <div class="col-3">
                    <x-rpd::label label="subscriptions::subscription.service_code"/>
                    <div>{{ $prd_code }}</div>
                </div>
                <x-rpd::input col="col-3" model="price" label="global.net_amount_per_unit" />
                <x-rpd::input col="col-3" model="qty" label="global.qty" />
            </div>
        </x-rpd::modal>


        <x-rpd::modal
            name="addItem"
            title="global.add"
            action="addCustomSubscriptionItem"
        >
            <div class="row">
                <x-rpd::input col="col-4" model="newName" label="subscriptions::subscription.service_name" />
                <x-rpd::input col="col-4" model="newCode" label="subscriptions::subscription.service_code" />
                <x-rpd::input col="col-4" model="newPrice" label="global.net_amount_per_unit" />
            </div>
        </x-rpd::modal>


        <x-rpd::modal
            name="confirmRemoveItem"
            title="Rimuovi"
            action="removeItem"
        >
            <div>
                {{ __("subscriptions::subscription.confirm_remove_service") }}

                <div class="fw-bold pt-1">
                    {{ $rmItemDesc }}
                </div>
            </div>
        </x-rpd::modal>


        <div class="row">
            <div class="col-md-8">
                <x-rpd::card title="subscriptions::subscription.subscription_detail">
                    <div>
                        <x-slot name="buttons">
                            @section('add-subscription-item')
{{--                                @if(auth()->user()->hasRoleOrPermission('admin|administrative|edit subscriptions') )--}}
{{--                                    <a wire:click.prevent="addItem" href="#" class="btn btn-sm btn-link float-right pl-2">--}}
{{--                                        <i class="far fa-plus-square"></i> aggiungi custom--}}
{{--                                    </a>--}}
{{--                                @endif--}}
                            @show
                        </x-slot>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="bg-light">
                                <tr>
                                    <th>{{ __('subscriptions::subscription.service_name') }}</th>
                                    <th>{{ __('subscriptions::subscription.service_code') }}</th>
                                    <th class="text-nowrap">{{ __('global.created_at') }}</th>
                                    <th class="text-end">{{ __('subscriptions::subscription.qty') }}</th>
                                    <th class="text-end">{{ __('subscriptions::subscription.coin_price') }}</th>
                                    <th class="text-end">{{ __('subscriptions::subscription.coin_subtotal') }}</th>
                                    <th ></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($items as $item)
                                    <tr class="tr-small sub-row-status-{{ $item->preact_status }}" wire:key="ci_{{$item->id}}">

                                        <td class="small">
                                            @if($item->model_type=='App\Models\Router' && $item->router)
                                                @if($item->router->location)
                                                   <a href="{{route_lang('routers.routers.view', $item->router->location)}}">
                                                       {{ $item->name}}
                                                   </a>
                                                @else
                                                    {{ $item->name}} ({{ $item->router->name }})
                                                @endif
                                            @else
                                                {{ $item->name}}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->prd_code}}
                                            @if($item->end_try)
                                                <div class="small text-danger">{{ __('global.on_trial') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->created_at->format('d/m/Y')}}
                                            @if($item->end_try)
                                                <div class="small text-danger"> {{ __('global.expire') }} {{ $item->end_try->format('d/m/Y')}}</div>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            {{ $item->qty }}
                                        </td>
                                        <td class="text-end text-nowrap">
                                            {{  $item->coin_price }}
                                        </td>
                                        <td class="text-end text-nowrap">
                                            {{  $item->coin_subtotal }}
                                        </td>
                                        <td class="text-nowrap">
                                            @if(auth()->user()->hasRoleOrPermission('admin|administrative|edit subscriptions'))


                                                @if($item->isEditable())
                                                    <a wire:click.prevent="changeItem({{ $item->id }})" href="#" class="btn btn-sm btn-link">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                @elseif($item->isPriceEditable())
                                                    <a wire:click.prevent="changeItemPrice({{ $item->id }})" href="#" class="btn btn-sm btn-link">
                                                        fa-edit
                                                    </a>
                                                @endif

{{--                                                @if($item->isDeletable())--}}
{{--                                                    <a wire:click.prevent="confirmRemoveItem({{ $item->id }})" href="#" class="btn btn-sm btn-link">--}}
{{--                                                        <i class="far fa-trash-alt"></i>--}}
{{--                                                    </a>--}}
{{--                                                @endif--}}

                                            @endif
                                        </td>
                                    </tr>
                                @endforeach



                                </tbody>
                                <tfoot>
                                <tr class="tr-small">
                                    <td colspan="5" class="text-end">{{ __('payments::payment.subtotal') }}</td>
                                    <td class="text-end text-nowrap">{{ $subscription->coin_subtotal }} </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-right"><strong>{{ __('payments::payment.total') }}</strong></td>
                                    <td class="text-end text-nowrap"><strong>{{ $subscription->coin_total }} <i class="fas fa-coins text-warning"></i></strong></td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="row">
                            {{ $items->links() }}
                        </div>
                    </div>
                </x-rpd::card>


{{--                @include('subscriptions::Subscriptions.views.subscriptions_payments', ['items'=>$payments])--}}

            </div>

            <div class="col-md-4">

                <x-rpd::card title="subscriptions::subscription.status">

                    <dl class="row">
                        <dt class="col-5">{{ __('subscriptions::subscription.description') }}</dt>
                        <dd class="col-7 small">
                            <div>{{ $subscription->computedDescription }}</div>
                            @if(auth()->user()->hasRole('admin'))
                                <div class="pb-1">
                                    <x-company.admin-link :company="$subscription->company"/>
                                </div>
                            @endif
                        </dd>

                        <dt class="col-5">{{ __('global.created_at') }}</dt>
                        <dd class="col-7">{{ $subscription->created_at->format('d/m/Y H:i:s') }}</dd>

                        <dt class="col-5">{{ __('subscriptions::subscription.status') }}</dt>
                        <dd class="col-7">
                            active
{{--                            {{ $subscription->status }}--}}
                        </dd>
                    </dl>

                </x-rpd::card>


            </div>
        </div>

    </x-rpd::view>
