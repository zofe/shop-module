<div>


    @php
        $addressableType = Auth::user()->company ? 'company' : 'user';
        $addressableId = Auth::user()->company ? Auth::user()->company : Auth::user()->id;
    @endphp


    <span class="small">Shipping Addresses</span>


    <livewire:addresses::customer-addresses-table-embed
        :addressableType="$addressableType"
        :addressableId="$addressableId"
        editable="true"
    />



</div>
