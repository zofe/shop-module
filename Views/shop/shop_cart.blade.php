<div>

    <div class="row g-4">

        <div class="col-md-3">

            <div class="bg-light p-2 rounded border">

                <h5>Finalize Order</h5>

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



            </div>

        </div>



        <div class="col-md-9">

            <div class="row">
                <ol class="breadcrumb mb-3">
                    <li class="breadcrumb-item pr-3"><a href="{{ route('shop.list') }}">Home</a></li>
                    <li class="breadcrumb-item">Cart</li>
                </ol>
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
                            <tr>
                                <td>{{ $item->sku }} </td>
                                <td>{{ $item->name }} <span class="small">{{ $item->description }}</span></td>
                                <td class="text-end">{{ $item->price() }} {{ Cart::currency() }}</td>
                                <td class="text-end">{{ $item->qty }}</td>
                                <td class="text-center">
                                    <input wire:change="updateItem('{{$item->rowId}}', $event.target.value)"
                                           type="number"  min="1" max="50" value="{{ $item->qty }}">

                                    <a wire:click.prevent="removeItem('{{$item->rowId}}')" href="#">remove</a>
                                </td>
                                <td class="text-end">{{ $item->subtotal() }}  {{ Cart::currency() }}</td>
                            </tr>
                        @endforeach
                        </tbody>

                        <tfoot>
                        <tr class="tr-small">
                            <td colspan="4">&nbsp;</td>
                            <td class="text-end">Subtotal</td>
                            <td class="text-end">{{ Cart::subtotal() }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr class="tr-small">
                            <td colspan="4">&nbsp;</td>
                            <td class="text-end">Shipping</td>
                            <td class="text-end shipping">{{ Cart::shipping() }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">&nbsp;</td>
                            <td class="text-end">Tax</td>
                            <td class="text-end tax">{{ Cart::tax() }} {{ Cart::currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">&nbsp;</td>
                            <td class="text-end h5"><strong>Total</strong></td>
                            <td class="text-end h5 total"><strong>{{ Cart::total() }} {{ Cart::currency() }}</strong></td>
                        </tr>
                        </tfoot>


                    </table>

                </x-rpd::table>


                @if(auth()->user() && (auth()->user()->addresses->count() || auth()->user()->company->addresses->count()))

                    <div class="text-center">
                        <a class="btn btn-primary my-2" href="#">make order</a>
                    </div>
                @endif

            </x-rpd::card>


        </div>

    </div>



</div>


