<div>

    <div class="p-2 p-2 card">
        <h5>{{ __('Cart') }}</h5>

        @if(Cart::count())



            <div class="small">
                @foreach(Cart::content() as $item)
                    <div>
                        <small>{{$item->qty}} {{ $item->name }} </small>
                    </div>
                @endforeach
            </div>


            <div class="h4 pt-1">
                <a class="btn btn-sm btn-primary w-50 ms-auto d-block" href="{{ route_lang('shop.cart') }}">{{ __('go to cart') }}</a>
            </div>
        @else
            <p class="text-muted">{{ __('Your cart is empty.') }}</p>
        @endif

    </div>


{{--    <h5>{{ $isHome ? 'Categories' : 'Subcategories' }}</h5>--}}

    @if(count($categories))
        <div class="my-3">

            <ul class="list-group">
                @foreach($categories as $child)
                    <li class="list-group-item">
                        <a class="text-decoration-none d-block" href="{{ route_lang('shop.list', $child->full_path) }}">{{ $child->name }}</a>
                    </li>
                @endforeach
            </ul>


        </div>
    @elseif(optional($category)->parent)
        {{ $category->parent }}

    @else

    @endif


</div>

