import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { catalogApi, unwrap, unwrapList } from '../api/client'
import { formatDate, formatPrice } from '../utils/format'
import { DetailSkeleton } from '../components/ui/Skeleton'
import Button from '../components/ui/Button'
import Textarea from '../components/ui/Textarea'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useToast } from '../context/ToastContext'
import { Heart, ShoppingBag, Star } from 'lucide-react'

export default function Product() {
  const { id } = useParams()
  const { user } = useAuth()
  const { addItem } = useCart()
  const toast = useToast()
  const [item, setItem] = useState(null)
  const [comments, setComments] = useState([])
  const [loading, setLoading] = useState(true)
  const [rating, setRating] = useState(5)
  const [comment, setComment] = useState('')
  const [busy, setBusy] = useState(false)

  async function load() {
    setLoading(true)
    try {
      const [itemPayload, commentsPayload] = await Promise.all([
        catalogApi.item(id),
        catalogApi.comments(id),
      ])
      setItem(unwrap(itemPayload) ?? itemPayload?.data ?? itemPayload)
      setComments(unwrapList(commentsPayload).items)
    } catch (err) {
      setItem(null)
      toast.error(err.message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id])

  async function handleAdd() {
    if (!user) return toast.info('ابتدا وارد شوید')
    setBusy(true)
    try {
      await addItem(item, 1)
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  async function handleFavorite() {
    if (!user) return toast.info('ابتدا وارد شوید')
    try {
      const payload = await catalogApi.toggleFavorite(id)
      toast.success(payload?.message || 'وضعیت علاقه‌مندی تغییر کرد')
    } catch (err) {
      toast.error(err.message)
    }
  }

  async function handleComment(e) {
    e.preventDefault()
    if (!user) return toast.info('برای ثبت نظر وارد شوید')
    setBusy(true)
    try {
      const payload = await catalogApi.addComment(id, { rating, comment })
      toast.success(payload?.message || 'نظر ثبت شد')
      setComment('')
      await load()
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (loading) return <DetailSkeleton />
  if (!item) {
    return (
      <div className="rounded-2xl bg-white p-8 text-center text-slate-500">
        کالا پیدا نشد. <Link className="text-indigo-600" to="/products">بازگشت</Link>
      </div>
    )
  }

  return (
    <div className="space-y-10">
      <div className="grid gap-8 lg:grid-cols-2">
        <div className="overflow-hidden rounded-3xl bg-slate-100">
          <img
            src={item.image || `https://picsum.photos/seed/${item.id}/960/720`}
            alt={item.name}
            className="h-full max-h-[480px] w-full object-cover"
          />
        </div>
        <div>
          <p className="text-sm text-indigo-600">{item.category?.name}</p>
          <h1 className="mt-2 text-3xl font-bold">{item.name}</h1>
          <p className="mt-4 text-slate-600">{item.description || 'توضیحی ثبت نشده است.'}</p>
          <p className="mt-6 text-2xl font-extrabold">{formatPrice(item.price)}</p>
          <p className="mt-2 text-sm text-slate-500">موجودی: {item.stock}</p>
          <div className="mt-6 flex flex-wrap gap-3">
            <Button onClick={handleAdd} disabled={!item.in_stock || busy}>
              <ShoppingBag className="h-4 w-4" />
              افزودن به سبد
            </Button>
            <Button variant="secondary" onClick={handleFavorite}>
              <Heart className="h-4 w-4" />
              علاقه‌مندی
            </Button>
            <Link to="/admin/items" className="text-sm text-slate-400 self-center">
              ویرایش در پنل کالا
            </Link>
          </div>
        </div>
      </div>

      <section className="grid gap-6 lg:grid-cols-[1fr_320px]">
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
          <h2 className="mb-4 font-semibold">نظرات</h2>
          {comments.length === 0 ? (
            <p className="text-sm text-slate-500">هنوز نظری ثبت نشده است.</p>
          ) : (
            <ul className="space-y-4">
              {comments.map((c) => (
                <li key={c.id} className="rounded-xl bg-slate-50 p-4">
                  <div className="mb-1 flex items-center gap-1 text-amber-500">
                    {Array.from({ length: c.rating || 0 }).map((_, i) => (
                      <Star key={i} className="h-3.5 w-3.5 fill-current" />
                    ))}
                  </div>
                  <p className="text-sm text-slate-700">{c.comment}</p>
                  <p className="mt-2 text-xs text-slate-400">{formatDate(c.created_at)}</p>
                </li>
              ))}
            </ul>
          )}
        </div>
        <form onSubmit={handleComment} className="rounded-2xl border border-slate-200 bg-white p-5">
          <h3 className="mb-3 font-semibold">ثبت نظر</h3>
          <label className="mb-3 block text-sm">
            امتیاز
            <select value={rating} onChange={(e) => setRating(Number(e.target.value))} className="mt-1 h-11 w-full rounded-xl border border-slate-200 px-3">
              {[1, 2, 3, 4, 5].map((n) => (
                <option key={n} value={n}>{n}</option>
              ))}
            </select>
          </label>
          <Textarea value={comment} onChange={(e) => setComment(e.target.value)} required maxLength={2000} placeholder="نظر شما" />
          <Button type="submit" className="mt-3 w-full" disabled={busy}>ارسال نظر</Button>
        </form>
      </section>
    </div>
  )
}
