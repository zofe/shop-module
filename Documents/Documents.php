<?php

namespace App\Modules\Shop\Documents;

use App\Modules\Shop\Documents\Contracts\DocumentRenderer;
use Illuminate\Database\Eloquent\Model;

/**
 * The documents of the shop and the renderer that produces them.
 *
 * config('shop.documents') lists them by subject type (order, subscription, service_item) with a
 * label and the states they make sense in; the renderer is whatever a module binds to the
 * DocumentRenderer contract (none by default). `available($subject)` is what the pages show.
 */
class Documents
{
    public function __construct(protected ?DocumentRenderer $renderer = null)
    {
    }

    public function renderer(): ?DocumentRenderer
    {
        return $this->renderer;
    }

    public function enabled(): bool
    {
        return $this->renderer !== null;
    }

    /** [document => label] the renderer can produce for this subject now. */
    public function available(Model $subject): array
    {
        if (! $this->renderer) {
            return [];
        }
        $type = $subject->getMorphClass();
        $out = [];
        foreach (config("shop.documents.{$type}", []) as $document => $meta) {
            $states = $meta['states'] ?? null;
            if ($states && ! in_array($subject->status ?? null, $states, true)) {
                continue;
            }
            if ($this->renderer->supports($document, $subject)) {
                $out[$document] = $meta['label'] ?? $document;
            }
        }

        return $out;
    }
}
