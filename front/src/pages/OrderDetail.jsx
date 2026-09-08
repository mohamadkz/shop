import { useEffect, useState } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { orderApi, unwrap } from '../api/client'
import { formatDate, formatPrice } from '../utils/format'
import { DetailSkeleton } from '../components/ui/Skeleton'
import Button from '../components/ui/Button'
import { useToast } from '../context/ToastContext'

export default function OrderDetail() {
  const { id } = useParams()
  const [params] = useSearchParams()
  const toast = useToast()
  const [order, setOrder] = useState(null)
  const [loading, setLoading] = useState(true)
  const [busy, setBusy] = useState(false)
  const paymentStatus = params.get('payment')

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    orderApi
      .show(id)
      .then((payload) => {
        if (!cancelled) setOrder(unwrap(payload) ?? payload?.data)
      })
      .catch((err) => {
        if (!cancelled) {
          setOrder(null)
          toast.error(err.message)
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })
    return () => {
      cancelled = true
    }
  }, [id])

  useEffect(() => {
    if (paymentStatus === 'success') toast.success('پرداخت با موفقیت انجام شد')
    if (paymentStatus === 'failed') toast.error('پرداخت ناموفق بود')
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [paymentStatus])

  async function pay() {
    setBusy(true)
    try {
      const payload = await orderApi.initiatePayment(id)
      const data = unwrap(payload) ?? payload?.data ?? payload
      if (data?.redirect_url) window.location.href = data.redirect_url
      else toast.info('آدرس درگاه دریافت نشد')
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (loading) return <DetailSkeleton />
  if (!order) {
    return (
      <div className="rounded-2xl bg-white p-8 text-center text-slate-500">
        سفارش پیدا نشد.
        {paymentStatus && <p className="mt-2">وضعیت بازگشت از درگاه: {paymentStatus}</p>}
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">سفارش {order.order_number}</h1>
          <p className="text-sm text-slate-500">{formatDate(order.created_at)}</p>
        </div>
        <span className="rounded-full bg-indigo-50 px-3 py-1 text-sm text-indigo-700">{order.status_label || order.status}</span>
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-5">
        <p className="text-sm text-slate-500">آدرس</p>
        <p className="mt-1">{order.address}</p>
      </div>

      <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        {(order.items || []).map((line, idx) => (
          <div key={idx} className="flex items-center justify-between border-b px-4 py-3 last:border-0">
            <div>
              <p className="font-medium">{line.item_name}</p>
              <p className="text-xs text-slate-500">{line.quantity} × {formatPrice(line.unit_price)}</p>
            </div>
            <p className="text-sm">{formatPrice(line.line_total)}</p>
          </div>
        ))}
      </div>

      <div className="rounded-2xl border border-slate-200 bg-white p-5 text-sm">
        <div className="flex justify-between"><span>جمع جزء</span><span>{formatPrice(order.subtotal)}</span></div>
        <div className="mt-2 flex justify-between"><span>تخفیف {order.discount_code ? `(${order.discount_code})` : ''}</span><span>{formatPrice(order.discount_amount)}</span></div>
        <div className="mt-2 flex justify-between"><span>مالیات</span><span>{formatPrice(order.tax)}</span></div>
        <div className="mt-3 flex justify-between border-t pt-3 font-bold"><span>کل</span><span>{formatPrice(order.total_price)}</span></div>
      </div>

      <Button onClick={pay} disabled={busy}>پرداخت سفارش</Button>
    </div>
  )
}
