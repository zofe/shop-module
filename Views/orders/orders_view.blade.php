

    <div class="row">
        <div class="col-md-8">
            <x-rpd::card title="Order Detail">
{{--                <x-slot name="buttons">--}}
{{--                    <x-rpd::button label="Add Product/Service" color="outline-primary" target="addItem" size="sm" class="m-1" />--}}
{{--                </x-slot>--}}

                <livewire:shop::orders.orders-modal-edit-embed
                    :order="$order->id"
                ></livewire:shop::orders.orders-modal-edit-embed>

                <livewire:shop::orders.orders-assign-item-modal />
                <livewire:shop::orders.orders-ship-modal />


                <table class="table">
                    <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Description</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Quantity</th>
                        <th></th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->prd_code }} </td>
                            <td>{{ $item->name }}</td>
                            <td class="text-end">{{ $item->price }} {{ Cart::currency() }}</td>
                            <td class="text-end">{{ $item->qty }}</td>
                            <td class="text-end"><x-rpd::icon name="edit" click="$dispatch('editItem',{itemId: '{{$item->id}}'})" /></td>
                            <td class="text-end">{{ $item->subtotal }} {{ Cart::currency() }}</td>
                        </tr>
                    @endforeach
                    </tbody>

                    <tfoot>
                    <tr class="tr-small">
                        <td colspan="4">&nbsp;</td>
                        <td class="text-end">Subtotal</td>
                        <td class="text-end">{{ $order->subtotal }} {{ Cart::currency() }}</td>
                    </tr>
                    <tr class="tr-small">
                        <td colspan="4">&nbsp;</td>
                        <td class="text-end">Shipping</td>
                        <td class="text-end shipping">{{ $order->shipping }} {{ Cart::currency() }}</td>
                    </tr>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td class="text-end">Tax @if($order->tax_rate !== null)<small class="text-muted" title="{{ $order->tax_reason }}">({{ $order->tax_final ? '' : 'estimated, ' }}{{ $order->tax_rate + 0 }}%)</small>@endif</td>
                        <td class="text-end tax">{{ $order->tax }} {{ Cart::currency() }}</td>
                    </tr>
                    <tr>
                        <td colspan="4">&nbsp;</td>
                        <td class="text-end h5"><strong>Total</strong></td>
                        <td class="text-end h5 total"><strong>{{ $order->total }} {{ Cart::currency() }}</strong></td>
                    </tr>
                    </tfoot>


                </table>

{{--                <x-rpd::modal--}}
{{--                    name="editItem"--}}
{{--                    title="Edit Order Item"--}}
{{--                    action="saveItem"--}}
{{--                >--}}
{{--                    <div>--}}
{{--                        <x-rpd::input inline="true" model="price" label="Price" />--}}
{{--                        <x-rpd::input inline="true" model="qty" label="Qty" />--}}
{{--                        <x-rpd::input inline="true" model="shipping" label="Shipping" />--}}
{{--                    </div>--}}
{{--                </x-rpd::modal>--}}

{{--                <x-rpd::modal--}}
{{--                    name="addItem"--}}
{{--                    title="Add Order Item"--}}
{{--                    action="addItem"--}}
{{--                >--}}
{{--                    <div>--}}
{{--                        <x-rpd::select-list inline="true" model="new_item" label="Product/Service" endpoint="/ajax/available-pricelist-item"  />--}}
{{--                        <x-rpd::input inline="true" model="new_qty" label="Qty" />--}}
{{--                    </div>--}}
{{--                </x-rpd::modal>--}}


            </x-rpd::card>

            <x-rpd::card title="Delivery">
                <table class="table">
                    <thead>
                    <tr>
                        <th>product/service</th>
                        <th style="text-transform: none;">serial number / service</th>
                        <th>state</th>
                        <th>delivery</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->assignments as $assignment)

                        <livewire:shop::orders.orders-assignment-embed
                            :assignmentId="$assignment->id"
                            :key="encrypt($assignment->id.'|'.$loop->index)"
                        />

                    @endforeach
                    </tbody>
                </table>
            </x-rpd::card>

        </div>
        <div class="col-md-4">
            <x-rpd::card title="Status">
                <dl class="row">
                    <dt class="col-4">Created at</dt>
                    <dd class="col-8"><x-rpd::date-formatted :date="$order->created_at"></x-rpd::date-formatted></dd>

                    <dt class="col-4">Status</dt>
                    <dd class="col-8">{{ $order->status }}</dd>

                    @if($order->shipped_at)
                        <dt class="col-4">Shipped</dt>
                        <dd class="col-8">
                            <x-rpd::date-formatted :date="$order->shipped_at"></x-rpd::date-formatted>
                            @if($order->carrier || $order->tracking_code || $order->shipping_document)
                                <div class="small text-muted">{{ $order->carrier }} {{ $order->tracking_code }}@if($order->shipping_document) · DDT {{ $order->shipping_document }}@endif</div>
                            @endif
                        </dd>
                    @endif

                    @php $payment = class_exists(\App\Modules\Payments\Models\Payment::class) ? \App\Modules\Payments\Models\Payment::where('order_id', $order->id)->latest()->first() : null; @endphp
                    @if($payment)
                        <dt class="col-4">Payment</dt>
                        <dd class="col-8">
                            <a href="{{ route('payments.view', $payment) }}" class="small">
                                <span class="badge bg-{{ match($payment->status) {
                                    'confirmed' => 'success',
                                    'failed','cancelled' => 'danger',
                                    default => 'warning text-dark',
                                } }}">{{ $payment->status }}</span>
                                <span class="text-muted ms-1">{{ $payment->gateway }}</span>
                            </a>
                        </dd>
                    @endif
                </dl>


                <livewire:workflow::workflow-table-embed
                    workfloable-type="order"
                    workfloable-id="{{ $order->id }}"
                    editable="true"
                    showHistory="true"
                />
            </x-rpd::card>

            <x-rpd::card title="Customer">
                <dl class="row">
                    @if($order->company)
                        <dt class="col-4">Company</dt>
                        <dd class="col-8">
                            {{ $order->company->business_name }}
                            <x-rpd::nav-link icon="address-card" :label="$order->company->business_name" route="companies.view" :params="$order->company_id" />
                        </dd>
                    @endif
                    @if($order->user)
                        <dt class="col-4">User</dt>
                        <dd class="col-8">
                            <x-rpd::nav-link icon="user" :label="$order->user->name" name="edit" route="auth.users.view" :params="$order->user_id" />
                            @canImpersonate
                            @if($order->user->canBeImpersonated())
                                <a href="{{ route('orders.impersonate-owner', $order) }}" class="btn btn-xsm btn-link text-muted ms-1" title="View order as this customer">
                                    <i class="fas fa-user-secret"></i>
                                </a>
                            @endif
                            @endCanImpersonate
                        </dd>
                    @endif

                    @if($order->shipping_address)
                        <dt class="col-4">Shipping</dt>
                        <dd class="col-8">
                            @include('shop::includes.address_lines', ['address' => $order->shipping_address])
                        </dd>
                    @endif

                </dl>
            </x-rpd::card>
        </div>
    </div>

