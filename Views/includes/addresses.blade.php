<div>
    @php
        // Addresses belong to the customer's company when they have one, to the user otherwise.
        $addressable = Auth::user()->company ?: Auth::user();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="small">Shipping Addresses</span>
        <livewire:addresses::addresses-button-add-embed
            :addressableType="$addressable->getMorphClass()"
            :addressableId="$addressable->id"
        />
    </div>

    <livewire:addresses::addresses-table-embed
        :addressableType="$addressable->getMorphClass()"
        :addressableId="$addressable->id"
        :editable="true"
    />
</div>
