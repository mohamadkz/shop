I have a clear picture of the codebase now. Let me write up the full analysis.

## 1. Domains identified

From the models/controllers/routes: **Catalog** (Item, Category, Comment, Favorite), **Cart/Basket** (Basket, BasketItem, DiscountCode), **Order** (Order, Followup), **Payment** (Payment — Zarinpal gateway, no callback controller yet), **Customer/Auth** (User, Otp, Sms), and a cross-cutting **Shared** layer (Enums are already domain-owned, so Shared mainly holds infra: Money, ApiResponse, base Action/Job classes).

## 2. Proposed folder tree

```
app/
├── Domain/
│   ├── Catalog/
│   │   ├── Models/{Item.php, Category.php, Comment.php, Favorite.php}
│   │   ├── Actions/{CreateItem.php, UpdateItem.php, DeleteItem.php, AddComment.php, ToggleFavorite.php}
│   │   ├── Services/CatalogSearchService.php
│   │   ├── DTOs/ItemData.php
│   │   ├── Events/ItemStockDepleted.php
│   │   ├── Listeners/NotifyBackInStockSubscribers.php
│   │   ├── Requests/{StoreItemRequest.php, UpdateItemRequest.php}
│   │   ├── Resources/{ItemResource.php, CategoryResource.php}
│   │   └── Enums/ItemStatus.php
│   │
│   ├── Cart/
│   │   ├── Models/{Basket.php, BasketItem.php, DiscountCode.php}
│   │   ├── Actions/
│   │   │   ├── AddItemToCart.php
│   │   │   ├── UpdateCartItemQuantity.php
│   │   │   ├── RemoveItemFromCart.php
│   │   │   ├── ApplyDiscountCode.php
│   │   │   ├── RemoveDiscountCode.php
│   │   │   └── ReserveCartForCheckout.php
│   │   ├── Services/{CartRedisRepository.php, CartPricingCalculator.php}
│   │   ├── DTOs/{CartLineData.php, CartSnapshotData.php}
│   │   ├── Events/CartCheckedOut.php
│   │   ├── Requests/{StoreBasketRequest.php, UpdateBasketRequest.php, ApplyDiscountRequest.php, CheckoutRequest.php}
│   │   ├── Resources/CartResource.php
│   │   └── Enums/DiscountType.php
│   │
│   ├── Order/
│   │   ├── Models/{Order.php, Followup.php}
│   │   ├── Actions/
│   │   │   ├── PlaceOrder.php
│   │   │   ├── MarkOrderPaid.php
│   │   │   ├── MarkOrderFailed.php
│   │   │   └── CancelOrder.php
│   │   ├── Services/OrderNumberGenerator.php
│   │   ├── DTOs/PlaceOrderData.php
│   │   ├── Events/{OrderPlaced.php, OrderPaid.php, OrderCancelled.php}
│   │   ├── Listeners/{DecrementStockOnOrderPlaced.php, RestockOnOrderCancelled.php, SendOrderConfirmation.php}
│   │   ├── Jobs/SendOrderConfirmationEmail.php
│   │   ├── Requests/CheckoutRequest.php
│   │   ├── Resources/{OrderResource.php, FollowupResource.php}
│   │   └── Enums/OrderStatus.php
│   │
│   ├── Payment/
│   │   ├── Models/Payment.php
│   │   ├── Actions/
│   │   │   ├── InitiatePayment.php
│   │   │   ├── HandlePaymentCallback.php
│   │   │   └── VerifyPaymentWithGateway.php
│   │   ├── Services/
│   │   │   ├── Gateways/ZarinpalGateway.php
│   │   │   └── Contracts/PaymentGatewayContract.php
│   │   ├── DTOs/{GatewayRequestData.php, GatewayVerifyResultData.php}
│   │   ├── Events/{PaymentSucceeded.php, PaymentFailed.php}
│   │   ├── Listeners/{MarkOrderPaidOnPaymentSucceeded.php, MarkOrderFailedOnPaymentFailed.php}
│   │   ├── Requests/PaymentCallbackRequest.php
│   │   ├── Resources/PaymentResource.php
│   │   └── Enums/PaymentStatus.php
│   │
│   └── Customer/
│       ├── Models/{User.php, Otp.php}
│       ├── Actions/{RegisterUser.php, SendOtp.php, VerifyOtp.php, LoginUser.php}
│       ├── Services/{OtpService.php, SmsService.php, Sms/Providers/*}
│       ├── DTOs/OtpRequestData.php
│       ├── Events/OtpVerified.php
│       ├── Requests/{RegisterRequest.php, SendOtpRequest.php, VerifyOtpRequest.php}
│       ├── Resources/UserResource.php
│       └── Enums/OtpChannel.php
│
├── Shared/
│   ├── Actions/Contracts/ActionInterface.php
│   ├── DTOs/Contracts/BaseData.php
│   ├── Services/{Money.php, DistributedLock.php}
│   ├── Jobs/Middleware/WithoutOverlapping.php
│   ├── Http/{ApiResponse.php, Resources/PaginatedResource.php}
│   ├── Exceptions/{InsufficientStockException.php, DomainException.php}
│   └── Traits/HasUuid.php
│
├── Http/
│   └── Controllers/
│       ├── Api/V1/{ItemController.php, BasketController.php, AuthController.php, OrderController.php}
│       ├── Api/V2/OtpController.php
│       ├── Payment/PaymentCallbackController.php
│       └── Auth/*  (Breeze, unchanged)
│
├── Observers/         (unchanged, or move per-model observer into its Domain/*/Observers)
└── Providers/
```

