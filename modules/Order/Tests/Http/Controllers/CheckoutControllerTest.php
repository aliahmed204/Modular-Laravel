<?php

namespace Modules\Order\Http\Controllers;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderFullfilled;
use Modules\Order\Mail\OrderReceived;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderLine;
use Modules\Order\Tests\OrderTestCase;
use Modules\Payment\PayBuddy;
use Modules\Payment\Payment;
use Modules\Product\Database\Factories\ProductFactory;
use PHPUnit\Framework\Attributes\Test;

class CheckoutControllerTest extends OrderTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function it_successfuly_creates_an_order(): void
    {
        Mail::fake();
        // Event::fake();
        $user = UserFactory::new()->createOne();
        $products = ProductFactory::new()->count(3)->create(
            new Sequence(
                ['name' => 'Very expensive air fryer', 'price_in_cents' => 10000, 'stock' => 10],
                ['name' => 'Macbook Pro M3', 'price_in_cents' => 50000, 'stock' => 10],
                ['name' => 'iPhone 15 Pro Max', 'price_in_cents' => 20000, 'stock' => 10],
            )
        );

        $paymentToken = PayBuddy::validToken();

        $res = $this->actingAs($user)
            ->post(route('order::checkout'), [
                'payment_token' => $paymentToken,
                'products' => $products->map(fn ($product) => [
                    'id' => $product->id,
                    'quantity' => 1,
                ])->toArray(),
            ]);

        /** @var Order $order */
        $order = Order::latest('id')->first();

        $res->assertJson([
            'message' => 'Order created successfully.',
            'order_url' => $order->url(),
        ])
            ->assertStatus(201);

        // Mail
        Mail::assertSent(function (OrderReceived $mail) use ($order) {
            return $mail->hasTo($order->user->email) &&
                $mail->localizedOrderTotal === $order->localizedTotal();
        });

        // Event::assertDispatched(OrderFullfilled::class);

        // Order
        $this->assertTrue($order->user->is($user));
        $this->assertEquals(80000, $order->total_in_cents);
        $this->assertEquals('completed', $order->status);

        /** @var Payment $payment */
        $payment = $order->load('lastPayment')->lastPayment;
        // Payment
        $this->assertTrue($payment->user->is($user));
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals('PayBuddy', $payment->payment_gateway);
        $this->assertEquals(36, strlen($payment->payment_id));
        $this->assertEquals(80000, $payment->total_in_cents);

        // Order Lines
        $this->assertCount(3, $order->lines);

        foreach ($order->lines as $line) {
            /** @var OrderLine $orderLine */
            $orderLine = $order->lines->where('product_id', $line->product_id)->first();
            $orderLine->loadMissing('product');

            $this->assertEquals(1, $orderLine->quantity);
            $this->assertEquals($orderLine->product->price_in_cents, $orderLine->product_price_in_cents);
        }

        $products = $products->fresh();

        $this->assertEquals(9, $products->first()->stock);
        $this->assertEquals(9, $products->last()->stock);
    }

    #[Test]
    public function it_fails_with_invalid_payment_token(): void
    {
        $user = UserFactory::new()->createOne();
        $products = ProductFactory::new()->count(3)->create();

        $paymentToken = PayBuddy::invalidToken();

        $res = $this->actingAs($user)
            ->postJson(route('order::checkout'), [
                'payment_token' => $paymentToken,
                'products' => $products->map(fn ($product) => [
                    'id' => $product->id,
                    'quantity' => 1,
                ])->toArray(),
            ]);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['payment_token']);

        $this->assertEquals(0, Order::query()->count());
    }
}
