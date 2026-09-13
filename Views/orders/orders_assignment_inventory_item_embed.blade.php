<tr>
    <td>
        {{ $assignment->orderItem->name ?? $assignment->deliverable_type }}
    </td>
    <td>
        {{ $assignment->serial_number }}
    </td>
    <td>{{ $assignment->metadata }}</td>
    <td wire:key="wfa_{{$assignment->id}}">
        <livewire:workflow::workflow-table-embed
            workfloable-type="order_item_assignment"
            workfloable-id="{{ $assignment->id }}"
            :editable="true"
        />
    </td>
</tr>
