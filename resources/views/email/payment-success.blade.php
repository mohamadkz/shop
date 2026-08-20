<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <title>پرداخت موفق</title>
</head>

<body>

    <h2>
        سلام {{ $payment->user->name }}
    </h2>

    <p>
        پرداخت شما با موفقیت انجام شد.
    </p>

    <hr>

    <p>
        <strong>شماره سفارش:</strong>
        {{ $payment->order->id }}
    </p>

    <p>
        <strong>مبلغ پرداخت:</strong>
        {{ number_format((float) $payment->amount) }}
        تومان
    </p>

    <p>
        <strong>شماره پیگیری:</strong>
        {{ $payment->ref_id ?? '---' }}
    </p>

    <p>
        <strong>تاریخ پرداخت:</strong>
        {{ $payment->paid_at?->format('Y-m-d H:i') }}
    </p>

    <hr>

    <p>
        از خرید شما متشکریم
    </p>

</body>

</html>