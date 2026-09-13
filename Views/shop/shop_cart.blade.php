<div>

    <div class="row g-4">

        <div class="col-md-4">


            <x-rpd::card title="Finalize Order">
                @if(Auth::user())

                    @include('shop::includes.addresses')

                @else

                    <div class="text-center py-5 text-warning">

                        please

                        <a class="text-warning" href="{{ route('login') }}">log in</a>

                        @if (Route::has('register'))
                            <a class="nav-link" href="{{ route('register') }}">register an account</a>
                        @endif

                        to proceed
                    </div>

                @endif
            </x-rpd::card>

        </div>



        <div class="col-md-8">

            <div wire:ignore>
                <x-rpd::breadcrumbs class="breadcrumb-item" active="active" />
            </div>


            <x-rpd::card>

                <x-rpd::table
                    title="Cart"
                    :items="$items"
                >

                    <x-slot name="buttons">

                    </x-slot>


                    <table class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>SKU</th>
                            <th>Description</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-center">Change</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($items as $item)
                            @php $imagePath = $item->model?->product?->image_path; @endphp
                            <tr>
                                <td style="width:52px;">
                                    @if($imagePath)
                                        <img src="{{ Storage::url(\Illuminate\Support\Str::beforeLast($imagePath, '.jpg') . '_thumb.jpg') }}"
                                             style="height:44px;width:44px;object-fit:cover;border-radius:6px;"
                                             alt="">
                                    @endif
                                </td>
                                <td>{{ $item->sku }} </td>
                                <td>{{ $item->name }} <span class="small">{{ $item->description }}</span></td>
                                <td class="text-end">{{ $item->price() }} {{ Cart::currency() }}</td>
                                <td class="text-end">{{ $item->qty }}</td>
                                <td class="text-center">
                                    <input wire:change="updateItem('{{$item->rowId}}', $event.target.value)"
                                           type="number"  min="1" max="50" value="{{ $item->qty }}" class="form-control rounded-end">

                                    <a wire:click.prevent="removeItem('{{$item->rowId}}')" href="#">remove</a>
                                </td>
                                <td class="text-end">{{ $item->subtotal() }}  {{ Cart::currency() }}</td>
                            </tr>
                        @endforeach
                        </tbody>

                        <tfoot>
                        <tr class="tr-small">
                            <td colspan="5">&nbsp;</td>
                            <td class="text-end">Subtotal</td>
                            <td class="text-end">{{ Cart::subtotal() }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr class="tr-small">
                            <td colspan="5">&nbsp;</td>
                            <td class="text-end">Shipping</td>
                            <td class="text-end shipping">{{ Cart::shipping() }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="5">&nbsp;</td>
                            <td class="text-end">Tax <small class="text-muted" title="{{ $estimate->reason }}">(estimated, {{ $estimate->rate + 0 }}%)</small></td>
                            <td class="text-end tax">{{ Cart::tax() }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="5">&nbsp;</td>
                            <td class="text-end h5"><strong>Total</strong></td>
                            <td class="text-end h5 total"><strong>{{ Cart::total() }} {{ Cart::currency() }}</strong></td>
                        </tr>
                        </tfoot>


                    </table>

                </x-rpd::table>


                @if(Cart::content()->count() && auth()->user())
                    @if(session('cart_error'))
                        <div class="alert alert-warning py-2 my-2">{{ session('cart_error') }}</div>
                    @endif
                    @if($requiresShipping && ! $addressId)
                        <div class="text-center text-muted small my-2">
                            <i class="fas fa-truck me-1"></i> This order contains physical goods: add and choose a shipping address to continue.
                        </div>
                    @else
                        <div class="text-center">
                            <a class="btn btn-primary my-2" wire:click.prevent="makeOrder">Make Order</a>
                        </div>
                    @endif
                @endif

            </x-rpd::card>


        </div>

    </div>



</div>


