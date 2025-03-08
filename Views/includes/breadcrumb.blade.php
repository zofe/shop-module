
    <div class="row">
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item pr-3"><a href="{{ route('shop.list') }}">Home</a></li>
            @if(count($breadcrumbs))
                @foreach($breadcrumbs as $crumb)
                    <li class="breadcrumb-item">
                        @if($crumb == $category && !$price)
                            {{ $crumb->name }}
                        @else
                            <a href="{{ route('shop.list', ['slugs' => $crumb->full_path]) }}">
                                {{ $crumb->name }}
                            </a>
                        @endif

                    </li>
                @endforeach
            @else
                <li class="breadcrumb-item">
                    Shop
                </li>
            @endif

            @if($price)
                <li class="breadcrumb-item">
                    {{ $price->product->name }}
                </li>
            @endif

        </ol>
    </div>