Now the key code files for the critical flows: **AddToCart**, **PlaceOrder (Checkout)**, **PaymentCallback**.## Domains

- **Catalog** — Item, Category, Comment, Favorite
- **Cart** — Basket (Redis-backed active cart), BasketItem (persisted snapshot), DiscountCode
- **Order** — Order, Followup
- **Payment** — Payment, gateway integration (Zarinpal — currently only stubbed in `web.php`)
- **Customer** — User, Otp, SMS delivery
- **Shared** — cross-domain infra (locks, money, API responses, base contracts)

## Folder tree

```
app/
├── Domain/
│   ├── Catalog/
│   │   ├── Models/{Item.php, Category.php, Comment.php, Favorite.php}
│   │   ├── Actions/{CreateItem.php, UpdateItem.php, DeleteItem.php, AddComment.php, ToggleFavorite.php}
│   │   ├── Services/CatalogSearchService.php
│   │   ├── DTOs/ItemData.php
│   │   ├── Events/ItemStockDepleted.php
│   │   ├── Listeners/NotifyBackInStockSubscribers.php
│   │   ├── Requests/{StoreItemRequest.php, UpdateItemRequest.php}
│   │   ├── Resources/{ItemResource.php, CategoryResource.php}
│   │   └── Enums/ItemStatus.php
│   │
│   ├── Cart/
│   │   ├── Models/{Basket.php, BasketItem.php, DiscountCode.php}
│   │   ├── Actions/{AddItemToCart.php, UpdateCartItemQuantity.php, RemoveItemFromCart.php,
│   │   │            ApplyDiscountCode.php, RemoveDiscountCode.php}
│   │   ├── Services/{CartRedisRepository.php, CartPricingCalculator.php}
│   │   ├── DTOs/{CartLineData.php, CartSnapshotData.php}
│   │   ├── Requests/{StoreBasketRequest.php, UpdateBasketRequest.php, ApplyDiscountRequest.php}
│   │   ├── Resources/CartResource.php
│   │   └── Enums/DiscountType.php
│   │
│   ├── Order/
│   │   ├── Models/{Order.php, Followup.php}
│   │   ├── Actions/{PlaceOrder.php, CancelOrder.php}
│   │   ├── Services/OrderNumberGenerator.php
│   │   ├── DTOs/PlaceOrderData.php
│   │   ├── Events/{OrderPlaced.php, OrderPaid.php, OrderCancelled.php}
│   │   ├── Listeners/{SendOrderConfirmation.php}
│   │   ├── Jobs/SendOrderConfirmationEmail.php
│   │   ├── Requests/CheckoutRequest.php
│   │   ├── Resources/{OrderResource.php, FollowupResource.php}
│   │   └── Enums/OrderStatus.php
│   │
│   ├── Payment/
│   │   ├── Models/Payment.php
│   │   ├── Actions/{InitiatePayment.php, HandlePaymentCallback.php}
│   │   ├── Services/{Gateways/ZarinpalGateway.php, Contracts/PaymentGatewayContract.php}
│   │   ├── DTOs/{GatewayVerifyResultData.php}
│   │   ├── Events/{PaymentSucceeded.php, PaymentFailed.php}
│   │   ├── Listeners/{MarkOrderPaidOnPaymentSucceeded.php, MarkOrderFailedOnPaymentFailed.php,
│   │   │              RestockOnPaymentFailed.php}
│   │   ├── Requests/PaymentCallbackRequest.php
│   │   ├── Resources/PaymentResource.php
│   │   └── Enums/PaymentStatus.php
│   │
│   └── Customer/
│       ├── Models/{User.php, Otp.php}
│       ├── Actions/{RegisterUser.php, SendOtp.php, VerifyOtp.php}
│       ├── Services/{OtpService.php, SmsService.php, Sms/Providers/*}
│       ├── Requests/{RegisterRequest.php, SendOtpRequest.php, VerifyOtpRequest.php}
│       ├── Resources/UserResource.php
│       └── Enums/OtpChannel.php
│
├── Shared/
│   ├── Services/{DistributedLock.php, Money.php}
│   ├── Http/ApiResponse.php
│   ├── Exceptions/{InsufficientStockException.php, DomainException.php}
│   └── Traits/HasUuid.php
│
├── Http/Controllers/
│   ├── Api/V1/{ItemController.php, BasketController.php, OrderController.php, AuthController.php}
│   ├── Api/V2/OtpController.php
│   ├── Payment/PaymentCallbackController.php
│   └── Auth/*  (unchanged, Breeze)
│
└── Providers/DomainEventServiceProvider.php
```

