<div>
    <x-rpd::modal
        name="assignItem"
        title="Assign Inventory Item"
        action="doAssign"
        actionLabel="Assign"
    >
        <div>
            <x-rpd::select-list
                inline="true"
                model="selectedInventoryItemId"
                label="Serial Number"
                endpoint="/ajax/available-inventory-item"
                placeholder="Scan or type serial number..."
            />

            @if($errorMessage)
                <div class="alert alert-danger mt-2 py-2 small">
                    {{ $errorMessage }}
                </div>
            @endif
        </div>
    </x-rpd::modal>
</div>
