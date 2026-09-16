<div>

    <div class="row g-4">

        <div class="col-md-4">

            <x-rpd::card title="Status">
                <dl class="row">
                    <dt class="col-4">Created at</dt>
                    <dd class="col-8"> <x-rpd::date-formatted :date="$order->created_at"></x-rpd::date-formatted></dd>

                    <dt class="col-4">Status</dt>
                    <dd class="col-8"> {{ $order->status }}</dd>
                </dl>
            </x-rpd::card>

            @if($order->shipping_address)
                <x-rpd::card title="Shipping">
                    @include('shop::includes.address_lines', ['address' => $order->shipping_address])
                </x-rpd::card>
            @endif
        </div>


        <div class="col-md-8">

            <div wire:ignore>
                <x-rpd::breadcrumbs class="breadcrumb-item" active="active" />
            </div>

            @if(in_array($order->status, ['pending_payment', 'payment_verification']) && auth()->id() === $order->user_id)

                <livewire:shop::orders.orders-checkout-embed :order="$order" />

            @else

                <x-rpd::card title="Order Detail">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Description</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->prd_code }}</td>
                                <td>{{ $item->name }}</td>
                                <td class="text-end">{{ $item->price }} {{ Cart::currency() }}</td>
                                <td class="text-end">{{ $item->qty }}</td>
                                <td class="text-end">{{ $item->subtotal }} {{ Cart::currency() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr class="tr-small">
                            <td colspan="3">&nbsp;</td>
                            <td class="text-end">Subtotal</td>
                            <td class="text-end">{{ $order->subtotal }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr class="tr-small">
                            <td colspan="3">&nbsp;</td>
                            <td class="text-end">Shipping</td>
                            <td class="text-end shipping">{{ $order->shipping }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="3">&nbsp;</td>
                            <td class="text-end">Tax @if($order->tax_rate !== null)<small class="text-muted" title="{{ $order->tax_reason }}">({{ $order->tax_final ? '' : 'estimated, ' }}{{ $order->tax_rate + 0 }}%)</small>@endif</td>
                            <td class="text-end tax">{{ $order->tax }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="3">&nbsp;</td>
                            <td class="text-end h5"><strong>Total</strong></td>
                            <td class="text-end h5 total"><strong>{{ $order->total }} {{ Cart::currency() }}</strong></td>
                        </tr>
                        </tfoot>
                    </table>
                </x-rpd::card>

            @endif

            @if($order->assignments->isNotEmpty())
                <x-rpd::card title="Delivery" class="mt-3">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>product/service</th>
                            <th style="text-transform: none;">key/serial_number</th>
                            <th>metadata</th>
                            <th>status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($order->assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->deliverable_type }}</td>
                                <td>{{ $assignment->serial_number }}</td>
                                <td>{{ $assignment->metadata }}</td>
                                <td>{{ $assignment->status }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </x-rpd::card>
            @endif

        </div>
    </div>


</div>
