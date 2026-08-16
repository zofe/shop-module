<tr>
    <td>
        {{ $assignment->deliverable_type }}
    </td>
    <td>
        @if($assignment->deliverable)
            <div>{{ $assignment->deliverable->product->name }}</div>
            <div class="small text-monospace text-uppercase text-dark">{{ $assignment->deliverable->id }}</div>


        @endif
{{--        @dump($assignment->deliverable)--}}
{{--        {{ $assignment->deliverable_id }}--}}
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
