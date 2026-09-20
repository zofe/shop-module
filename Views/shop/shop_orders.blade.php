<div>

    <div class="row g-4">

        <div class="col-md-4">

            <x-rpd::card title="Your orders">
                @php $mine = \App\Modules\Shop\Models\Order::where('user_id', auth()->id()); @endphp
                <dl class="row mb-0">
                    <dt class="col-7">{{ __('Orders') }}</dt><dd class="col-5 text-end">{{ (clone $mine)->count() }}</dd>
                    <dt class="col-7">{{ __('Waiting for payment') }}</dt><dd class="col-5 text-end">{{ (clone $mine)->whereIn('status', ['pending_payment', 'payment_verification'])->count() }}</dd>
                    <dt class="col-7">{{ __('In progress') }}</dt><dd class="col-5 text-end">{{ (clone $mine)->whereIn('status', ['payment_done', 'in_process', 'shipped'])->count() }}</dd>
                    <dt class="col-7">{{ __('Completed') }}</dt><dd class="col-5 text-end">{{ (clone $mine)->where('status', 'completed')->count() }}</dd>
                </dl>
            </x-rpd::card>

        </div>


        <div class="col-md-8">

            <div wire:ignore>
                <x-rpd::breadcrumbs class="breadcrumb-item" active="active" />
            </div>

            <x-rpd::card>

                <x-rpd::table
                    title="Orders"
                    :items="$items"
                >
{{--                    <x-slot name="filters">--}}
{{--                        <x-rpd::input col="col" debounce="350" model="search"  placeholder="search..." />--}}
{{--                    </x-slot>--}}

{{--                    <x-slot name="buttons">--}}
{{--                        <x-rpd::button label="Reset" route="orders.table" color="outline-dark" />--}}
{{--                    </x-slot>--}}


                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ __('products') }}</th>
                            <th>{{ __('status') }}</th>
                            <th><x-rpd::sort model="created_at" label="created_at" /></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($items as $order)
                            <tr>
                                <td>
                                    @foreach ($order->items as $itm)
                                        <span class="badge bg-primary position-relative">
                                {{ $itm->name }}
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info"> {{ round($itm->qty) }}</span>
                            </span>
                                    @endforeach
                                </td>
                                <td>
                                    {{ $order->status }}
                                </td>

                                <td class="small">
                                    <x-rpd::date-formatted :date="$order->created_at"></x-rpd::date-formatted>
                                </td>
                                <td>
                                    <x-rpd::icon name="eye" route="shop.order" :params="$order->id" />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </x-rpd::table>
            </x-rpd::card>

        </div>
    </div>