---

## Key code — Cart: `AddItemToCart`

```php
// app/Domain/Cart/Services/CartRedisRepository.php
<?php

namespace App\Domain\Cart\Services;

use App\Domain\Cart\Models\DiscountCode;
use App\Domain\Customer\Models\User;
use Illuminate\Support\Facades\Cache;

class CartRedisRepository
{
    private const TTL_DAYS = 7;

    public function itemsKey(int $userId): string
    {
        return "basket:{$userId}:items";
    }

    public function discountKey(int $userId): string
    {
        return "basket:{$userId}:discount_code_id";
    }

    public function lockKey(int $userId): string
    {
        return "basket-lock:{$userId}";
    }

    public function getItems(User $user): array
    {
        return Cache::get($this->itemsKey($user->id), []);
    }

    public function putItems(User $user, array $items): void
    {
        if (empty($items)) {
            Cache::forget($this->itemsKey($user->id));
            return;
        }

        Cache::put($this->itemsKey($user->id), $items, now()->addDays(self::TTL_DAYS));
    }

    public function getDiscount(User $user): ?DiscountCode
    {
        $id = Cache::get($this->discountKey($user->id));

        return $id ? DiscountCode::find($id) : null;
    }

    public function clear(User $user): void
    {
        Cache::forget($this->itemsKey($user->id));
        Cache::forget($this->discountKey($user->id));
    }
}
```

