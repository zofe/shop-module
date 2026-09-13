<tr>
    <td class="text-gray-600">
        {{ $assignment->orderItem->name }}
    </td>
    <td>

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
