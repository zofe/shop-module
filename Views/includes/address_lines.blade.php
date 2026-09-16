{{-- A stored address (orders.shipping_address) as postal lines: street, postcode city (province), country --}}
@php
    $a = $address ?? [];
    $lines = array_filter([
        trim(($a['address'] ?? '') . ' ' . ($a['street_number'] ?? '')),
        trim(($a['zipcode'] ?? '') . ' ' . ($a['city'] ?? '') . (! empty($a['province'] ?? $a['state_code'] ?? null) ? ' (' . ($a['province'] ?? $a['state_code']) . ')' : '')),
        $a['country'] ?? $a['country_code'] ?? '',
    ]);
@endphp
<address class="mb-0">
    @foreach($lines as $line)
        {{ $line }}@if(! $loop->last)<br>@endif
    @endforeach
</address>