```php
// app/Domain/Cart/Actions/AddItemToCart.php
<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Catalog\Models\Item;
use App\Domain\Cart\Services\CartRedisRepository;
use App\Domain\Customer\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AddItemToCart
{
    private const LOCK_SECONDS = 5;

    public function __construct(private readonly CartRedisRepository $cart)
    {
    }

    public function execute(User $user, Item $item, int $quantity = 1): array
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'مقدار باید حداقل ۱ باشد']);
        }

        if ($item->stock < $quantity) {
            throw ValidationException::withMessages(['quantity' => 'موجودی کالا کافی نیست']);
        }

        return Cache::lock($this->cart->lockKey($user->id), self::LOCK_SECONDS)
            ->block(3, function () use ($user, $item, $quantity) {
                $items = $this->cart->getItems($user);

                $newQty = ($items[$item->id]['quantity'] ?? 0) + $quantity;

                if ($item->stock < $newQty) {
                    throw ValidationException::withMessages(['quantity' => 'موجودی کالا کافی نیست']);
                }

                $items[$item->id] = [
                    'item_id'  => $item->id,
                    'quantity' => $newQty,
                    'price'    => $item->price,
                ];

                $this->cart->putItems($user, $items);

                return $items;
            });
    }
}
```

```php
// app/Http/Controllers/Api/V1/BasketController.php  (relevant slice)
<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Cart\Actions\AddItemToCart;
use App\Domain\Cart\Requests\StoreBasketRequest;
use App\Domain\Catalog\Models\Item;
use App\Http\Controllers\Controller;

class BasketController extends Controller
{
    public function store(StoreBasketRequest $request, AddItemToCart $action)
    {
        $data = $request->validated();
        $item = Item::findOrFail($data['item_id']);

        return response()->json(
            $action->execute($request->user(), $item, $data['quantity'] ?? 1)
        );
    }
}
```

---

## Key code — Order: `PlaceOrder` (checkout)

```php
// app/Domain/Order/DTOs/PlaceOrderData.php
<?php

namespace App\Domain\Order\DTOs;

class PlaceOrderData
{
    public function __construct(
        public readonly string $address,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(address: $data['address']);
    }
}
```

```php
// app/Domain/Order/Actions/PlaceOrder.php
<?php

namespace App\Domain\Order\Actions;

use App\Domain\Cart\Models\Basket;
use App\Domain\Cart\Models\BasketItem;
use App\Domain\Cart\Services\CartRedisRepository;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use App\Domain\Order\DTOs\PlaceOrderData;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    public function __construct(private readonly CartRedisRepository $cart)
    {
    }

    public function execute(User $user, PlaceOrderData $data): Order
    {
        $items = $this->cart->getItems($user);

        if (empty($items)) {
            throw ValidationException::withMessages(['basket' => 'سبد خرید شما خالی است']);
        }

        $order = DB::transaction(function () use ($user, $items, $data) {
            // Lock rows to prevent oversell under concurrent checkouts
            $dbItems = Item::whereIn('id', array_column($items, 'item_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $line) {
                $dbItem = $dbItems->get($line['item_id']);

                if (!$dbItem || $dbItem->stock < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => "موجودی محصول ناکافی برای \"{$dbItem?->name}\".",
                    ]);
                }
            }

            $discount = $this->cart->getDiscount($user);
            $amount = collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']);
            $discountAmount = $this->calculateDiscount($amount, $discount);
            $total = max(0, $amount - $discountAmount);

            $basket = Basket::create([
                'user_id'          => $user->id,
                'discount_code_id' => $discount?->id,
                'status'           => true,
                'total_amount'     => $amount,
                'discount_amount'  => $discountAmount,
                'amount'           => $total,
            ]);

            $now = now();
            BasketItem::insert(collect($items)->map(fn ($line) => [
                'basket_id'  => $basket->id,
                'item_id'    => $line['item_id'],
                'quantity'   => $line['quantity'],
                'price'      => $line['price'],
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());

            foreach ($items as $line) {
                $dbItems->get($line['item_id'])->decrement('stock', $line['quantity']);
            }

            $order = Order::create([
                'user_id'     => $user->id,
                'basket_id'   => $basket->id,
                'total_price' => $total,
                'address'     => $data->address,
                'status'      => OrderStatus::Pending,
            ]);

            Payment::create([
                'order_id'       => $order->id,
                'user_id'        => $user->id,
                'amount'         => $order->total_price,
                'payment_method' => 'zarinpal',
                'status'         => PaymentStatus::Pending,
            ]);

            return $order->load('payment');
        });

        $this->cart->clear($user);

        // Side effects (confirmation email, analytics) run outside the DB transaction
        OrderPlaced::dispatch($order);

        return $order;
    }

    private function calculateDiscount(float $amount, $discount): float
    {
        if (!$discount) {
            return 0;
        }

        $discountAmount = ($amount * $discount->percent) / 100;

        return $discount->max_discount
            ? min($discountAmount, $discount->max_discount)
            : $discountAmount;
    }
}
```

