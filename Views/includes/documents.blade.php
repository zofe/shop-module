{{-- Download buttons for the documents the bound renderer can produce for $subject (none without a documents module) --}}
@php $docs = app(\App\Modules\Shop\Documents\Documents::class)->available($subject); @endphp
@foreach($docs as $document => $label)
    <a href="{{ route_lang('shop.document', [$subject->getMorphClass(), $subject->getKey(), $document]) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
        <i class="fas fa-file-pdf me-1"></i>{{ $label }}
    </a>
@endforeach
