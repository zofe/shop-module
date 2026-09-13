<div>
        <x-rpd::modal
            name="editPriceList"
            title="Edit PriceList"
            action="save"
        >
            <div class="row">
                <x-rpd::input col="col-md-6" model="priceList.name" label="Name" />
                <x-rpd::checkbox col="col-md-3 pt-2" model="priceList.is_active" checkLabel="Active" switch="true" />
                <x-rpd::checkbox col="col-md-3 pt-2" model="priceList.is_default" checkLabel="Default" />

            </div>
        </x-rpd::modal>


</div>
