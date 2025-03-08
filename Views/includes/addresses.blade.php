<div>

    @php
        $addressableType = Auth::user()->company ? 'company' : 'user';
        $addressableId = Auth::user()->company ? Auth::user()->company : Auth::user()->id;
    @endphp

    <div class="d-flex justify-content-between align-items-center">

        <span class="small">Shipping Addresses</span>

        <livewire:addresses::customer-addresses-button-add-embed
            label="add address"
            class="btn-link"
            :addressableType="$addressableType"
            :addressableId="$addressableId"
        />

    </div>

    <livewire:addresses::customer-addresses-table-embed
        :addressableType="$addressableType"
        :addressableId="$addressableId"
        editable="true"
    />



</div>