```php
// app/Domain/Order/Events/OrderPlaced.php
<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
    }
}
```

```php
// app/Domain/Order/Listeners/SendOrderConfirmation.php
<?php

namespace App\Domain\Order\Listeners;

use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Jobs\SendOrderConfirmationEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        SendOrderConfirmationEmail::dispatch($event->order);
    }
}
```

```php
// app/Http/Controllers/Api/V1/BasketController.php  (checkout slice)
public function checkout(CheckoutRequest $request, PlaceOrder $action)
{
    $order = $action->execute(
        $request->user(),
        PlaceOrderData::fromArray($request->validated())
    );

    return response()->json([
        'message' => 'سفارش با موفقیت ایجاد شد',
        'order'   => $order,
    ], 201);
}
```

---

## Key code — Payment: `InitiatePayment` + `HandlePaymentCallback`

```php
// app/Domain/Payment/Services/Contracts/PaymentGatewayContract.php
<?php

namespace App\Domain\Payment\Services\Contracts;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\DTOs\GatewayVerifyResultData;

interface PaymentGatewayContract
{
    public function requestPayment(Order $order, string $callbackUrl): string; // returns authority/token

    public function verify(string $authority, float $amount): GatewayVerifyResultData;

    public function redirectUrl(string $authority): string;
}
```

```php
// app/Domain/Payment/DTOs/GatewayVerifyResultData.php
<?php

namespace App\Domain\Payment\DTOs;

class GatewayVerifyResultData
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $refId = null,
        public readonly ?string $cardPan = null,
        public readonly ?string $message = null,
    ) {
    }
}
```

```php
// app/Domain/Payment/Actions/InitiatePayment.php
<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use Illuminate\Validation\ValidationException;

class InitiatePayment
{
    public function __construct(private readonly PaymentGatewayContract $gateway)
    {
    }

    public function execute(Order $order): string
    {
        $payment = $order->payment()->latest()->firstOrFail();

        if (!$payment->status->isPending()) {
            throw ValidationException::withMessages(['order' => 'این سفارش قبلاً پرداخت شده است']);
        }

        $authority = $this->gateway->requestPayment(
            $order,
            route('payment.callback', ['order' => $order->id])
        );

        $payment->update(['authority' => $authority]);

        return $this->gateway->redirectUrl($authority);
    }
}
```

