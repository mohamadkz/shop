import { Link, useNavigate } from 'react-router-dom'
import { Minus, Plus, Tag, Trash2 } from 'lucide-react'
import { useCart } from '../context/CartContext'
import { useToast } from '../context/ToastContext'
import { formatPrice } from '../utils/format'
import Button from '../components/ui/Button'
import Input from '../components/ui/Input'
import { TableSkeleton } from '../components/ui/Skeleton'
import { useState } from 'react'

export default function Cart() {
  const { cart, catalog, loading, updateQty, removeItem, applyDiscount, removeDiscount } = useCart()
  const toast = useToast()
  const navigate = useNavigate()
  const [code, setCode] = useState('')
  const [busy, setBusy] = useState(false)

  const lines = cart.lines || []

  async function changeQty(itemId, quantity) {
    try {
      await updateQty(itemId, quantity)
    } catch (err) {
      toast.error(err.message)
    }
  }

  async function apply(e) {
    e.preventDefault()
    setBusy(true)
    try {
      await applyDiscount(code)
      setCode('')
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (loading) return <TableSkeleton />

  if (!lines.length) {
    return (
      <div className="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
        <h1 className="text-xl font-bold">سبد خرید خالی است</h1>
        <p className="mt-2 text-sm text-slate-500">از کاتالوگ کالایی اضافه کنید.</p>
        <Link to="/products">
          <Button className="mt-5">مشاهده محصولات</Button>
        </Link>
      </div>
    )
  }

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
      <div className="space-y-4">
        <h1 className="text-2xl font-bold">سبد خرید</h1>
        {lines.map((line) => {
          const meta = catalog[line.item_id] || {}
          return (
            <div key={line.item_id} className="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
              <div className="min-w-0 flex-1">
                <p className="font-semibold">{meta.name || `کالا #${line.item_id}`}</p>
                <p className="text-sm text-slate-500">{formatPrice(line.unit_price)}</p>
              </div>
              <div className="flex items-center gap-2">
                <button className="rounded-lg border p-1" onClick={() => changeQty(line.item_id, Math.max(0, line.quantity - 1))}>
                  <Minus className="h-4 w-4" />
                </button>
                <span className="w-6 text-center text-sm">{line.quantity}</span>
                <button className="rounded-lg border p-1" onClick={() => changeQty(line.item_id, line.quantity + 1)}>
                  <Plus className="h-4 w-4" />
                </button>
              </div>
              <p className="w-28 text-left text-sm font-medium">{formatPrice(line.line_total)}</p>
              <button className="text-rose-500" onClick={() => removeItem(line.item_id).catch((err) => toast.error(err.message))}>
                <Trash2 className="h-4 w-4" />
              </button>
            </div>
          )
        })}
      </div>

      <aside className="h-fit rounded-2xl border border-slate-200 bg-white p-5">
        <h2 className="font-semibold">خلاصه سفارش</h2>
        <dl className="mt-4 space-y-2 text-sm">
          <div className="flex justify-between"><dt>جمع جزء</dt><dd>{formatPrice(cart.subtotal)}</dd></div>
          <div className="flex justify-between"><dt>تخفیف</dt><dd>{formatPrice(cart.discount_amount)}</dd></div>
          <div className="flex justify-between"><dt>مالیات</dt><dd>{formatPrice(cart.tax)}</dd></div>
          <div className="flex justify-between border-t pt-2 font-bold"><dt>قابل پرداخت</dt><dd>{formatPrice(cart.total)}</dd></div>
        </dl>
        {cart.discount_code ? (
          <div className="mt-4 flex items-center justify-between rounded-xl bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
            <span className="flex items-center gap-1"><Tag className="h-4 w-4" />{cart.discount_code}</span>
            <button onClick={() => removeDiscount().catch((err) => toast.error(err.message))}>حذف</button>
          </div>
        ) : (
          <form onSubmit={apply} className="mt-4 flex gap-2">
            <Input value={code} onChange={(e) => setCode(e.target.value)} placeholder="کد تخفیف" />
            <Button type="submit" variant="secondary" disabled={busy}>اعمال</Button>
          </form>
        )}
        <Button className="mt-5 w-full" onClick={() => navigate('/checkout')}>تسویه حساب</Button>
      </aside>
    </div>
  )
}
