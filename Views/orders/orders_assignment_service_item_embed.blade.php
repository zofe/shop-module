<tr>
    <td>
        {{ $assignment->orderItem->name ?? $assignment->deliverable_type }}
    </td>
    <td class="small">
        @if($service = $assignment->deliverable)
            <x-rpd::nav-link icon="concierge-bell" :label="'Service ' . $service->shortId" route="service_items.edit" :params="$service->id" />
            @if($service->license)
                <div class="text-muted">licence {{ $service->license->shortId }} · until {{ optional($service->license->expire_date)->format('Y-m-d') ?? 'no expiry' }}</div>
            @endif
        @endif
    </td>
    <td class="small">
        @if($service = $assignment->deliverable)
            <span class="badge bg-{{ match($service->status) { 'active' => 'success', 'suspended' => 'warning text-dark', 'terminated' => 'danger', default => 'secondary' } }}">{{ $service->status }}</span>
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