```php
// app/Domain/Payment/Actions/HandlePaymentCallback.php
<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Events\PaymentFailed;
use App\Domain\Payment\Events\PaymentSucceeded;
use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HandlePaymentCallback
{
    public function __construct(private readonly PaymentGatewayContract $gateway)
    {
    }

    public function execute(Order $order, string $authority, bool $gatewayOk): Order
    {
        // Distributed lock: guards against duplicate/replayed gateway callbacks
        return Cache::lock("payment-callback:{$order->id}", 10)->block(5, function () use ($order, $authority, $gatewayOk) {
            return DB::transaction(function () use ($order, $authority, $gatewayOk) {
                $payment = $order->payment()
                    ->where('authority', $authority)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Idempotency: already processed, don't double-charge state
                if (!$payment->status->isPending()) {
                    return $order->fresh('payment');
                }

                if (!$gatewayOk) {
                    $payment->update(['status' => PaymentStatus::Failed]);
                    $order->update(['status' => OrderStatus::Failed]);

                    PaymentFailed::dispatch($payment);

                    return $order->fresh('payment');
                }

                $result = $this->gateway->verify($authority, (float) $order->total_price);

                if (!$result->success) {
                    $payment->update(['status' => PaymentStatus::Failed]);
                    $order->update(['status' => OrderStatus::Failed]);

                    PaymentFailed::dispatch($payment);

                    return $order->fresh('payment');
                }

                $payment->update([
                    'status'   => PaymentStatus::Success,
                    'ref_id'   => $result->refId,
                    'card_pan' => $result->cardPan,
                    'paid_at'  => now(),
                ]);

                $order->update(['status' => OrderStatus::Paid]);

                PaymentSucceeded::dispatch($payment);

                return $order->fresh('payment');
            });
        });
    }
}
```

```php
// app/Domain/Payment/Listeners/RestockOnPaymentFailed.php
<?php

namespace App\Domain\Payment\Listeners;

use App\Domain\Catalog\Models\Item;
use App\Domain\Payment\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class RestockOnPaymentFailed implements ShouldQueue
{
    public function handle(PaymentFailed $event): void
    {
        $order = $event->payment->order()->with('basket.basketItems')->first();

        DB::transaction(function () use ($order) {
            foreach ($order->basket->basketItems as $line) {
                Item::whereKey($line->item_id)->lockForUpdate()->increment('stock', $line->quantity);
            }
        });
    }
}
```

```php
// app/Http/Controllers/Payment/PaymentCallbackController.php
<?php

namespace App\Http\Controllers\Payment;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Actions\HandlePaymentCallback;
use App\Domain\Payment\Requests\PaymentCallbackRequest;
use App\Http\Controllers\Controller;

class PaymentCallbackController extends Controller
{
    public function __invoke(PaymentCallbackRequest $request, Order $order, HandlePaymentCallback $action)
    {
        $data = $request->validated();

        $order = $action->execute(
            $order,
            authority: $data['Authority'],
            gatewayOk: $data['Status'] === 'OK'
        );

        return $order->status->isPaid()
            ? redirect()->route('orders.show', $order)->with('success', 'پرداخت با موفقیت انجام شد')
            : redirect()->route('orders.show', $order)->with('error', 'پرداخت ناموفق بود');
    }
}
```

```php
// routes/web.php  (add)
Route::get('/payment/callback/{order}', PaymentCallbackController::class)
    ->name('payment.callback')
    ->middleware('signed'); // or verify via authority match instead of signed URLs
```

**Wiring** (`app/Providers/DomainEventServiceProvider.php`):

```php
protected $listen = [
    OrderPlaced::class => [SendOrderConfirmation::class],
    PaymentSucceeded::class => [MarkOrderPaidOnPaymentSucceeded::class], // if not done inline in Action
    PaymentFailed::class => [RestockOnPaymentFailed::class],
];
```

**Migration notes** (incremental, no big-bang rewrite):
1. Move models/enums into `Domain/*` namespaces one at a time; add a temporary `class_alias` for old `App\Models\*` paths if other code still references them, then delete once all references are updated.
2. Extract `BasketService` into `CartRedisRepository` + `AddItemToCart`/`UpdateCartItemQuantity`/etc. Actions — keep the controller thin, one Action per method.
3. Add the `PaymentCallbackController` + `ZarinpalGateway` — this closes the gap left by the commented-out code in `web.php`.
4. Run `composer dump-autoload -o` after each namespace move; update `use` statements domain-by-domain rather than all at once.