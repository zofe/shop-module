<tr>
    <td>
        {{ $assignment->deliverable_type }}
    </td>
    <td>
        @if($editable)
            edita ..
        @else

            {{ $assignment->serial_number }}
        @endif
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
