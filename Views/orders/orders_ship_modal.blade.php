<div>
    <x-rpd::modal
        name="shipOrder"
        title="Ship order"
        action="doShip"
        actionLabel="Ship"
    >
        <div class="row g-2">
            <x-rpd::input col="col-md-5" model="carrier" label="Carrier" placeholder="DHL, UPS, Poste…" />
            <x-rpd::input col="col-md-7" model="trackingCode" label="Tracking code" placeholder="optional" />
        </div>
        @if($errorMessage)
            <div class="alert alert-danger mt-2 py-2 small">{{ $errorMessage }}</div>
        @endif
    </x-rpd::modal>
</div>
