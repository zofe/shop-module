<?php

namespace App\Modules\Shop\Tests\Feature;

use App\Modules\Shop\Documents\Contracts\DocumentRenderer;
use App\Modules\Shop\Documents\Documents;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\PriceListItem;
use App\Modules\Shop\Services\OrderService;
use App\Modules\Shop\Tests\Models\User;
use App\Modules\Shop\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;
use Zofe\Rapyd\Modules\Auth\Database\Seeders\AuthSeeder;

/** The shop defines its documents; a renderer (a documents module) produces them. */
class DocumentsTest extends TestCase
{
    use DatabaseMigrations;

    protected function customer(string $name, string $email): User
    {
        $user = User::create(['name' => $name, 'email' => $email, 'password' => 'x']);
        $user->addresses()->create(['address' => 'Via Roma 1', 'city' => 'Bari', 'zipcode' => '70100', 'country_code' => 'IT']);
        $user->assignRole('customer');

        return $user;
    }

    protected function paidOrder(User $user): Order
    {
        app('cart')->add(PriceListItem::find(3), [], 1);
        $order = OrderService::createOrderFromCart(null, $user->id, null, $user->addresses()->first()->id);
        foreach (['pay_order', 'check_payment', 'payment_done'] as $t) {
            $order->workflow_apply($t, 'order');
            $order->save();
        }

        return $order->fresh();
    }

    public function test_without_a_renderer_there_are_no_documents()
    {
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        $this->seed(AuthSeeder::class);
        $user = $this->customer('Ann', 'ann@example.com');
        $order = $this->paidOrder($user);

        $this->assertFalse(app(Documents::class)->enabled());
        $this->assertSame([], app(Documents::class)->available($order));
        $this->actingAs($user)->get(route('shop.document', ['order', $order->id, 'order_confirmation']))->assertNotFound();
        $this->actingAs($user)->get(route('shop.order', $order))->assertOk()->assertDontSee('Order confirmation');
    }

    public function test_a_bound_renderer_adds_the_documents_it_supports_for_the_owner_and_the_back_office()
    {
        $this->seed(\App\Modules\Shop\Database\Seeders\ShopSeeder::class);
        $this->seed(AuthSeeder::class);
        $this->app->singleton(DocumentRenderer::class, fn () => new class implements DocumentRenderer {
            public function supports(string $document, Model $subject): bool { return $document !== 'delivery_note'; }
            public function render(string $document, Model $subject): Response { return response("PDF of {$document} for {$subject->getKey()}", 200, ['Content-Type' => 'application/pdf']); }
            public function fileName(string $document, Model $subject): string { return "{$document}.pdf"; }
        });
        $this->app->forgetInstance(Documents::class);

        $user = $this->customer('Ann', 'ann@example.com');
        $order = $this->paidOrder($user);

        // the confirmation is available in payment_done, the delivery note is not supported by this renderer
        $this->assertSame(['order_confirmation' => 'Order confirmation'], app(Documents::class)->available($order));

        $this->actingAs($user)->get(route('shop.order', $order))->assertOk()->assertSee('Order confirmation');
        $this->actingAs($user)->get(route('shop.document', ['order', $order->id, 'order_confirmation']))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertSee('PDF of order_confirmation');
        $this->actingAs($user)->get(route('shop.document', ['order', $order->id, 'delivery_note']))->assertNotFound();

        // somebody else: no
        $other = $this->customer('Bob', 'bob@example.com');
        $this->actingAs($other)->get(route('shop.document', ['order', $order->id, 'order_confirmation']))->assertForbidden();
        $this->actingAs($other)->get(route('shop.order', $order))->assertForbidden();

        // the back office: yes
        $admin = User::create(['name' => 'Root', 'email' => 'root@example.com', 'password' => 'x']);
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('shop.document', ['order', $order->id, 'order_confirmation']))->assertOk();
        $this->actingAs($admin)->get(route('orders.view', $order))->assertOk()->assertSee('Order confirmation');
    }
}
