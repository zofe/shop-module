<?php

namespace App\Modules\Shop\Documents\Contracts;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders the documents of the shop (an order confirmation, a delivery note, a subscription
 * statement…) for a subject: an Order, a Subscription, a ServiceItem. The shop only defines
 * the documents (config shop.documents) and shows a button for each one the renderer
 * supports; a documents module registers a renderer (PDF, Excel…) and its templates.
 * Nothing is registered by default: no dependency on a PDF library in the shop.
 */
interface DocumentRenderer
{
    /** The renderer can produce this document for this subject (the template exists, the state allows it…). */
    public function supports(string $document, Model $subject): bool;

    /** The document as a download / inline response (a PDF, an XLSX…). */
    public function render(string $document, Model $subject): Response;

    /** A file name for the download, e.g. "order-3E7F6C02.pdf". */
    public function fileName(string $document, Model $subject): string;
}
