<tr>
    <td>
        {{ $assignment->orderItem->name ?? $assignment->deliverable_type }}
    </td>
    <td class="small">
        @if($assignment->serial_number)
            <x-rpd::nav-link icon="barcode" :label="$assignment->serial_number" route="inventory_items.edit" :params="$assignment->deliverable_id" />
        @endif
    </td>
    <td class="small">
        @if($unit = $assignment->deliverable)
            <span class="badge bg-{{ match($unit->status) { 'sold' => 'success', 'assigned' => 'info', default => 'secondary' } }}">{{ $unit->status }}</span>
        @endif
    </td>
    <td wire:key="wfa_{{$assignment->id}}">
        <livewire:workflow::workflow-table-embed
            workfloable-type="order_item_assignment"
            workfloable-id="{{ $assignment->id }}"
            :editable="true"
        />
    </td>
</tr>
