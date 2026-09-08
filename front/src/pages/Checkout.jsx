import { useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import Textarea from '../components/ui/Textarea'
import Button from '../components/ui/Button'
import { useCart } from '../context/CartContext'
import { useToast } from '../context/ToastContext'
import { orderApi, unwrap } from '../api/client'
import { createIdempotencyKey, formatPrice } from '../utils/format'

export default function Checkout() {
  const { cart, refresh } = useCart()
  const toast = useToast()
  const navigate = useNavigate()
  const [address, setAddress] = useState('')
  const [busy, setBusy] = useState(false)
  const idempotencyKey = useMemo(() => createIdempotencyKey(), [])

  async function submit(e) {
    e.preventDefault()
    setBusy(true)
    try {
      const payload = await orderApi.checkout({ address, idempotency_key: idempotencyKey })
      const order = unwrap(payload) ?? payload?.data ?? payload
      toast.success(payload?.message || 'سفارش ثبت شد')
      await refresh()
      navigate(`/orders/${order.id}`)
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <h1 className="text-2xl font-bold">تسویه حساب</h1>
      <div className="rounded-2xl border border-slate-200 bg-white p-5">
        <p className="text-sm text-slate-500">مبلغ قابل پرداخت</p>
        <p className="text-2xl font-extrabold">{formatPrice(cart.total)}</p>
      </div>
      <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-5">
        <Textarea
          label="آدرس ارسال"
          required
          maxLength={500}
          value={address}
          onChange={(e) => setAddress(e.target.value)}
          placeholder="شهر، خیابان، پلاک، واحد"
        />
        <p className="mt-2 text-xs text-slate-400">کلید تکرارناپذیر این تلاش: {idempotencyKey}</p>
        <Button type="submit" className="mt-4" disabled={busy || cart.is_empty}>ثبت سفارش</Button>
      </form>
    </div>
  )
}
