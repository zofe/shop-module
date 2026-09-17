# Documents

Order confirmations, delivery notes, subscription statements, licence certificates: the shop **defines** them and
shows a download button for each one; **producing** them (a PDF, an XLSX…) is the job of a renderer bound to the
`App\Modules\Shop\Documents\Contracts\DocumentRenderer` contract. Nothing is bound by default, so the shop carries no
PDF library and shows no button: a documents module (or your application) registers the renderer and its templates.

## The documents

`config('shop.documents')`, by subject (the morph alias of the model): a label and, optionally, the states of the
subject the document makes sense in.

```php
'documents' => [
    'order' => [
        'order_confirmation' => ['label' => 'Order confirmation', 'states' => ['payment_done', 'in_process', 'shipped', 'completed']],
        'delivery_note'      => ['label' => 'Delivery note',      'states' => ['shipped', 'completed']],
    ],
    'subscription' => [
        'subscription_statement' => ['label' => 'Statement'],
    ],
    'service_item' => [
        'license_certificate' => ['label' => 'Licence certificate', 'states' => ['active']],
    ],
],
```

Add your own entries in the published config: the buttons appear as soon as the renderer supports them.

## The renderer

```php
class PdfRenderer implements App\Modules\Shop\Documents\Contracts\DocumentRenderer
{
    public function supports(string $document, Model $subject): bool
    {
        return view()->exists("documents::{$document}");
    }

    public function render(string $document, Model $subject): Response
    {
        return Pdf::loadView("documents::{$document}", ['subject' => $subject])->download($this->fileName($document, $subject));
    }

    public function fileName(string $document, Model $subject): string
    {
        return "{$document}-{$subject->shortId}.pdf";
    }
}

// a service provider
$this->app->singleton(DocumentRenderer::class, PdfRenderer::class);
```

`App\Modules\Shop\Documents\Documents` (a singleton) is what the pages use: `available($subject)` gives
`[document => label]` for the documents the renderer supports in the subject's current state; `enabled()` says
whether a renderer is bound at all.

## Where they show up

The route `shop.document` (`/shop-documents/{type}/{id}/{document}`) serves a document to the owner of the subject
(the customer, or a user of the customer's company) and to the back office (`view orders`, `view subscriptions`,
`view service items`). The buttons are in:

- the admin order page (Status card) and the customer's order page (Order Detail card);
- the admin and the customer's subscription pages;
- the service item page.

`zofe/documents-module` (premium) brings the PDF renderer (dompdf) with branded templates for these documents,
numbering, Excel exports of orders, subscriptions, stock and payments, and the link with the invoice module.
