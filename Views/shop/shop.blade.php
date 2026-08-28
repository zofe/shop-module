<div>


    <div class="row g-4">

        <div class="col-md-4">

            @include('shop::includes.sidebar')

        </div>

        <div class="col-md-8">

            <div wire:ignore>
                <x-rpd::breadcrumbs class="breadcrumb-item" active="active" />
            </div>

        @if($price)

                @include('shop::shop.product_detail')

            @elseif($category)

                <div class="row g-3">
                    @foreach($category->priceListItems as $price)

                        @include('shop::shop.product_item', ['price' => $price])

                    @endforeach

                    <x-rpd::pagination :items="$category" count="" limits="" />
                </div>

            @else

                <div class="row">
                    @foreach($homeItems as $price)
                        @include('shop::shop.product_item', ['price' => $price])
                    @endforeach

                     <x-rpd::pagination :items="$homeItems" count="" limits="" />
                </div>

            @endif

        </div>

    </div>







</div>

